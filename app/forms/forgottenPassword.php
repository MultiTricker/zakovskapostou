<?php

$form = new Nette\Forms\Form("password");
require __DIR__ . "/renderer.php";

$form->setMethod("POST");
$form->setAction("");
$form->addHidden("zapomenute_heslo", 1);
$form->addText("email", "E-mail: ")
    ->setType('email')
    ->setRequired("Vyplňte e-mail, pro který chcete získat nové heslo.")
    ->addRule(Nette\Forms\Form::EMAIL, 'Musíte vyplnit platnou e-mailovou adresu.')
    ->addRule('zpEmailExists', "Je mi líto, ale tento e-mail na zakovskapostou.cz není registrovaný.");
$form->addSubmit("odeslat", "Zaslat e-mail s kódem pro reset hesla")->setAttribute('class', 'btn btn-primary');

if($form->isSuccess())
{
    $qData = mysqli_query($GLOBALS["DBC"], "SELECT id
                                            FROM uzivatel
                                            WHERE email = '{$_POST['email']}'
                                                  AND (kod_reset_hesla_znovuzaslan IS NULL
                                                       OR kod_reset_hesla_znovuzaslan < NOW() - INTERVAL 1 HOUR);");

    if(mysqli_num_rows($qData) == 1)
    {
        $kod_pro_reset = substr(strtoupper(md5(rand(100000, 9999999))), 0, 4) . rand(100000, 999999);

        mysqli_query($GLOBALS["DBC"], "UPDATE uzivatel
                                       SET kod_reset_hesla = \"{$kod_pro_reset}\",
                                           kod_reset_hesla_znovuzaslan = now()
                                       WHERE email = '{$_POST['email']}';");

        // Send e-mail with reset code
        try
        {
            require_once dirname(__FILE__) . "/../../vendor/swiftmailer/swiftmailer/lib/swift_required.php";
            $transport = Swift_SmtpTransport::newInstance('localhost', 25);
            $mailer = Swift_Mailer::newInstance($transport);
            $message = Swift_Message::newInstance()
                ->setCharset("UTF-8")
                ->setSubject("zakovskapostou.cz - odkaz na změnu hesla")
                ->setFrom("registrace@zakovskapostou.cz")
                ->setTo($_POST['email'])
                ->setContentType("text/html");
            $message->setBody("<html><body>
<p>Ahoj,</p>

<p>tento e-mail Ti přišel, protože jsi na zakovskapostou.cz odeslal(a) žádost o nové heslo k účtu. Pokud to udělal někdo jiný, tak se není čeho obávat - k žádné změně nedojde, dokud neotevřeš odkaz níže.</p>

<p>Pokud jsi zapomněl(a) své heslo, tak nové můžeš získat na tomto odkazu: <a href='http://www.zakovskapostou.cz/index.php?reset_hesla={$kod_pro_reset}&email=" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['email']) . "'>http://www.zakovskapostou.cz/index.php?reset_hesla={$kod_pro_reset}&email=" . mysqli_real_escape_string($GLOBALS["DBC"], $_POST['email']) . "</a></p>

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

        echo "<p class='alert alert-success'>E-mail s kódem pro reset hesla byl zaslán.</p>";
    }
    else
    {
        echo "<p class='alert alert-danger'>E-mail s kódem pro reset hesla je možné poslat pouze jednou za hodinu.</p>";
    }
}
else
{
    $form->render();
}
