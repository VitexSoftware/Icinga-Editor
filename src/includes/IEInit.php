<?php

/**
 * Init aplikace
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2010
 */
if (file_exists('includes/Configure.php')) {
    require_once 'includes/Configure.php';
}

spl_autoload_register(
    function($class) {
    $filepath = "classes/{$class}.php";
    is_file($filepath) && include $filepath;
}, false, false
);


$language = "cs_CZ";
$codeset = "cs_CZ.UTF-8";
$domain = "messages";
putenv("LANGUAGE=" . $language);
putenv("LANG=" . $language);
bind_textdomain_codeset($domain, "UTF8");
setlocale(LC_ALL, $codeset);
bindtextdomain($domain, realpath("./locale"));
textdomain($domain);

session_start();

require_once 'Ease/EaseShared.php';
if (!isset($_SESSION['User']) || !is_object($_SESSION['User'])) {
    require_once 'Ease/EaseAnonym.php';
    EaseShared::user(new EaseAnonym());
}

/**
 * Objekt uživatele VSUser nebo VSAnonym
 * @global EaseUser|IEUser
 */
$oUser = & EaseShared::user();
$oUser->SettingsColumn = 'settings';


/* @var $oPage IEWebPage */
$oPage = new IEWebPage();
