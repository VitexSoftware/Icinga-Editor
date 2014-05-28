<?php

/**
 * VitexSoftware
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2010 Vitex@vitexsoftware.cz (G)
 */
date_default_timezone_set('Europe/Prague');

/**
 * Adresa odesilatele
 */
define('SEND_MAILS_FROM', 'noreply@vitexsoftware.cz');
/**
 * Databázový server
 */
define('DB_SERVER', 'localhost');
/**
 * Uživatelské jméno k databázi
 */
define('DB_SERVER_USERNAME', 'iciedit');
/**
 * Heslo k databázi
 */
define('DB_SERVER_PASSWORD', 'LojAstod9');
/**
 * Název databáze
 */
define('DB_DATABASE', 'iciedit');

/**
 * Prefix tabulek
 */
define('DB_PREFIX', 'iciedit_');

/**
 * Adresář pro zápis logů
 */
define('LOG_DIRECTORY', '/var/tmp/');

/**
 * Konfigurační adresář  Icingy
 */
define('CFG_GENERATED', '/etc/icinga/generated/');

/**
 * Výchozí odesilatel zpráv
 */
define('EMAIL_FROM','monitor@v.s.cz');
/**
 * Kam posílat oznámení o nových registracích
 */
define('SEND_INFO_TO','info@vitexsoftware.cz');
/**
 * Veřejná IP nagiosu
 */
define('ICINGA_SERVER_IP','77.87.241.140');
