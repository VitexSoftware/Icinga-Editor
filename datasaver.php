<?php

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
if (!$saverClass) {
    $saverClass = 'LBSaver';
}

if (file_exists('classes/' . $saverClass . '.php')) {
    require_once 'classes/' . $saverClass . '.php';
} else {
    $oUser->addStatusMessage(_('Načítání souboru: classes/' . $saverClass . '.php'), 'warning');
}

$field = $oPage->getRequestValue('Field');
$value = $oPage->getRequestValue('Value');
$key = $oPage->getRequestValue('Key', 'int');

if (is_null($saverClass) || is_null($field) || is_null($value) || is_null($key)) {
    header('HTTP/1.0 400 Bad Request', 400);
    die(_('Chybné volání'));
}

$saver = new $saverClass();
//$saver->setUpUser($oUser);
$saver->setMyKey($key);
$saver->takeData(array($field => $value));


if (is_null($saver->saveToMySql())) {
    header('HTTP/1.0 501 Not Implemented', 501);
    $oUser->addStatusMessage(_('Chyba ukládání do databáze: ') . ' ' . $saver->myDbLink->ErrorText . ': ' .
        _('Třída') . ': <strong>' . $saverClass . '</strong> ' .
        _('Tabulka') . ': <strong>' . $saver->myTable . '</strong> ' .
        _('Pole') . ': <strong>' . $field . '</strong> ' .
        _('Hodnota') . ': <strong>' . $value . '</strong> <tt>' . $saver->myDbLink->LastQuery . '</tt>', 'error');
}
