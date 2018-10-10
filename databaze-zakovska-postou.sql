-- Adminer 4.6.3 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `email`;
CREATE TABLE `email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_zakovska` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `smazany` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_zakovska` (`id_zakovska`),
  KEY `email` (`email`),
  CONSTRAINT `email_ibfk_1` FOREIGN KEY (`id_zakovska`) REFERENCES `zk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kdy` datetime NOT NULL,
  `akce` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `popis` text COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `akce` (`akce`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `skola`;
CREATE TABLE `skola` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nazev` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `url_zakovska` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `typ_zakovske` varchar(40) COLLATE utf8_czech_ci NOT NULL DEFAULT 'iSAS',
  `verze_zakovske` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nazev` (`nazev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `skola` (`id`, `nazev`, `url_zakovska`, `typ_zakovske`, `verze_zakovske`) VALUES
(1,	'ZŠ Karla Jeřábka, Roudnice nad Labem',	'https://www.zskjerabka-rce.cz/isas/',	'iSAS',	'8.0.7'),
(2,	'SŠ, ZŠ a MŠ pro ZZ, Brno',	'http://www.sss-ou.cz/ISAS/',	'iSAS',	'8.0.7');

DROP TABLE IF EXISTS `uzivatel`;
CREATE TABLE `uzivatel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `heslo` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `overeny` tinyint(4) NOT NULL,
  `posledni_prihlaseni` datetime NOT NULL,
  `session` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `potvrzovaci_kod` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `vytvoren_kdy` datetime NOT NULL,
  `ip` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `potvrzovaci_kod_znovuzaslan` datetime DEFAULT NULL,
  `kod_reset_hesla` varchar(10) COLLATE utf8_czech_ci DEFAULT NULL,
  `kod_reset_hesla_znovuzaslan` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `zaznam`;
CREATE TABLE `zaznam` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_zk` int(11) NOT NULL COMMENT 'ID žákovské.',
  `typ` varchar(10) COLLATE utf8_czech_ci NOT NULL COMMENT 'Typ záznamu znamka/nastenka.',
  `zaznam` text COLLATE utf8_czech_ci NOT NULL COMMENT 'Samotný záznam.',
  `md5` varchar(64) COLLATE utf8_czech_ci NOT NULL COMMENT 'Kontrolní součet id_zk + zaznam.',
  `kdy` datetime NOT NULL COMMENT 'Kdy došlo ke vložení do tabulky.',
  `novy` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Nový záznam?',
  PRIMARY KEY (`id`),
  KEY `id_zk_md5` (`id_zk`,`md5`),
  KEY `id_zk_typ` (`id_zk`,`typ`),
  CONSTRAINT `zaznam_ibfk_1` FOREIGN KEY (`id_zk`) REFERENCES `zk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `zk`;
CREATE TABLE `zk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_uzivatel` int(11) NOT NULL,
  `id_skola` int(11) NOT NULL,
  `jmeno` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `heslo` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `posledni_kontrola` datetime DEFAULT NULL,
  `posledni_uspesny_login` datetime DEFAULT NULL,
  `zaplacena_do` datetime DEFAULT NULL,
  `do_zmeny_odpojena` tinyint(4) NOT NULL DEFAULT '0',
  `smazana` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_skola_jmeno` (`id_skola`,`jmeno`),
  KEY `posledni_kontrola` (`posledni_kontrola`),
  KEY `id_uzivatel` (`id_uzivatel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `zk_historie`;
CREATE TABLE `zk_historie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_zakovska` int(11) NOT NULL,
  `kdy` datetime NOT NULL,
  `znamky` text COLLATE utf8_czech_ci NOT NULL,
  `nastenka` text COLLATE utf8_czech_ci NOT NULL,
  `log` text COLLATE utf8_czech_ci NOT NULL,
  `verze_zakovske` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_zakovska` (`id_zakovska`),
  CONSTRAINT `zk_historie_ibfk_1` FOREIGN KEY (`id_zakovska`) REFERENCES `zk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


-- 2018-10-08 09:41:33
