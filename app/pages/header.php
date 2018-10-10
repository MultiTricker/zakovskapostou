<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<head>
    <title>Žákovská poštou</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="stylesheet" href="/assets/all.css">
    <!--[if lt IE 9]>
    <![endif]-->
</head>

<body data-spy="scroll">
<header id="top" class="header navbar-fixed-top">
    <div class="container">
        <h1 class="logo pull-left">
            <a href="https://zakovskapostou.cz">
                <img id="logo-image" class="logo-image" src="/assets/images/logo/logo.png" alt="Logo">
                <span class="logo-title">Žákovská poštou</span>
            </a>
        </h1>
        <nav id="main-nav" class="main-nav navbar-right" role="navigation">
            <div class="navbar-header">
                <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="navbar-collapse collapse" id="navbar-collapse">
                <ul class="nav navbar-nav">
                    <?php if(!isLoggedIn()) { ?>
                        <li class="nav-item last"><a class="scrollto" href="#promo">Můj účet</a></li>
                        <li class="nav-item"><a class="scrollto" href="#features">Vlastnosti</a></li>
                        <li class="nav-item"><a class="scrollto" href="#how">Jak to funguje</a></li>
                        <li class="nav-item"><a class="scrollto" href="#faq">FAQ</a></li>
                        <li class="nav-item"><a class="scrollto" href="#story">Vznik služby</a></li>
                        <?php
                    }
                    else
                    {
                        // <li class=\"nav-item last\"><a href='#' title='Ověřovací kód byl odeslán na e-mail při registraci.'><b>Ověřený e-mail:</b> " . ($uzivatel['overeny'] == 1 ? "ano" : "ne") . "</a></li>
                        echo "<li class=\"nav-item\"><a href='?zmenitHeslo=1' class='btn btn-default'><i class='fa fa-key' style='color:darkgrey;'></i> Změnit heslo</a></li>
                          <li class=\"nav-item\"><a href='?odhlaseni=1' class='btn btn-default'><i class='fa fa-sign-out' style='color:darkgrey;'></i>{$_SESSION['email']} - odhlásit</a></li>";
                    }
                    ?>
                </ul>
            </div>
        </nav>
    </div>
</header>

<?php

if(isset($fault) AND ($fault == 2 AND $fault == 3))
{

    echo "<section id='fault' class='section offset-header'>
      <div class='container'>
        <div class='row'>";

    if($fault == "2")
    {
        echo "<center><div class='alert alert-danger'>Zadané heslo nebo jméno nebylo nalezeno.<br>Při přihlašování je také brán ohled na malá a velká písena.<br />Je rovněž možné, že vypršel časový limit Vašeho přihlášení a byl(a) jste z bezpečnostních důvodů odhlášen(a).</div></center>";
    }
    if($fault == "3")
    {
        echo "<center><div class='alert alert-success'>Byl(a) jste úspěšně odhlášen(a).</div></center>";
    }

    echo "</div>
      </div>
    </section>";

}