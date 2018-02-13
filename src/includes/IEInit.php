<?php
/**
 * Init aplikace
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2010-2018
 */

namespace Icinga\Editor;

require_once '../vendor/autoload.php';

\Ease\Shared::instanced()->loadConfig('../config.json');
\Ease\Shared::initializeGetText('icinga-editor', 'UTF-8', '../locale');

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
