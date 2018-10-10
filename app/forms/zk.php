<?php

echo "<div class='alert alert-info'>Jako uživatelské jméno a heslo vyplňte údaje, kterými se přihlašujete do žákovské knížky na stránkách školy.</div>";

$qSkoly = mysqli_query($GLOBALS["DBC"], "SELECT id, nazev FROM skola ORDER BY nazev DESC");
$skoly = [];
while($r = mysqli_fetch_assoc($qSkoly))
{
    $skoly[$r['id']] = $r['nazev'];
}

$form = new Nette\Forms\Form("zk");
require __DIR__ . "/renderer.php";

$form->setMethod("POST");
$form->setAction("");
$form->addHidden("id_uzivatele");
$skola = $form->addSelect("id_skola", "Škola:", $skoly);
$jmeno = $form->addText("jmeno", "Uživatelské jméno: ")
    ->setRequired()
    ->addRule('zpAvailableUser', "Je mi líto, ale tento uživatel na této škole již v systému existuje.");
$form->addPassword("heslo", "Heslo: ")
    ->setRequired();
$form->addPassword("heslo2", "Heslo znovu pro kontrolu: ")
    ->addRule(Nette\Forms\Form::EQUAL, 'Zadaná hesla se neshodují', $form['heslo'], [$skola, $jmeno]);

if($uprava == 1)
{
    $form->addHidden("id_zk", $zk['id']);
    $form->setDefaults($zk);
    $form->addSubmit("odeslat", "Upravit žákovskou")->setAttribute('class', 'btn btn-primary');
}
else
{
    $form->addSubmit("odeslat", "Přidat žákovskou")->setAttribute('class', 'btn btn-primary');
}

if($form->isSuccess())
{
    if($uprava == 0)
    {
        mysqli_query($GLOBALS["DBC"], "INSERT INTO zk(id_uzivatel, id_skola, jmeno, heslo)
                                       VALUES(\"{$uzivatel['id']}\",
                                              \"" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['id_skola']) . "\",
                                              \"" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['jmeno']) . "\",
                                              \"" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['heslo']) . "\");");
        echo mysqli_error($GLOBALS["DBC"]);
        $idZakovske = mysqli_insert_id($GLOBALS["DBC"]);

        // Vlozime pro sledovani uzivateluv e-mail
        mysqli_query($GLOBALS["DBC"], "INSERT INTO email(id_zakovska, email) VALUES('$idZakovske', \"{$uzivatel['email']}\");");

        echo "<p class='alert alert-success'>Žákovská byla přidána. Její kontrola proběhne odpoledne v časech po 14:00, 16:00, 18:00, 20:00 a 22:00 (o víkendy po 12:00, 14:00, 16:00 a 18:00).</p>";

        // At mame radost z pouzivani
        mail("multi@tricker.cz", "Pridana zakovska " . $_POST['jmeno'], "User: " . $uzivatel['email']);
    }
    elseif($uprava == 1)
    {
        mysqli_query($GLOBALS["DBC"], "UPDATE zk 
                                       SET jmeno = \"" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['jmeno']) . "\", 
                                           heslo = \"" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['heslo']) . "\", 
                                           posledni_kontrola = null, 
                                           posledni_uspesny_login = null, 
                                           do_zmeny_odpojena = 0 
                                       WHERE id = '" . intval($_POST['id_zk']) . "'
                                         AND id_uzivatel = '{$uzivatel['id']}';");

        echo mysqli_error($GLOBALS["DBC"]);

        echo "<p class='alert alert-success'>Žákovská byla upravena.</p>";
    }
}
else
{
    $form->render();
}
