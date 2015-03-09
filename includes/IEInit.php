<?php

/**
 * Init aplikace
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2010
 */
require_once 'includes/Configure.php';

$language = "cs_CZ";
$codeset = "cs_CZ.UTF-8";
$domain = "messages";
putenv("LANGUAGE=" . $language);
putenv("LANG=" . $language);
bind_textdomain_codeset($domain, "UTF8");
setlocale(LC_ALL, $codeset);
bindtextdomain($domain, realpath("./locale"));
textdomain($domain);

require_once 'classes/IEUser.php';
require_once 'classes/IEPreferences.php';

session_start();

if (!isset($_SESSION['User']) || !is_object($_SESSION['User'])) {
    EaseShared::user(new EaseAnonym());
}

/**
 * Objekt uživatele VSUser nebo VSAnonym
 * @global EaseUser
 */
$oUser = & EaseShared::user();
$oUser->SettingsColumn = 'settings';

require_once 'classes/IEWebPage.php';

/* @var $oPage IEWebPage */
$oPage = new IEWebPage();
