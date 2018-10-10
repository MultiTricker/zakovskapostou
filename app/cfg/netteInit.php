<?php

require_once __DIR__ . "/../scripts/nette.phar";

$configurator = new Nette\Configurator;
//$configurator->setDebugMode('IP');
//$configurator->enableDebugger(__DIR__ . '/../../log', "multi@tricker.cz");
$configurator->setTempDirectory(__DIR__ . '/../../temp');
$configurator->addConfig(__DIR__ . '/zp.neon');
$container = $configurator->createContainer();

session_name("zakovskapostou");
ini_set("session.gc_maxlifetime", "7200");
ini_set("session.cookie_domain", ".zakovskapostou.cz");
ini_set('session.save_path', dirname(__FILE__) . "/../../../tmp");
session_save_path(dirname(__FILE__) . "/../../../tmp");

// seshny
if(!isset($_SESSION))
{
    session_start();
}