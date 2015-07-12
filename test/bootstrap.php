<?php

/**
 * Icinga-editor - nastavení testů.
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2015 VitexSoftware.
 */
ini_set(
    'include_path', ini_get('include_path') . PATH_SEPARATOR .
    dirname(__FILE__) . '/../src/'
);

function __autoload($class_name)
{
    $class_file = dirname(__FILE__) . '/../src/classes/' . $class_name . '.php';
    if (file_exists($class_file)) {
        include $class_file;

        return true;
    }

    return false;
}

include_once 'Token.php';
include_once 'Token/Stream.php';
require_once 'Ease/EaseShared.php';
require_once '../src/includes/IEInit.php';
