<?php

$form = new Nette\Forms\Form("password");
require __DIR__ . "/renderer.php";

$form->setMethod("POST");
$form->setAction("");
$form->addPassword("stare_heslo", "Staré heslo: ")
    ->setRequired("Vyplňte správně Vaše staré heslo.")
    ->addRule('oldPasswordCheck', "Staré heslo se neshoduje s tím, které jste zadali.");
$form->addPassword("nove_heslo", "Nové heslo:")
    ->setRequired("Vyplňte Vaše nové heslo.")
    ->addRule(Nette\Forms\Form::MIN_LENGTH, 'Vaše heslo musí být dlouhé alespoň %d znaků.', 6);
$form->addPassword("nove_heslo_znovu", "Nové heslo znovu pro kontrolu: ")
    ->setRequired("Vyplňte znovu Vaše nové heslo pro kontrolu, že je správně zadané.")
    ->addRule(Nette\Forms\Form::EQUAL, 'Zadaná hesla se neshodují', $form['nove_heslo']);
$form->addSubmit("odeslat", "Změnit heslo")->setAttribute('class', 'btn btn-primary');

if($form->isSuccess())
{
    $_POST['nove_heslo'] = password_hash($_POST['nove_heslo'], PASSWORD_BCRYPT);

    mysqli_query($GLOBALS["DBC"], "UPDATE uzivatel
                                   SET heslo = \"" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['nove_heslo']) . "\"
                                   WHERE id = '{$uzivatel['id']}';");

    echo "<p class='alert alert-success'>Heslo bylo změněno</p>";
}
else
{
    $form->render();
}
