<?php
/**
 * Init aplikace
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2010
 */

namespace Icinga\Editor;

require_once 'includes/Configure.php';
require_once '../vendor/autoload.php';

//Initialise Gettext
$langs = [
    'en_US' => ['en', 'English (International)'],
    'cs_CZ' => ['cs', 'Česky (Čeština)'],
];
$locale = 'en_US';
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $locale = \locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
}
if (isset($_GET['locale'])) {
    $locale = preg_replace('/[^a-zA-Z_]/', '', substr($_GET['locale'], 0, 10));
}
foreach ($langs as $code => $lang) {
    if ($locale == $lang[0]) {
        $locale = $code;
    }
}
setlocale(LC_ALL, $locale);
bind_textdomain_codeset('skelicz', 'UTF-8');
putenv("LC_ALL=$locale");
if (file_exists('../locale')) {
    bindtextdomain('messages', '../locale');
}
textdomain('messages');

session_start();

/*
 * Objekt uživatele VSUser nebo VSAnonym
 * @global EaseUser
 */
$oUser = \Ease\Shared::user();
$oUser->settingsColumn = 'settings';

if (!\Ease\Shared::isCli()) {
    /* @var $oPage \Sys\WebPage */
    $oPage = new UI\WebPage();
}
