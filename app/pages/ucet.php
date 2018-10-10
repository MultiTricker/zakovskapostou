<?php

if(isLoggedIn())
{
    echo "<p>&nbsp;</p>";

    if(isset($_GET['smazatZakovskou']) AND is_numeric($_GET['smazatZakovskou']))
    {
        $staraZakovska = mysqli_query($GLOBALS["DBC"], "SELECT id 
                                                        FROM zk
                                                        WHERE id = '{$_GET['smazatZakovskou']}'
                                                          AND id_uzivatel = '{$uzivatel['id']}'
                                                          AND smazana = 0");

        if(mysqli_num_rows($staraZakovska) == 0)
        {
            echo "<div class='alert alert-warning'>Tahle žákovská tu není, nemažu nic.</div>";
        }
        else
        {
            $staraZakovskaData = mysqli_fetch_assoc($staraZakovska);

            mysqli_query($GLOBALS["DBC"], "UPDATE zk 
                                           SET smazana = 1, jmeno = concat(jmeno, '-smazana') 
                                           WHERE id = '{$staraZakovskaData['id']}'");

            echo "<div class='alert alert-success'>Žákovská ke sledování je smazaná.</div>";
        }
    }

    if(isset($_GET['smazatEmail']) AND is_numeric($_GET['smazatEmail']))
    {
        $staryEmail = mysqli_query($GLOBALS["DBC"], "SELECT id 
                                                     FROM email
                                                     WHERE id = '{$_GET['smazatEmail']}'
                                                       AND id_zakovska IN (SELECT id FROM zk WHERE id_uzivatel = '{$uzivatel['id']}')");

        if(mysqli_num_rows($staryEmail) == 0)
        {
            echo "<div class='alert alert-warning'>Není zde takový e-mail ke smazání, nedělám nic.</div>";
        }
        else
        {
            $staryEmailData = mysqli_fetch_assoc($staryEmail);

            mysqli_query($GLOBALS["DBC"], "DELETE FROM email WHERE id = '{$staryEmailData['id']}'");

            echo "<div class='alert alert-success'>E-mail byl smazán.</div>";
        }
    }

    $qZakovsky = mysqli_query($GLOBALS["DBC"], "SELECT * FROM zk WHERE id_uzivatel = '{$uzivatel['id']}' AND smazana = 0");
    $zakovsky = mysqli_num_rows($qZakovsky);

    echo '<div class="contact-form col-md-12 col-sm-12 col-xs-12">
          <center>';

    if(!isset($_GET['upravitZakovskou']) AND !isset($_GET['pridatZakovskou']) AND
        !isset($_GET['upravitEmail']) AND !isset($_GET['pridatEmail']) AND !isset($_GET['debug'])
        AND !isset($_GET['zmenitHeslo'])
    )
    {

        // Blok s dodatecnym overenim uctu
        if($uzivatel['overeny'] == 0)
        {
            echo "<div class=\"panel panel-default\" style='max-width: 500px;'>
                  <div class=\"panel-heading\"><i class='fa fa-exclamation'></i> Neověřený účet</div>
                  <div class=\"panel-body\">";

            if(!isset($_GET['zasliOverovaciKod']))
            {
                echo "<p>Tvůj e-mail není ověřený, takže není možné zasílat informace z žákovské. Ověření provedete otevřením odkazu, který Vám přišel do e-mailu při registraci.</p>";
                echo "<p><a href='?zasliOverovaciKod=1' class='btn btn-default'><i class='fa fa-envelope'></i> Zaslat znovu e-mail s odkazem na aktivaci.</a></p>";
            }
            else
            {
                resendActivationCode($uzivatel['email']);
            }

            echo "</div>
            </div>";
        }

        echo "<h2>Seznam sledovaných žákovských knížek</h2><br>";

        if($zakovsky > 0)
        {
            echo "<div class='panel panel-default'>
            <table class='table table-normal table-hover'>
            <thead>
              <tr style='background: #dddddd;'>
                <td><b>Škola</b></td>
                <td><b>Přihl. jméno</b></td>
                <td><b>Poslední kontrola</b></td>
                <td><b>Poslední úspěšné přihlášení</b></td>
                <td></td>
              </tr>
            </thead>";

            while($r = mysqli_fetch_assoc($qZakovsky))
            {
                $emaily = mysqli_query($GLOBALS["DBC"], "SELECT id, email 
                                                         FROM email
                                                         WHERE id_zakovska = '{$r['id']}' AND smazany = 0
                                                         ORDER BY email DESC;");

                $skola = mysqli_fetch_assoc(mysqli_query($GLOBALS["DBC"], "SELECT nazev FROM skola WHERE id = '{$r['id_skola']}'"));
                $skola['nazev'] = explode(",", $skola['nazev'])[0];

                echo "<tr>
                        <td><b>" . $skola['nazev'] . "</b></td>
                        <td>{$r['jmeno']}</td>";

                if($r['posledni_kontrola'] != "")
                {
                    echo "<td>" . $r['posledni_kontrola'] . "</td>";
                }
                else
                {
                    echo "<td>-</td>";
                }

                if($r['posledni_uspesny_login'] != "")
                {
                    echo "<td>" . $r['posledni_uspesny_login'] . "</td>";
                }
                else
                {
                    echo "<td>-</td>";
                }

                echo "<td>
                      <a class='btn btn-success btn-sm' href='?pridatEmail={$r['id']}&zakovska={$r['id']}'><i class='fa fa-fw fa-plus'></i> Přidat e-mail pro oznámení</a>&nbsp;
                      <div class='btn-group'>
                        <a class='btn btn-primary btn-sm' href='?upravitZakovskou={$r['id']}'><i class='fa fa-fw fa-pencil'></i> Upravit žákovskou</a>
                        <button type='button' class='btn btn-primary btn-sm dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                          <span class='caret'></span>
                          <span class='sr-only'>Menu</span>
                        </button>
                        <ul class='dropdown-menu'>
                          <li><a href='?debug={$r['id']}'><i class='fa fa-fw fa-bug'></i> Logy</a></li>
                          <li><a href='?smazatZakovskou={$r['id']}' onClick='return confirm(\"Doopravdy smazat žákovskou?\")'><i class='fa fa-fw fa-times'></i> Smazat žákovskou</a></li>
                        </ul>
                      </div>
                    </td>
                  </tr>";

                while($t = mysqli_fetch_assoc($emaily))
                {
                    echo "<tr>
                            <td colspan='4'>&nbsp;&nbsp;<i class='fa fa-long-arrow-right'></i> {$t['email']}</td>
                            <td>
                              <a class='btn btn-info btn-sm' href='?upravitEmail={$t['id']}&zakovska={$r['id']}'><i class='fa fa-pencil'></i> Upravit</a>&nbsp;
                              <a href='?smazatEmail={$t['id']}'  onClick='return confirm(\"Doopravdy smazat e-mail {$t['email']}?\")' class='btn btn-sm btn-danger'><i class='fa fa-fw fa-trash-o'></i> Smazat</a>
                            </td>
                          </tr>";
                }
            }

            echo "</table>
            </div>
          </center>";

        }
        else
        {
            echo "<p class='alert alert-info'><i class='fa fa-info'></i> Zatím nemáte přidanou žádnou žákovskou knížku.</p>";
        }

        echo "<p><a href='?pridatZakovskou=1' class='btn btn-primary btn-lg'><i class='fa fa-plus'></i> Přidat žákovskou</a></p>
            </div>";
    }

    if(isset($_GET['upravitZakovskou']) AND is_numeric($_GET['upravitZakovskou']))
    {
        echo '<div class="contact-form col-md-6 col-sm-12 col-xs-12 col-md-offset-3">';
        echo "<h2>Úprava žákovské</h2>";

        $qZakovska = mysqli_query($GLOBALS["DBC"], "SELECT * FROM zk WHERE id = '{$_GET['upravitZakovskou']}' AND id_uzivatel = '{$uzivatel['id']}';");
        if(mysqli_num_rows($qZakovska) == 0)
        {
            echo "<p class='alert alert-danger'>Chyba - zadaná žákovská pod tvým účtem nebyla nalezena.</p>";
        }
        else
        {
            $uprava = 1;
            $zk = mysqli_fetch_assoc($qZakovska);
            require_once dirname(__FILE__) . "/../forms/zk.php";

            echo "<br><p>
                    <a href='index.php' class='btn btn-default'><i class='fa fa-arrow-left' style='color: black;'></i> Zpět na přehled</a>
                  </p>";
        }
        echo '</div>';
    }

    if(isset($_GET['upravitEmail']) AND is_numeric($_GET['upravitEmail']))
    {
        echo '<div class="contact-form col-md-6 col-sm-12 col-xs-12 col-md-offset-3">';
        echo "<h2>Úprava e-mailu</h2>";

        $qEmail = mysqli_query($GLOBALS["DBC"], "SELECT *  
                                                 FROM email 
                                                 WHERE id = '{$_GET['upravitEmail']}' 
                                                   AND id_zakovska IN (SELECT id FROM zk WHERE id_uzivatel = '{$uzivatel['id']}');");

        if(mysqli_num_rows($qEmail) == 0)
        {
            echo "<p class='alert alert-danger'>Chyba - zadaný e-mail pod tvým účtem nebyl nalezen.</p>";
        }
        else
        {
            $uprava = 1;
            $email = mysqli_fetch_assoc($qEmail);
            $email['emailUpozorneni'] = $email['email'];
            require_once dirname(__FILE__) . "/../forms/email.php";
        }

        echo "<br><p><a href='index.php' class='btn btn-default'><i class='fa fa-arrow-left' style='color: black;'></i> Zpět na přehled žákovských</a></p>";

        echo '</div>';
    }

    if(isset($_GET['debug']) AND is_numeric($_GET['debug']))
    {
        echo '<div class="contact-form col-md-12 col-sm-12 col-xs-12">';
        echo "<h2>Zobrazení logů žákovské</h2>";


        if($uzivatel['id'] == 1)
        {
            $qLogy = mysqli_query($GLOBALS["DBC"], "SELECT *
        	                                        FROM zk_historie
                	                                WHERE id_zakovska = {$_GET['debug']}
                        	                        ORDER BY kdy DESC
                                	                LIMIT 10;");

        }
        else
        {
            $qLogy = mysqli_query($GLOBALS["DBC"], "SELECT * 
                	                                FROM zk_historie 
                        	                        WHERE id_zakovska = (SELECT id FROM zk WHERE id_uzivatel = '{$uzivatel['id']}' AND id_zakovska='{$_GET['debug']}') 
                                	                ORDER BY kdy DESC 
                                        	        LIMIT 10;");
        }

        if(mysqli_num_rows($qLogy) == 0)
        {
            echo "<p class='alert alert-info'>Nenašel jsem žádné záznamy.</p>";
        }
        else
        {
            echo "<p class='alert alert-info'>Posledních až 10 záznamů vyčtených ze žákovské.</p>
            <div class='panel panel-default'>
                            <table class='table table-normal table-hover'>
                            <thead>
                              <tr style='background: #dddddd;'>
                                <td><b>Odeslaný e-mail</b></td>
                                <!--<td><b>Log</b></td>-->
                                <td></td>
                              </tr>
                            </thead>";

            while($r = mysqli_fetch_assoc($qLogy))
            {
                echo "<tr>
                        <td colspan='3' style='color: white; background: black;'><b>" . lidskyCas($r['kdy']) . "</b></td>
                      </tr>
                      <tr>
                        <td>{$r['znamky']}</td>
                        <!--<td>" . strip_tags($r['log']) . "</td>-->
                      </tr>";
            }
            echo "</table>
                            </div>";
        }

        echo "<br><p>
                <a href='index.php' class='btn btn-default'><i class='fa fa-arrow-left' style='color: black;'></i> Zpět na přehled žákovských</a>
              </p>";

        echo '</div>';
    }

    if(isset($_GET['zmenitHeslo']) AND $_GET['zmenitHeslo'] == 1)
    {
        echo '<div class="contact-form col-md-6 col-sm-12 col-xs-12 col-md-offset-3">';
        echo "<h2>Změna hesla k účtu {$uzivatel['email']}</h2>";

        require_once dirname(__FILE__) . "/../forms/password.php";

        echo "<br><br><p><a href='index.php' class='btn btn-default'><i class='fa fa-arrow-left' style='color: black;'></i> Zpět na přehled žákovských</a></p>";
        echo "</div>";
    }

    if(isset($_GET['pridatZakovskou']))
    {
        if($uzivatel['overeny'] == 0)
        {
            echo '<div class="contact-form col-md-6 col-sm-12 col-xs-12 col-md-offset-3">';
            echo "<div class='alert alert-danger'>Není možné přidat žákovskou knížku, dokud neověříte svůj účet.</div>";
            echo "<br><br><br><p><a href='index.php' class='btn btn-default'><i class='fa fa-arrow-left' style='color: black;'></i> Zpět</a></p>";
            echo '</div>';
        }
        else
        {
            echo '<div class="contact-form col-md-6 col-sm-12 col-xs-12 col-md-offset-3">';
            echo "<h2>Přidat žákovskou knížku</h2><br>";

            $uprava = 0;
            require_once dirname(__FILE__) . "/../forms/zk.php";

            echo "<br><br><br><p><a href='index.php' class='btn btn-default'><i class='fa fa-arrow-left' style='color: black;'></i> Zpět na přehled žákovských</a></p>";
            echo '</div>';
        }
    }

    if(isset($_GET['pridatEmail']))
    {
        echo '<div class="contact-form col-md-6 col-sm-12 col-xs-12 col-md-offset-3">';
        echo "<h2>Přidat e-mail pro oznámení</h2>";

        $uprava = 0;
        require_once dirname(__FILE__) . "/../forms/email.php";

        echo "<br><br><p><a href='index.php' class='btn btn-default'><i class='fa fa-arrow-left' style='color: black;'></i> Zpět na přehled žákovských</a></p>";
        echo '</div>';
    }

}
