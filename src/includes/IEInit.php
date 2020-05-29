<?php

/**
 * Init aplikace
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2010-2018
 */

namespace Icinga\Editor;

require_once '../vendor/autoload.php';

\Ease\Shared::singleton()->loadConfig('../config.json', true);
\Ease\Locale::singleton('UTF-8', '../locale', 'icinga-editor');

session_start();


$oUser = \Ease\Shared::user(null, 'Icinga\Editor\User');

$oPage = new UI\WebPage();
