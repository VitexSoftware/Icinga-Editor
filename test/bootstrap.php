<?php

/**
 * Icinga-editor - nastavení testů.
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2015 VitexSoftware.
 */
include_once 'Token.php';
include_once 'Token/Stream.php';



spl_autoload_register(
    function($class) {
    $filepath = "../src/classes/{$class}.php";
    is_file($filepath) && include $filepath;
}, false, false
);
spl_autoload_register(
    function($class) {
    $filepath = "src/classes/{$class}.php";
    is_file($filepath) && include $filepath;
}, false, false
);

echo ini_get('include_path');

require_once '../src/includes/IEInit.php';
