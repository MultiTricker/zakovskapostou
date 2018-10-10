<?php
if(!isLoggedIn())
{
    ?>
    <section id="promo" class="promo section offset-header has-pattern">
        <div class="container">
            <div class="row">
                <div class="overview col-md-8 col-sm-12 col-xs-12">
                    <h2 class="title">Známky a oznámení hlídáme za vás</h2>
                    <?php

                    $registrace = 0;

                    if(isset($_GET['zapomenute_heslo']) OR isset($_POST['zapomenute_heslo']))
                    {
                        echo '<div class="contact-form col-md-6 col-sm-12 col-xs-12 col-md-offset-1">
                              <h2>Reset hesla</h2>';

                        require_once dirname(__FILE__) . "/../forms/forgottenPassword.php";

                        echo "<p><a href='/'><i class='fa fa-arrow-left'></i> Zpět</a></p>
                        </div>";
                    }
                    else
                    {

                        echo '<div class="contact-form col-md-6 col-sm-12 col-xs-12 col-md-offset-1">';

                        if(isset($_GET['reset_hesla']) AND strlen($_GET['reset_hesla']) > 5)
                        {
                            echo "<h2>Reset hesla</h2>";

                            $reset_kod = mysqli_real_escape_string($GLOBALS["DBC"], $_GET['reset_hesla']);
                            $email = mysqli_real_escape_string($GLOBALS["DBC"], $_GET['email']);

                            $qMame = mysqli_query($GLOBALS["DBC"], "SELECT id
                                                                    FROM uzivatel
                                                                    WHERE email = '$email' AND kod_reset_hesla = '$reset_kod'");

                            if(mysqli_num_rows($qMame) == 1)
                            {
                                $noveHeslo = randomPassword();

                                $q2 = mysqli_query($GLOBALS["DBC"], "UPDATE uzivatel
                                                                     SET kod_reset_hesla = null,
                                                                         kod_reset_hesla_znovuzaslan = null,
                                                                         heslo = '" . password_hash($noveHeslo, PASSWORD_BCRYPT) . "'
                                                                     WHERE email = '$email'
                                                                           AND kod_reset_hesla = '$reset_kod';");

                                echo "<h2 style='color: lightgreen;'>Reset proběhl úspěšně. Vaše nové heslo je {$noveHeslo} - po přihlášení si jej, prosím, změňte.</h2>";
                            }
                            else
                            {
                                echo "<h2 style='color: lightsalmon;'>Nedošlo k resetu hesla - špatný e-mail nebo kód. Možná již byl využit?</h2>";
                            }

                        }
                        else
                        {
                            $registrace = 1;
                        }

                        echo "<h3>Přihlášení do aplikace</h3>";

                        require_once dirname(__FILE__) . "/../forms/login.php";

                        echo "<p><a href='?zapomenute_heslo=1' class='btn btn-mini btn-link' style='color: white;'><i class='fa fa-undo'></i> Zapomněl(a) jsem heslo.</a></p>";

                        // Aktivujeme účet?
                        if(isset($_GET['potvrzovaci_kod']) AND isset($_GET['email']))
                        {
                            validateUserEmail($_GET['potvrzovaci_kod'], $_GET['email']);
                            $registrace = 0;
                        }

                        if($registrace == 1)
                        {
                            echo "<br>";
                            echo "<h3>Registrace nového účtu</h3>";

                            require_once dirname(__FILE__) . "/../forms/register.php";
                        }

                        if(isLoggedIn() OR isset($_GET['zapomenute_heslo']) OR isset($_POST['zapomenute_heslo'])
                            OR isset($_GET['reset_hesla'])
                        )
                        {
                            echo "<p>&nbsp;</p>";
                        }

                        echo "</div>";

                    }
                    ?>
                </div>

                <div class="ipad ipad-white col-md-4 col-sm-12 col-xs-12 col-md-pull-1">
                    <div class="ipad-holder">
                        <div class="ipad-holder-inner">
                            <div class="slider flexslider">
                                <ul class="slides">
                                    <li>
                                        <img src="/assets/images/ipad/newSlide.jpg" alt=""/>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section id="features" class="features section">
        <div class="container">
            <div class="row">
                <h2 class="title text-center sr-only">Vlastnosti</h2>

                <div class="item col-md-3 col-sm-6 col-xs-12 text-center">
                    <div class="icon">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <div class="content">
                        <h3 class="title">Šetří spoustu času</h3>

                        <p>Změny hlídáme za vás. Několikrát denně.</p>
                    </div>
                </div>
                <div class="item col-md-3 col-sm-6 col-xs-12 text-center">
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <div class="content">
                        <h3 class="title">Pro celou rodinu</h3>

                        <p>Informace mají všichni po ruce.</p>
                    </div>
                </div>

                <div class="item col-md-3 col-sm-6 col-xs-12 text-center">
                    <div class="icon">
                        <i class="fa fa-line-chart"></i>
                    </div>
                    <div class="content">
                        <h3 class="title"><?php echo number_format(db_hodnota("SELECT COUNT(id) FROM zk WHERE smazana = 0 AND do_zmeny_odpojena = 0"), 0, "", "&nbsp;"); ?>&nbsp;žákovských</h3>
                        <p><?php echo number_format(db_hodnota("SELECT COUNT(id) FROM zaznam WHERE typ = 'znamka'"), 0, "", "&nbsp;"); ?>&nbsp;známek,
                            <?php echo number_format(db_hodnota("SELECT COUNT(id) FROM zaznam WHERE typ = 'oznameni'"), 0, "", "&nbsp;"); ?>&nbsp;oznámení,
                            <?php echo number_format(db_hodnota("SELECT MAX(id) FROM zk_historie"), 0, "", "&nbsp;"); ?>&nbsp;kontrol</p>
                    </div>
                </div>

                <div class="item col-md-3 col-sm-6 col-xs-12 text-center">
                    <div class="icon">
                        <i class="fa fa-hospital-o"></i>
                    </div>
                    <div class="content">
                        <h3 class="title">Pro žákovskou iSAS</h3>

                        <p>Od rodičů pro rodiče.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section id="how" class="how section has-pattern">
        <div class="container">
            <div class="row">
                <div class="content col-md-6 col-sm-6 col-xs-12 col-md-push-6 col-sm-push-6 text-center">
                    <h2 class="title">Jak to funguje</h2>

                    <p class="intro">Na portálu zakovskapostou.cz se během minuty registrujete a okamžitě se můžete přihlásit
                        do svého nově vytvořeného účtu.</p>

                    <p class="intro">V účtu nastavíte přístup do žákovské knížky. Nemusíte se bát, že bychom
                        přístup zneužili - není to možné.</p>

                    <p class="intro">Následně vám v případě nového oznámení na nástěnce či nové známky přijde e-mail se
                        změnami. Nic už vám neunikne.</p>

                </div>
                <div id="video-container"
                     class="video-container col-md-6 col-sm-6 col-xs-12 col-md-pull-6 col-sm-pull-6">
                    <img src="assets/images/HowTo.png" width="440" height="263">
                </div>
            </div>
        </div>
    </section>

    <section id="faq" class="faq section">
        <div class="container">
            <div class="row">
                <h2 class="title text-center">Často kladené otázky</h2>

                <div class="faq-col col-md-6 col-sm-6 col-xs-12">
                    <div class="item">
                        <h3 class="question"><i class="fa fa-question-circle"></i>Kolik služba stojí? Pro koho je?</h3>

                        <p class="answer">Služba je určená především pro 1. ZŠ Karla Jeřábka v Roudnici nad Labem a je pro všechny zdarma. Budeme moc rádi, když ji budete využívat a bude vám dobrým pomocníkem.</p>
                    </div>
                </div>

                <div class="faq-col col-md-6 col-sm-6 col-xs-12">
                    <div class="item">
                        <h3 class="question"><i class="fa fa-question-circle"></i>Můžu se na zakovskapostou.cz spolehnout?
                        </h3>

                        <p class="answer">Sami službu využíváme a udržujeme, aby byla bechybná a plně funkční.</p>

                        <p class="answer">Náš server nikdy nerozesílal SPAM, <a href="https://www.senderbase.org/lookup/?search_string=89.221.208.65" target="_blank">má skvělé skóre ohledně důvěryhodnosti</a>, e-maily jsou podepsané DKIMem a máme nastavené SPF.</p>
                    </div>
                </div>

                <div class="faq-col col-md-6 col-sm-6 col-xs-12">
                    <div class="item">
                        <h3 class="question"><i class="fa fa-question-circle"></i>Jak často probíhá kontrola žákovské?</h3>

                        <p class="answer">Přes týden probíhá odpoledne v časech po 14:00, 16:00, 18:00, 20:00 a 22:00. O víkendy po 12:00, 14:00, 16:00 a 18:00.</p>

                        <p class="answer">Kontroly v tomto časovém rozvrhu jsou takto schválně, protože se ukázaly být nejvíce efektivní - uživatelům nepřijde mnoho e-mailů, změny mají spíše souhrnně.</p>
                    </div>
                </div>

                <div class="faq-col col-md-6 col-sm-6 col-xs-12">
                    <div class="item">
                        <h3 class="question"><i class="fa fa-question-circle"></i>Jsou má data v bezpečí?</h3>

                        <p class="answer">Ano, je to naše priorita. Naše aplikace neběží na veřejném sdíleném hostingu, server je pravidelně aktualizovaný, služby nejsou otevřené světu, na doméně je aktivní DNSSEC, provoz probíhá šifrovaně přes HTTPS, uživatelská hesla šifrujeme BCRYPTem.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <section id="story" class="story section has-pattern">
        <div class="container">
            <div class="row">
                <div class="content col-md-8 col-sm-8 col-xs-12 text-center">
                    <h2 class="title">Vznik služby</h2>

                    <p>Službu jsme vytvořili na základě naší potřeby.
                        Ruční kontrola žákovské knížky je poměrně náročná - samotné přihlášení a proklikání se
                        žákovskou je kvůli odezvě pomalé a pokud chcete být opravdu v obraze, tak je potřeba
                        žákovskou kontrolovat několikrát denně. Oznámení a známky tam totiž učitelé mohou (pochopitelně) vložit kdykoliv.</p>

                    <p>Proto jsme zpracovali řešení, které tyto změny hlídá za nás a oznámí je do
                        e-mailu. Aplikaci dále šíříme, aby sloužila co největšímu počtu
                        uživatelů.</p>

                    <p>Za jakékoliv náměty a pomoc při propagaci budeme rádi - čím více rodičů bude aplikaci používat, tím větší má smysl.</p>

                </div>

                <div class="team col-md-3 col-sm-3 col-md-offset-1 col-sm-offset-1 col-xs-12">
                    <div class="row">
                        <div class="member col-md-12 text-center">
                            <img class="img-rounded" src="/assets/images/team/MultiTricker.png" alt=""/>

                            <p class="name">Michal Ševčík</p>

                            <p class="title">Autor</p>
                            <ul class="connect list-inline">
                                <li><a href="https://twitter.com/MultiTricker"><i class="fa fa-twitter"></i></a></li>
                                <li>
                                    <a href="https://www.linkedin.com/profile/view?id=23755966&trk=nav_responsive_tab_profile"><i
                                                class="fa fa-linkedin"></i></a></li>
                                <li class="row-end"><a href="https://github.com/MultiTricker/"><i
                                                class="fa fa-github"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" class="testimonials section">
        <div class="container">
            <div class="row">
                <h2 class="title text-center">Ohlasy k aplikaci</h2>

                <div class="item col-md-4 col-sm-4">
                    <div class="quote-box">
                        <i class="fa fa-quote-left"></i>
                        <blockquote class="quote">
                            Víme o všem hned z první ruky. Perfektní!</a>
                        </blockquote>

                    </div>

                    <div class="people row">
                        <img class="img-rounded user-pic col-md-5 col-sm-5 col-xs-12 col-md-offset-1 col-sm-offset-1"
                             src="/assets/images/people/Uzivatel.png" alt=""/>

                        <p class="details text-center pull-left">
                            <span class="name"><a href="http://www.multigutka.cz">Michal</a></span>
                        </p>
                    </div>

                </div>

                <div class="item col-md-4 col-sm-4">
                    <div class="quote-box">
                        <i class="fa fa-quote-left"></i>
                        <blockquote class="quote">
                            Perfektní služba a skvělý pomocník.
                        </blockquote>

                    </div>

                    <div class="people row">
                        <img class="img-rounded user-pic col-md-5 col-sm-5 col-xs-12 col-md-offset-1 col-sm-offset-1"
                             src="/assets/images/people/Uzivatel.png" alt=""/>

                        <p class="details text-center pull-left">
                            <span class="name"><a href="http://www.multigutka.cz">Petra</a></span>
                        </p>
                    </div>

                </div>

                <div class="item col-md-4 col-sm-4">
                    <div class="quote-box">
                        <i class="fa fa-quote-left"></i>
                        <blockquote class="quote">
                            Luxusní služba.
                        </blockquote>

                    </div>

                    <div class="people row">
                        <img class="img-rounded user-pic col-md-5 col-sm-5 col-xs-12 col-md-offset-1 col-sm-offset-1"
                             src="/assets/images/people/Mojmir.jpg" alt=""/>

                        <p class="details text-center pull-left">
                            <span class="name">Mojmír</span>
                        </p>
                    </div>

                </div>

            </div>

            <?php /* ?>
    <div class="row">
      <div class="item col-md-4 col-sm-4 col-md-offset-2 col-sm-offset-2">
        <div class="quote-box">
          <i class="fa fa-quote-left"></i>
          <blockquote class="quote">
            DeltaApp is fab lorem ipsum dolor sit amet proin sagittis sodales pulvinar Mauris id arcu eget augue condimentum euismod: <a href="#">http://bit.ly/1gB9UBR</a>
          </blockquote>
        </div>
        <div class="people row">
          <img class="img-rounded user-pic col-md-5 col-sm-5 col-xs-12 col-md-offset-1 col-sm-offset-1" src="/assets/images/people/Uzivatel.png" alt="" />
          <p class="details text-center pull-left">
            <span class="name">Annie Lee</span>
            <span class="title">Berlin, Germany</span>
          </p>
        </div>
      </div>
      <div class="item col-md-4 col-sm-4">
        <div class="quote-box">
          <i class="fa fa-quote-left"></i>
          <blockquote class="quote">
            DeltaApp is a great dolor sit amet proin sagittis sodales pulvinar vestibulum porta dolor molestie semper.: <a href="#">http://bit.ly/1gB9UBR</a>
          </blockquote>
        </div>
        <div class="people row">
          <img class="img-rounded user-pic col-md-5 col-sm-5 col-xs-12 col-md-offset-1 col-sm-offset-1" src="/assets/images/people/Uzivatel.png" alt="" />
          <p class="details text-center pull-left">
            <span class="name">Adam Gordon</span>
            <span class="title">London, UK</span>
          </p>
        </div>
      </div>
    </div>
<?php */ ?>
        </div>

    </section>
    <?php
}

// Sekce s vlastnim uctem
if(isLoggedIn())
{
    echo '<section id="how" class="how section has-pattern">';
    ?>

    <div class="container">
        <div class="row text-center">
            <?php
            // Registrace a prihlaseni
            require dirname(__FILE__) . "/ucet.php";
            ?>
        </div>
    </div>
    </div>
    </section>

    <?php
}