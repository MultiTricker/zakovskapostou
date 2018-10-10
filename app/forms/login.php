<?php

$form = new Nette\Forms\Form("login");
require __DIR__ . "/renderer.php";

$form->setMethod("POST");
if(in_array($_SERVER['HTTP_HOST'], ["zakovskapostou.cz", "www.zakovskapostou.cz"]))
{
    $form->setAction("https://zakovskapostou.cz");
}
$form->addHidden("logInAndDoNotForget", 1);
$form->addText("email", "E-mail: ")
    ->setType('email')
    ->setRequired("Vyplňte Váš e-mail použitý pro přístup do administrace.")
    ->addRule(Nette\Forms\Form::EMAIL, 'Musíte vyplnit platnou e-mailovou adresu.')
    ->addRule('zpEmailExists', "Tento e-mail na tomto serveru není registrovaný.");
$form->addPassword("heslo", "Heslo: ")
    ->setRequired("Vyplňte Vaše heslo.")
    ->addRule(Nette\Forms\Form::MIN_LENGTH, 'Vaše heslo musí být dlouhé alespoň %d znaků. Toto jste při registraci nemohli použít.', 6)
    ->addRule('zpValidLogin', "Tyto přihlašovací údaje nejsou správné.");
$form->addSubmit("odeslat", "Přihlásit se")->setAttribute('class', 'btn btn-primary');
$form->setDefaults([]);

if(!$form->isSuccess())
{
    $form->render();
}
