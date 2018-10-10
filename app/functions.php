<?php

/**
 * @param $delka
 * @return string
 */
function getChksum($delka)
{
    $chksum = '';

    // nahodne vyberu radu znaku z predem dane paterny
    for($a = 0; $a < $delka; $a++)
    {

        $pattern = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM!@#$%^&*(){}:?:_+-=";
        $chksum .= substr($pattern, rand(0, strlen($pattern) - 1), 1);

    }

    return $chksum;
}


/**
 * @param $item
 * @param $arg
 * @return bool
 */
function zpAvailableUser($item, $arg)
{
    global $uzivatel;

    $q = mysqli_query($GLOBALS["DBC"], "SELECT id 
                                        FROM zk 
                                        WHERE id_skola = \"" . mysqli_real_escape_string($GLOBALS["DBC"], $arg[0]->value) . "\" 
                                          AND jmeno = \"" . mysqli_real_escape_string($GLOBALS["DBC"], $arg[1]->value) . "\"  
                                          AND id_uzivatel != '{$uzivatel['id']}';");

    if(mysqli_num_rows($q) > 0)
    {
        return false;
    }
    else
    {
        return true;
    }
}

/**
 * @param $item
 * @param $arg
 * @return bool
 */
function zpEmailExists($item, $arg)
{
    $q = mysqli_query($GLOBALS["DBC"], "SELECT email FROM uzivatel WHERE email = '{$item->value}';");

    if(mysqli_num_rows($q) > 0)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * @param $item
 * @param $arg
 * @return bool
 */
function zpAvailableEmail($item, $arg)
{
    $q = mysqli_query($GLOBALS["DBC"], "SELECT email FROM uzivatel WHERE email = '{$item->value}';");

    if(mysqli_num_rows($q) > 0)
    {
        return false;
    }
    else
    {
        return true;
    }
}

/**
 * @param $item
 * @param $arg
 * @return bool
 */
function zpValidLogin($item, $arg)
{
    $q = mysqli_query($GLOBALS["DBC"], "SELECT id, heslo
                                        FROM uzivatel
                                        WHERE email = '{$_POST['email']}';");

    // Mame vubec mail?
    if(mysqli_num_rows($q) > 0)
    {

        // Sedi heslo?
        $u = mysqli_fetch_assoc($q);
        // Je heslo OK?
        if(password_verify($_POST['heslo'], $u['heslo']))
        {
            return true;
        }
        else
        {
            return false;
        }

    }
    else
    {
        return false;
    }
}

/**
 * @param $item
 * @param $arg
 * @return bool
 */
function oldPasswordCheck($item, $arg)
{
    global $uzivatel;

    $q = mysqli_query($GLOBALS["DBC"], "SELECT id, heslo
                                        FROM uzivatel
                                        WHERE id = '{$uzivatel['id']}';");

    // Mame id co existuje?
    if(mysqli_num_rows($q) > 0)
    {

        // Sedi heslo?
        $u = mysqli_fetch_assoc($q);
        // Je heslo OK?
        if(password_verify($_POST['stare_heslo'], $u['heslo']))
        {
            return true;
        }
        else
        {
            return false;
        }

    }
    else
    {
        return false;
    }
}

/**
 * @param $potvrzovaci_kod
 * @param $email
 */
function validateUserEmail($potvrzovaci_kod, $email)
{
    $potvrzovaci_kod = mysqli_real_escape_string($GLOBALS["DBC"], $potvrzovaci_kod);
    $email = mysqli_real_escape_string($GLOBALS["DBC"], $email);

    $q = mysqli_query($GLOBALS["DBC"], "UPDATE uzivatel
                                        SET overeny = 1
                                        WHERE email = \"{$email}\" AND potvrzovaci_kod = \"{$potvrzovaci_kod}\";");

    echo mysqli_error($GLOBALS["DBC"]);

    if(mysqli_affected_rows($GLOBALS["DBC"]) == 1)
    {
        echo "<h2 class='alert alert-success'>Díky, e-mail byl úspěšně ověřen.</h2>";
    }
    else
    {

        $q2 = mysqli_query($GLOBALS["DBC"], "SELECT id FROM uzivatel WHERE overeny = 1 AND email = \"{$email}\";");

        if(mysqli_num_rows($q2) == 1)
        {
            echo "<h2 class='alert alert-success'>Díky, e-mail již byl ověřen.</h2>";
        }
        else
        {
            echo "<h2 class='alert alert-warning'>Nedošlo k ověření e-mailu. Neplatné údaje.</h2>";
        }

    }

    return;
}

/**
 * @return bool
 */
function isLoggedIn()
{
    if(isset($_SESSION['email']) AND isset($_SESSION['chksum'])
        AND $_SESSION['email'] != "" AND $_SESSION['chksum'] != "")
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * @param $email
 */
function resendActivationCode($email)
{
    // Prepare data
    $potvrzovaci_kod = mysqli_query($GLOBALS["DBC"], "SELECT potvrzovaci_kod
                                                      FROM uzivatel
                                                      WHERE email = '{$email}'
                                                        AND (potvrzovaci_kod_znovuzaslan IS NULL
                                                             OR potvrzovaci_kod_znovuzaslan < NOW() - INTERVAL 1 HOUR);");

    if(mysqli_num_rows($potvrzovaci_kod) > 0)
    {
        mysqli_query($GLOBALS["DBC"], "UPDATE uzivatel
                                     SET potvrzovaci_kod_znovuzaslan = now()
                                     WHERE email = '{$email}';");

        $kod = mysqli_fetch_assoc($potvrzovaci_kod);

        // Send e-mail with validation code
        try
        {
            require_once dirname(__FILE__) . "/../vendor/swiftmailer/swiftmailer/lib/swift_required.php";
            $transport = Swift_SmtpTransport::newInstance('localhost', 25);
            $mailer = Swift_Mailer::newInstance($transport);
            $message = Swift_Message::newInstance()->setCharset("UTF-8")->setSubject("zakovskapostou.cz - odkaz na aktivaci účtu")->setFrom("registrace@zakovskapostou.cz")->setTo($email)->setContentType("text/html");
            $message->setBody("<html><body>
<p>Ahoj,</p>

<p>ještě jednou díky za registraci na portále zakovskapostou.cz, na vyžádání ti posílám odkaz na aktivaci účtu.</p>

<p>Otevři prosím následující odkaz pro aktivaci: <a href='http://www.zakovskapostou.cz/index.php?potvrzovaci_kod={$kod['potvrzovaci_kod']}&email=" . mysqli_real_escape_string($GLOBALS["DBC"], $email) . "'>http://www.zakovskapostou.cz/index.php?potvrzovaci_kod={$kod['potvrzovaci_kod']}&email=" . mysqli_real_escape_string($GLOBALS["DBC"], $email) . "</a></p>

<p><b><i>zakovskapostou.cz</i></b>
</p></body></html>", 'text/html');

            $mailer->send($message);
            unset($transport);
            unset($message);
            unset($mailer);

        } catch(Exception $h)
        {
            echo "<font style='color: darkred;'><b>" . $h->getMessage() . "</b></font><br />";
        }

        // Thank you!
        echo "<p class='alert alert-success'><b>Díky za zájem o aktivaci!</b><br />
            E-mail s ověřovacím kódem byl odeslán. Ten je potřeba potvrdit, aby bylo možné zasílat e-maily.</p>
            <p>Zpráva přijde z adresy <b>registrace@zakovskapostou.cz</b>.</p>";
    }
    else
    {
        echo "<p class='alert alert-danger'>Ověřovací kód pro {$email} není možné poslat vícekrát za 1 hodinu. Zkuste to, prosím, později.</p>";
    }

    return;
}

/**
 * @return string
 * @source http://stackoverflow.com/questions/6101956/generating-a-random-password-in-php
 */
function randomPassword()
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

    $pass = []; //remember to declare $pass as an array

    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache

    for($i = 0; $i < 8; $i++)
    {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }

    return implode($pass); //turn the array into a string
}

/**
 * @param string $sql
 * @return mixed
 */

function db_hodnota($sql = '')
{
    $q = mysqli_query($GLOBALS["DBC"], $sql);
    $r = mysqli_fetch_row($q);
    print ((is_object($GLOBALS["DBC"])) ? mysqli_error($GLOBALS["DBC"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));

    return $r[0];
}
