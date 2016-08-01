<?php
/**
 * VitexSoftware
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2010 Vitex@vitexsoftware.cz (G)
 */
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
define('DB_SERVER_USERNAME', 'icinga_editor');
/**
 * Heslo k databázi
 */
define('DB_SERVER_PASSWORD', 'LojAstod9');
/**
 * Název databáze
 */
define('DB_DATABASE', 'icinga_editor');

/*
 * Database Port
 */
define('DB_PORT', 3306);

/*
 * Druh Databaze
 */
define('DB_TYPE', 'mysql');


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
define('EMAIL_FROM', 'monitor@v.s.cz');
/**
 * Kam posílat oznámení o nových registracích
 */
define('SEND_INFO_TO', 'info@vitexsoftware.cz');
