<?php

$form = new Nette\Forms\Form("email");
require __DIR__ . "/renderer.php";

$form->setMethod("POST");
$form->setAction("");
$form->addHidden("id_uzivatele");
$form->addHidden("id_zakovska")
    ->setValue($_GET['zakovska']);
$form->addText("email", "E-mail: ")
    ->setRequired()
    ->addRule(Nette\Forms\Form::EMAIL, 'Musíte vyplnit platnou e-mailovou adresu.');
if($uprava == 1)
{
    $form->addHidden("id_email", $email['id']);
    $form->setDefaults($email);
    $form->addSubmit("odeslat", "Upravit e-mail")
        ->setAttribute('class', 'btn btn-primary');
}
else
{
    $form->setDefaults([]);
    $form->addSubmit("odeslat", "Přidat e-mail")
        ->setAttribute('class', 'btn btn-primary');
}

if($form->isSuccess() AND $uprava == 0)
{
    $muzeme = mysqli_query($GLOBALS["DBC"], "SELECT id FROM zk WHERE id = '{$_POST['id_zakovska']}' AND id_uzivatel = '{$uzivatel['id']}'");

    if(mysqli_num_rows($muzeme) == 1)
    {

        mysqli_query($GLOBALS["DBC"], "INSERT INTO email(id_zakovska, email, smazany)
                                       VALUES(\"" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['id_zakovska']) . "\",
                                              \"" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['email']) . "\",
                                              \"0\");");

        echo "<p class='alert alert-success'>E-mail byl přidán.</p>";

    }
    else
    {
        echo "<p class='alert alert-danger'>E-mail nemohl být přidán pod účet, který nevlastníte.</p>";
    }
}
elseif($form->isSuccess() AND $uprava == 1)
{
    $muzeme = mysqli_query($GLOBALS["DBC"], "SELECT id FROM zk WHERE id = '{$_POST['id_zakovska']}' AND id_uzivatel = '{$uzivatel['id']}'");

    if(mysqli_num_rows($muzeme) == 1)
    {

        mysqli_query($GLOBALS["DBC"], "UPDATE email
                                       SET email = \"" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['email']) . "\"
                                       WHERE id = '" . intval($_POST['id_email']) . "';");

        echo mysqli_error($GLOBALS["DBC"]);

        echo "<p class='alert alert-success'>E-mail byl upraven.</p>";

    }
    else
    {
        echo "<p class='alert alert-danger'>E-mail nebyl přidaný pod žákovskou, která není pod vaším účtem.</p>";
    }
}
else
{
    $form->render();
}