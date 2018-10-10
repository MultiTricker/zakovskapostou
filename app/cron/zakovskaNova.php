<?php

require_once dirname(__FILE__) . "/simple_html_dom.php";
require_once dirname(__FILE__) . "/../functions.php";
require_once dirname(__FILE__) . "/../cfg/dbConnect.php";
require_once dirname(__FILE__) . "/../../vendor/swiftmailer/swiftmailer/lib/swift_required.php";

$stylKlasifikace = "<style>
table{border:1px solid #4b8c21;}
table tr td{border-bottom:1px solid #b2df96; border-right:1px solid #b2df96;}
.new{font-weight:bold;}
.old{color:#3f4144;}
.zahlavi{background:#c2e0af !important; font-weight:bold;}
table tr:nth-child(odd){background:#ebf4e6}
table tr td a{text-decoration:none; color:black;}
table tr td.old a{text-decoration:none; color:grey;}
h3{color:#659944;}
h4{color:#659944;}
</style>";

$stylOznameni = "<style>
.stareOznameni{color: grey;}
.stareOznameni h4{color: grey;}
.stareOznameni p{color: grey;}
.stareOznameni div{color: grey;}
.stareOznameni p a{color: grey;}
</style>";

$q = mysqli_query($GLOBALS["DBC"], "SELECT id, id_uzivatel, id_skola, jmeno, heslo, posledni_kontrola, posledni_uspesny_login 
                                    FROM zk 
                                    WHERE smazana = 0 
                                      AND (posledni_kontrola < NOW() - INTERVAL 1 HOUR OR posledni_kontrola IS NULL) 
                                      AND do_zmeny_odpojena = 0 
                                    ORDER BY posledni_kontrola ASC  
                                    LIMIT 3;");

while($r = mysqli_fetch_assoc($q))
{
    mysqli_query($GLOBALS["DBC"], "UPDATE zk SET posledni_kontrola = now() WHERE id = '{$r['id']}';");

    $rSkola = mysqli_fetch_assoc(mysqli_query($GLOBALS["DBC"], "SELECT nazev, url_zakovska FROM skola WHERE id = {$r['id_skola']}"));
    $rNaMaily = mysqli_fetch_assoc(mysqli_query($GLOBALS["DBC"], "SELECT GROUP_CONCAT(email) AS naMaily FROM email WHERE id_zakovska = {$r['id']}"));
    $hlavniMail = mysqli_fetch_assoc(mysqli_query($GLOBALS["DBC"], "SELECT email FROM uzivatel WHERE id = {$r['id_uzivatel']}"));

    $urlISAS = $rSkola['url_zakovska'];
    $SASjmeno = $r['jmeno'];
    $SASheslo = $r['heslo'];
    $naMaily = $rNaMaily['naMaily'];
    $mailOd = "oznameni@zakovskapostou.cz";
    $log = "";

    ////////////////////
    // Zpracovani
    ////////////////////

    if(isset($ch))
    {
        unset($ch);
    }

    $post_data = "login-isas-username={$SASjmeno}&login-isas-password={$SASheslo}&login-isas-send=isas-send";

    // Nastaveni CURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlISAS . "prihlasit.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // Bohuzel vypnout, jestli certifikat neni OK
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1); // Bohuzel vypnout, jestli certifikat neni OK
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36');
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie-jar-' . $r['id'] . '.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie-file-' . $r['id'] . '.txt');
    curl_setopt($ch, CURLOPT_VERBOSE, true);

    // Volame LOGIN
    $postResult = curl_exec($ch);
    $log .= "LOGIN\n<br>RESPONSE: {$postResult}\n<br>INFO: " . json_encode(curl_getinfo($ch)) . "\n<br>ERROR: " . json_encode(curl_error($ch));

    // Volame
    curl_setopt($ch, CURLOPT_URL, $urlISAS . "profil.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    $profil = iconv("windows-1250", "UTF-8", curl_exec($ch));
    $log .= "<br>PROFIL\n<br>RESPONSE: {$profil}\n<br>INFO: " . json_encode(curl_getinfo($ch)) . "\n<br>ERROR: " . json_encode(curl_error($ch));

    // Podarilo se prihlasit?
    if(mb_strpos($profil, "Poslední přihlášení") !== false)
    {
        mysqli_query($GLOBALS["DBC"], "UPDATE zk SET posledni_uspesny_login = now() WHERE id = '{$r['id']}';");
    }
    else
    {
        if($r['posledni_uspesny_login'] == "" AND $r['posledni_kontrola'] == "")
        {
            try
            {
                mysqli_query($GLOBALS["DBC"], "UPDATE zk SET do_zmeny_odpojena = 1 WHERE id = '{$r['id']}';");

                $transport = Swift_SmtpTransport::newInstance('localhost', 25);
                $mailer = Swift_Mailer::newInstance($transport);
                $message = Swift_Message::newInstance()
                    ->setCharset("UTF-8")
                    ->setSubject("Žákovská poštou - nezdařilo se přihlášení ($SASjmeno)")
                    ->setFrom("chyba@zakovskapostou.cz")
                    ->setTo($hlavniMail['email'])
                    ->setContentType("text/html");
                $message->setBody("<html><body><p>Dobrý den,</p>
<p>do žákovské knížky s přihlašovacím jménem \"{$SASjmeno}\" se nepodařilo přihlásit.</p>
<p>Pokud je aplikace žákovské knížky na adrese <a href='{$urlISAS}'>{$urlISAS}</a> funkční, zkontrolujte prosím 
přihlašovací údaje ve svém účtu na <a href='https://www.zakovskapostou.cz'>https://www.zakovskapostou.cz</a>.</p>
<p><b><i>zakovskapostou.cz</i></b></p></body></html>", 'text/html');

                $mailer->send($message);
                unset($transport);
                unset($message);
                unset($mailer);
            } catch(Exception $h)
            {
                $log .= "<font style='color: darkred;'><b>" . $h->getMessage() . "</b></font><br />";
            }
        }
    }

    // Nacteme klasifikaci
    curl_setopt($ch, CURLOPT_URL, $urlISAS . "prubezna-klasifikace.php");
    $klasifikace = iconv("windows-1250", "UTF-8", curl_exec($ch));
    $log .= "<br>KLASIFIKACE\n<br>RESPONSE: {$klasifikace}\n<br>INFO: " . json_encode(curl_getinfo($ch)) . "\n<br>ERROR: " . json_encode(curl_error($ch));

    // Nacteme nastenku
    curl_setopt($ch, CURLOPT_URL, $urlISAS . "informacni-nastenka.php");
    $nastenka = iconv("windows-1250", "UTF-8", curl_exec($ch));
    $log .= "<br>NASTENKA\n<br>RESPONSE: {$nastenka}\n<br>INFO: " . json_encode(curl_getinfo($ch)) . "\n<br>ERROR: " . json_encode(curl_error($ch));

    // Nacteme ctvtletni a pololetni hodnoceni
    curl_setopt($ch, CURLOPT_URL, $urlISAS . "prubezna-klasifikace.php?zobraz=hodnoceni");
    $hodnoceni = iconv("windows-1250", "UTF-8", curl_exec($ch));
    $log .= "<br>HODNOCENI\n<br>RESPONSE: {$hodnoceni}\n<br>INFO: " . json_encode(curl_getinfo($ch)) . "\n<br>ERROR: " . json_encode(curl_error($ch));

    // Odhlasime se
    curl_setopt($ch, CURLOPT_URL, $urlISAS . "isas/odhlasit.php");
    curl_setopt($ch, CURLOPT_POST, 1);

    ////////////////////
    // Zpracovani
    ////////////////////

    if(mb_strlen($klasifikace) > 1000 OR mb_strlen($nastenka) > 1000)
    {
        ////////////////////
        // Klasifikace
        ////////////////////

        $html = new simple_html_dom($klasifikace);
        $novaKlasifikace = 0;

        foreach(array_reverse($html->find('.isas-tabulka tr')) as $polozka)
        {
            if(mb_strpos($polozka, "\"zahlavi\"") === false)
            {
                $polozka = strip_tags($polozka, "<tr><td>");
                $polozka = str_replace(' class="lichy"', '', $polozka);
                $polozka = str_replace(' class="sudy"', '', $polozka);

                $md5 = md5($r['id'] . $polozka);

                if(mysqli_num_rows(mysqli_query($GLOBALS["DBC"], "SELECT id FROM zaznam WHERE id_zk = '{$r['id']}' AND md5 = '$md5'")) == 0)
                {
                    mysqli_query($GLOBALS["DBC"], "INSERT INTO zaznam(id_zk, typ, zaznam, md5, kdy) 
                                               VALUES('{$r['id']}', 
                                                      'znamka', 
                                                      \"" . mysqli_real_escape_string($GLOBALS["DBC"], $polozka) . "\",
                                                      '{$md5}',
                                                      now());");
                    $novaKlasifikace = 1;
                }
            }
        }

        ///////////////////////////////////////
        // Ctvrtletni/pololetni hodnoceni
        ///////////////////////////////////////

        $html = new simple_html_dom($hodnoceni);
        foreach($html->find(".isas-tabulka") as $polozka)
        {
            $hodnoceni = $polozka;
        }

        ///////////////////////////
        // Oznameni na nastence
        ///////////////////////////

        $html = new simple_html_dom($nastenka);
        $noveOznameni = 0;

        foreach(array_reverse($html->find('.isas-oznameni')) as $polozka)
        {
            $polozkaNase = "";

            $zahlavi = trim($polozka->find('div.zahlavi', 0)->plaintext);
            $zahlavi = str_replace([" &nbsp;Nové", "&nbsp;Nové", " &nbsp;nové", "&nbsp;nové ", " &nbsp;nové ", "&nbsp;nové"], "", $zahlavi);

            $polozkaNase = " <div style = 'border: 1px solid #53763c; padding: 5px; margin: 10px;'>
<h4 style = 'margin: 0;' > " . $zahlavi . "</h4>
<p>" . $polozka->find('div.isas-zapisnik', 0) . "</p>
";

            if($polozka->find('div.odkazy', 0))
            {
                $polozkaNase .= "<p>" . $polozka->find('div.odkazy', 0) . "</p>";
            }

            $polozkaNase .= "<p style = 'font-size: 12px; color: darkgrey;margin: 0;' > " . $polozka->find('div.paticka', 0)->plaintext . "</p>
</div> ";

            $md5 = md5($r['id'] . $polozkaNase);
            if(mysqli_num_rows(mysqli_query($GLOBALS["DBC"], "SELECT id FROM zaznam WHERE id_zk = '{$r['id']}' AND md5 = '$md5'")) == 0)
            {
                mysqli_query($GLOBALS["DBC"], "INSERT INTO zaznam(id_zk, typ, zaznam, md5, kdy) 
                                               VALUES('{$r['id']}', 
                                                      'oznameni', 
                                                      \"" . mysqli_real_escape_string($GLOBALS["DBC"], $polozkaNase) . "\",
                                                      '{$md5}',
                                                      now());");
                $noveOznameni = 1;
            }
        }

        ////////////////////
        // Porovnani
        ////////////////////

        $zmena = 0;
        $predmet = "";
        $textMailu = "<html><body>\n";

        //////////////////////////
        // Poresime Nastenku
        /////////////////////////

        if($noveOznameni)
        {
            $zmena = 1;
            $predmet = " - nástěnka";

            $textMailu .= "<h3>Nástěnka</h3>\n";

            $qNova = mysqli_query($GLOBALS["DBC"], "SELECT zaznam 
                                                    FROM zaznam 
                                                    WHERE id_zk = '{$r['id']}' AND typ = 'oznameni' AND novy = 1 
                                                    ORDER BY id DESC");

            while($t = mysqli_fetch_assoc($qNova))
            {
                $textMailu .= $t['zaznam'] . "\n";
            }

            $qStara = mysqli_query($GLOBALS["DBC"], "SELECT zaznam 
                                                     FROM zaznam 
                                                     WHERE id_zk = '{$r['id']}' AND typ = 'oznameni' AND novy = 0 
                                                     ORDER BY id DESC 
                                                     LIMIT 0, 3");

            if(mysqli_num_rows($qStara) > 0)
            {
                $starsiOznameni = "<h3 style='color: grey;'>Starší oznámení</h3>\n";
                while($t = mysqli_fetch_assoc($qStara))
                {
                    $starsiOznameni .= str_replace("<div ", "<div class='stareOznameni' ", $t['zaznam']) . "\n";
                }
                $starsiOznameni .= $stylOznameni;
            }
        }

        //////////////////////////
        // Poresime klasifikaci
        /////////////////////////

        if($novaKlasifikace == 1)
        {
            if($zmena == 1)
            {
                $predmet .= " a známky";
            }
            else
            {
                $predmet = " - známky";
            }

            $zmena = 1;

            $textMailu .= "<h3>Klasifikace</h3>\n";
            $textMailu .= "<table>\n";
            $textMailu .= "<tr class=\"zahlavi\"><td>Datum</td><td>Předmět</td><td>Známka</td><td>Hodnota</td><td>Typ zkoušení</td><td>Váha</td><td>Poznámka</td><td>Učitel</td></tr>\n";

            $qNova = mysqli_query($GLOBALS["DBC"], "SELECT zaznam 
                                                    FROM zaznam 
                                                    WHERE id_zk = '{$r['id']}' AND typ = 'znamka' AND novy = 1 
                                                    ORDER BY id DESC");

            while($t = mysqli_fetch_assoc($qNova))
            {
                $textMailu .= str_replace("<td", "<td class='new'", $t['zaznam']);
            }

            $qStara = mysqli_query($GLOBALS["DBC"], "SELECT zaznam 
                                                     FROM zaznam 
                                                     WHERE id_zk = '{$r['id']}' AND typ = 'znamka' AND novy = 0 
                                                     ORDER BY id DESC 
                                                     LIMIT 0, 10");

            if(mysqli_num_rows($qStara) > 0)
            {
                while($t = mysqli_fetch_assoc($qStara))
                {
                    $textMailu .= str_replace("<td", "<td class='old'", $t['zaznam']) . "\n";
                }
            }

            $textMailu .= "</table>\n";

            if(strlen($hodnoceni) > 200)
            {
                $textMailu .= "<h4>Čtvrtletní a pololetní klasifikace</h4>\n";
                $textMailu .= $hodnoceni;
            }
        }

        $textMailu .= $stylKlasifikace;

        // Mame stara oznameni?
        if(isset($starsiOznameni))
        {
            $textMailu .= $starsiOznameni;
            unset($starsiOznameni);
        }

        $textMailu .= "</body></html> ";

        //////////////////////////////////////////////
        // Nastavime, ze zaznamy uz nejsou nove
        //////////////////////////////////////////////

        mysqli_query($GLOBALS["DBC"], "UPDATE zaznam SET novy = 0 WHERE id_zk = '{$r['id']}';");

        ////////////////////
        // E-mailing
        ////////////////////

        if($zmena == 1 AND mb_strlen($textMailu) > 50)
        {
            $log .= "\n <br>Odesílám e - maily o změnách na {$naMaily}.";
            foreach(explode(",", $naMaily) as $mail)
            {
                $mail = trim($mail);

                if(filter_var($mail, FILTER_VALIDATE_EMAIL))
                {
                    try
                    {
                        $transport = Swift_SmtpTransport::newInstance('localhost', 25);
                        $mailer = Swift_Mailer::newInstance($transport);
                        $message = Swift_Message::newInstance()
                            ->setCharset("UTF-8")
                            ->setSubject("Žákovská poštou $SASjmeno" . $predmet)
                            ->setFrom("oznameni@zakovskapostou.cz")
                            ->setTo($mail)
                            ->setContentType("text/html");
                        $message->setBody($textMailu, 'text/html');

                        $mailer->send($message);
                        unset($transport);
                        unset($message);
                        unset($mailer);
                    } catch(Exception $h)
                    {
                        $log .= " <font style = 'color: darkred;'><b> " . $h->getMessage() . "</b></font><br />";
                    }
                }
            }
        }
    }

    // Ulozime
    mysqli_query($GLOBALS["DBC"], "INSERT INTO zk_historie(id_zakovska, kdy, znamky, nastenka, log) 
                                   VALUES(\"" . $r['id'] . "\", 
                                          now(), 
                                          \"" . mysqli_real_escape_string($GLOBALS["DBC"], $textMailu) . "\", 
                                          \"\", 
                                          \"" . mysqli_real_escape_string($GLOBALS["DBC"], $log) . "\");");

    // Chvili pockame pred zpracovanim dalsi zakovske
    sleep(1);

    // echo $log;
}
