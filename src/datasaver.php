<?php

namespace Icinga\Editor;

/**
 * Uloží data
 *
 * @package IcingaEditor
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

if (!$oUser->GetUserID()) {
    die(_('nejprve se prosím přihlaš'));
}

$saverClass = $oPage->GetRequestValue('SaverClass');
if ($saverClass == 'undefined') {
    exit;
}
if (!$saverClass) {
    $saverClass = 'LBSaver';
}

if (file_exists('classes/'.$saverClass.'.php')) {
    require_once 'classes/'.$saverClass.'.php';
} else {
    $oUser->addStatusMessage(_('Načítání souboru: classes/'.$saverClass.'.php'),
        'warning');
}

$field = $oPage->getRequestValue('Field');
$value = $oPage->getRequestValue('Value');
$key   = $oPage->getRequestValue('Key', 'int');

/**
 * @var IEcfg Třída pro ukládající data
 */
$saver = new $saverClass();
$saver->setMyKey($key);


switch ($saver->getColumnType($field)) {
    case 'IDLIST':
        $valueId = $oPage->getRequestValue('ValueID');
        if (is_null($saverClass) || is_null($field) || is_null($value) || is_null($key)
            || is_null($value) || is_null($valueId)) {
            header('HTTP/1.0 400 Bad Request', 400);
            die(_('Chybné volání'));
        }

        $saver->loadFromSQL();

        switch ($oPage->getRequestValue('operation')) {
            case 'add':
                $saver->addMember($field, $valueId, $value);
                break;
            case 'del':
                $saver->delMember($field, $valueId, $value);
                break;
        }

        break;
    default:
        if (is_null($saverClass) || is_null($field) || is_null($value) || is_null($key)) {
            header('HTTP/1.0 400 Bad Request', 400);
            die(_('Chybné volání'));
        }
        $saver->takeData([$field => $value]);
        break;
}

if (is_null($saver->saveToSQL())) {
    header('HTTP/1.0 501 Not Implemented', 501);
    $oUser->addStatusMessage(_('Chyba ukládání do databáze: ').' '.$saver->dblink->ErrorText.': '.
        _('Třída').': <strong>'.$saverClass.'</strong> '.
        _('Tabulka').': <strong>'.$saver->myTable.'</strong> '.
        _('Pole').': <strong>'.$field.'</strong> '.
        _('Hodnota').': <strong>'.$value.'</strong> <tt>'.$saver->dblink->LastQuery.'</tt>',
        'error');
}



