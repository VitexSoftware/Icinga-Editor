<?php
/**
 * Init aplikace
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2010-2016
 */

namespace Icinga\Editor;

require_once 'Configure.php';
require_once '../vendor/autoload.php';

//Initialise Gettext
$langs  = [
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
bind_textdomain_codeset('icinga-editor', 'UTF-8');
putenv("LC_ALL=$locale");
if (file_exists('../locale')) {
    bindtextdomain('icinga-editor', '../locale');
}
textdomain('icinga-editor');

session_start();

try {
    /**
     * Objekt uživatele User nebo Anonym
     * @global \Ease\User
     */
    $oUser                 = \Ease\Shared::user();
    $oUser->settingsColumn = 'settings';
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

if (!\Ease\Shared::isCli()) {
    /* @var $oPage \Sys\WebPage */
    $oPage = new UI\WebPage();
}
