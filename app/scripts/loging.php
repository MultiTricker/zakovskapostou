<?php

// Pocatecni inicializace
if(isset($_POST['email']))
{
    $_SESSION['email'] = $_POST['email'];
}
if(!isset($_POST['logInAndDoNotForget']))
{
    $_POST['logInAndDoNotForget'] = 0;
}

if(isset($_COOKIE['email']))
{
    $_SESSION['email'] = $_COOKIE['email'];
}
if(isset($_COOKIE['chksum']))
{
    $_SESSION['chksum'] = $_COOKIE['chksum'];
}

// Vytvorime jedinecny session pro nastaveni odhlaseni o urcite delce
$odhl = getChksum(90);

// Aplikujeme odhlaseni
mysqli_query($GLOBALS['DBC'], "UPDATE uzivatel 
                               SET session='$odhl'
                               WHERE posledni_prihlaseni < now()-interval 4320 minute;");

// Obcerstvime cas u prihlaseneho uzivatele
if(isset($_SESSION['email']))
{
    mysqli_query($GLOBALS["DBC"], "UPDATE uzivatel
                                   SET posledni_prihlaseni=now()
                                   WHERE email=\"" . mysqli_real_escape_string($GLOBALS["DBC"], $_SESSION['email']) . "\"");
}

// Prvni prihlaseni?
if($_POST['logInAndDoNotForget'] == "1")
{
    $exist = mysqli_query($GLOBALS["DBC"], "SELECT email, heslo
                                            FROM uzivatel
                                            WHERE email=\"" . mysqli_real_escape_string($GLOBALS["DBC"], $_SESSION['email']) . "\"");

    // Uzivatel neexistuje
    if(mysqli_num_rows($exist) == 0)
    {
        // Kiks
        if(isset($_SESSION))
        {
            unset($_SESSION);
        }
        $fault = 1;
        require dirname(__FILE__) . "/../../index.php";
        exit;
    }
    else
    {
        $u = mysqli_fetch_assoc($exist);
        // Je heslo OK?
        if(password_verify($_POST['heslo'], $u['heslo']))
        {
            // Existuje, vytvorime mu session pro relaci
            $chksum = getChksum(80);

            // Aktualizujeme to
            mysqli_query($GLOBALS["DBC"], "UPDATE uzivatel
                                       SET posledni_prihlaseni=now()
                                       WHERE email=\"" . mysqli_real_escape_string($GLOBALS["DBC"], $_SESSION['email']) . "\"");

            mysqli_query($GLOBALS["DBC"], "UPDATE uzivatel
                                       SET session='$chksum'
                                       WHERE email=\"" . mysqli_real_escape_string($GLOBALS["DBC"], $_SESSION['email']) . "\"");

            $_SESSION['chksum'] = $chksum;
        }
        else
        {
            // Kiks
            if(isset($_SESSION))
            {
                unset($_SESSION);
            }
            $fault = 1;
            require dirname(__FILE__) . "/../../index.php";
            exit;
        }
    }
}
else
{
    if(isset($_SESSION['email']) AND $_SESSION['email'] != "")
    {
        if(!isset($_SESSION['chksum']))
        {
            $_SESSION['chksum'] = "";
        }

        $exist = mysqli_query($GLOBALS["DBC"], "SELECT email, session
                                              FROM uzivatel
                                              WHERE email=\"" . mysqli_real_escape_string($GLOBALS["DBC"], $_SESSION['email']) . "\" AND session=\"" . mysqli_real_escape_string($GLOBALS["DBC"], $_SESSION['chksum']) . "\"");

        if(mysqli_num_rows($exist) < "1")
        {
            // Kiks
            if(isset($_SESSION))
            {
                unset($_SESSION);
            }
            $fault = 2;
            require dirname(__FILE__) . "/../../index.php";
            exit;
        }
    }
    else
    {
        // Kiks
        if(isset($_SESSION))
        {
            unset($_SESSION);
        }
        $fault = 2;
        require dirname(__FILE__) . "/../../index.php";
        exit;
    }
}

if(isset($_GET['odhlaseni']) AND $_GET['odhlaseni'] == 1 AND (!isset($_POST['logInAndDoNotForget']) OR $_POST['logInAndDoNotForget'] == 0))
{
    $query = mysqli_query($GLOBALS["DBC"], "UPDATE uzivatel
                                            SET session='$odhl'
                                            WHERE email=\"" . mysqli_real_escape_string($GLOBALS["DBC"], $_SESSION['email']) . "\" AND session=\"" . mysqli_real_escape_string($GLOBALS["DBC"], $_SESSION['chksum']) . "\"");

    // Kiks
    if(isset($_SESSION))
    {
        unset($_SESSION);
    }

    unset($_COOKIE['email']);
    unset($_COOKIE['chksum']);
    setcookie('email', null, -1, '/', ".zakovskapostou.cz");
    setcookie('chksum', null, -1, '/', ".zakovskapostou.cz");

    $fault = 3;
    require dirname(__FILE__) . "/../../index.php";
    exit;
}

// Jsme prihlaseni? Jestli jo, nacteme si data o uzivateli
if(isLoggedIn())
{
    $qUzivatel = mysqli_query($GLOBALS["DBC"], "SELECT *
                                                FROM uzivatel
                                                WHERE email = \"" . mysqli_real_escape_string($GLOBALS["DBC"], $_SESSION['email']) . "\";");

    $uzivatel = mysqli_fetch_assoc($qUzivatel);

    // Nastavime cookie
    setcookie("email", $_SESSION['email'], time() + 7200, "/", ".zakovskapostou.cz");
    setcookie("chksum", $_SESSION['chksum'], time() + 7200, "/", ".zakovskapostou.cz");
}