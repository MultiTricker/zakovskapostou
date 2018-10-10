<?php

$form = new Nette\Forms\Form("registration");
require __DIR__ . "/renderer.php";

$form->setMethod("POST");
$form->setAction("https://zakovskapostou.cz");
$form->addText("email", "E-mail: ")
    ->setType('email')
    ->setRequired("Vyplňte Váš e-mail, bude sloužit pro přístup do administrace.")
    ->addRule(Nette\Forms\Form::EMAIL, 'Musíte vyplnit platnou e-mailovou adresu.')
    ->addRule('zpAvailableEmail', "Je nám líto, ale tento e-mail je již zabraný.");
$form->addPassword("password", "Heslo: ")
    ->setRequired("Vyplňte Vaše heslo.")
    ->addRule(Nette\Forms\Form::MIN_LENGTH, 'Vaše heslo musí být dlouhé alespoň %d znaků.', 6);
$form->addPassword("passwordAgain", "Opakujte heslo: ")
    ->setRequired("Vyplňte znovu Vaše heslo pro kontrolu.")
    ->addRule(Nette\Forms\Form::EQUAL, 'Zadaná hesla se neshodují', $form['password']);
$form->addSubmit("odeslat", "Registrovat")->setAttribute('class', 'btn btn-primary');
$form->setDefaults([]);

if($form->isSuccess())
{
    // Prepare data
    $potvrzovaci_kod = "AC" . rand(100000, 999999);
    $_POST['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Insert
    mysqli_query($GLOBALS["DBC"], "INSERT INTO uzivatel(email, heslo, overeny, potvrzovaci_kod, vytvoren_kdy, ip)
                                   VALUES(\"" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['email']) . "\",
                                   \"" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['password']) . "\",
                                   1, \"{$potvrzovaci_kod}\", NOW(), \"" . $_SERVER['REMOTE_ADDR'] . "\")");

    echo mysqli_error($GLOBALS["DBC"]);
    /*
        // Send e-mail with validation code
        // Afterall not used - no need to
        try
        {
            require dirname(__FILE__) . "/../../vendor/swiftmailer/swiftmailer/lib/swift_required.php";
            $transport = Swift_SmtpTransport::newInstance('localhost', 25);
            $mailer = Swift_Mailer::newInstance($transport);
            $message = Swift_Message::newInstance()
                ->setCharset("UTF-8")
                ->setSubject("zakovskapostou.cz - potvrzení registrace")
                ->setFrom("registrace@zakovskapostou.cz")
                ->setTo($_POST['email'])
                ->setContentType("text/html");
            $message->setBody("<html><body>
    <p>Ahoj,</p>

    <p>díky za registraci na portále zakovskapostou.cz!</p>

    <p>Otevři prosím následující odkaz pro aktivaci e-mailu: <a href='http://www.zakovskapostou.cz/index.php?potvrzovaci_kod={$potvrzovaci_kod}&email=" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['email']) . "'>http://www.zakovskapostou.cz/index.php?potvrzovaci_kod={$potvrzovaci_kod}&email=" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['email']) . "</a></p>

    <p>Pokud jsi se na tomto portálu neregistroval(a), tento e-mail prosím ignoruj. Žádné další zprávy už nebudou chodit.</p>

    <p><b><i>zakovskapostou.cz</i></b></p></body></html>", 'text/html');

            $mailer->send($message);
            unset($transport);
            unset($message);
            unset($mailer);

        } catch(Exception $h)
        {
            echo "<font style='color: darkred;'><b>" . $h->getMessage() . "</b></font><br />";
        }
    */
    // Thank you!
    echo "<p class='alert alert-success'><b>Díky za registraci!</b><br />
          Nyní se můžeš přihlásit do svého nově vytvořeného účtu a přidat údaje k žákovské knížce.</p>";

}
else
{
    $form->render();
}