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

$saverClass = str_replace('-', '\\', $oPage->GetRequestValue('SaverClass'));
if ($saverClass == 'undefined') {
    exit;
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
            header('HTTP/1.1 400 Bad Request', 400);
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
    default :
        if (is_null($saverClass) || is_null($field) || is_null($value) || is_null($key)) {
            header('HTTP/1.1 400 Bad Request', 400);
            die(_('Chybné volání'));
        }
        if (strtolower($value) == 'null') {
            $value = null;
        }

        $saver->takeData([$field => $value]);
        break;
}

if (is_null($saver->saveToSQL())) {
    header('HTTP/1.1 501 Not Implemented', 501);
    $oUser->addStatusMessage(_('Error saving to database').': '.$saver->dblink->ErrorText.': '.
        _('Class').': <strong>'.$saverClass.'</strong> '.
        _('Table').': <strong>'.$saver->myTable.'</strong> '.
        _('Field').': <strong>'.$field.'</strong> '.
        _('Value').': <strong>'.$value.'</strong> <tt>'.$saver->dblink->LastQuery.'</tt>',
        'error');
} else {
    header("HTTP/1.1 200 OK");
}



