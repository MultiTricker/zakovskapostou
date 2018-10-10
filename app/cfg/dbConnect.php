<?php

if(!($GLOBALS["DBC"] = MySQLi_connect("localhost", "user", "password")))
{
    die ("<div style=\"float: center; margin: auto; background-color:white; border: solid red 5px; padding: 5px; font-weight: bold;\">
          Nepodarilo se spojit s databazovym serverem a prihlasit se. Prosim, zkontrolujte nastaveni.<br>
          Unable to connect to database server and log in. Please, check out your settings.
          </div>");
}

if(!((bool)mysqli_query($GLOBALS["DBC"], "USE database")))
{
    die ("<div style=\"float: center; margin: auto; background-color:white; border: solid red 5px; padding: 5px; font-weight: bold;\">
          Chyba ve vybrani databaze \"{$dbDb}\". Prosim, zkontrolujte nastaveni.<br>
          Unable to select database \"{$dbDb}\". Please, check out your settings.
          </div>");
}

mysqli_query($GLOBALS["DBC"], "SET NAMES utf8;");