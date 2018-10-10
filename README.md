Žákovská poštou
===============

Jednoduchá aplikace, která se přihlásí do systému žákovské iSAS, stáhne klasifikaci a oznámení, porovná s již 
staženými záznamy a novinky pošle uživateli na e-mail. Používá PHP a MySQL. Nemá administraci nad uživatelskými účty, 
to je potřeba řešit přímo v databázi přes tabulky.

Zdejší kód na GitHubu je okleštěný o zakoupenou šablonu:
https://wrapbootstrap.com/theme/delta-promote-mobile-app-bootstrap-4-WB09R23P8

Doporučený CRON pro automatickou kontrolu (cestu upravte podle sebe):

    */5 14-22/2 * * 1-5 /usr/bin/php -f /var/www/zakovskapostou.cz/web/app/cron/zakovskaNova.php
    */15 12-18/2 * * 0,6 /usr/bin/php -f /var/www/zakovskapostou.cz/web/app/cron/zakovskaNova.php
    
Schéma databáze naleznete v *databaze-zakovska-postou.sql*, nastavení do databáze upravíte v *app/fg/dbConnect.php*. 
Dále přepište doménu "zakovskapostou" svojí vlastní.