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

$Field = $oPage->getRequestValue('Field');
$value = $oPage->getRequestValue('Value');

if (is_null($saverClass) || is_null($Field) || is_null($value)) {
    die(_('Chybné volání'));
}

$Saver = new $saverClass($Field);
$Saver->setUpUser($oUser);
$Saver->setDataValue($Field, $value);

if (is_null($Saver->SaveToMySql())) {
    header('HTTP/1.0 501 Not Implemented', 501);
    $oUser->addStatusMessage(_('Chyba ukládání do databáze: ') . ' ' . $Saver->myDbLink->ErrorText . ': ' .
        _('Třída') . ': <strong>' . $saverClass . '</strong> ' .
        _('Tabulka') . ': <strong>' . $Saver->myTable . '</strong> ' .
        _('Pole') . ': <strong>' . $Field . '</strong> ' .
        _('Hodnota') . ': <strong>' . $value . '</strong> <tt>' . $Saver->myDbLink->LastQuery . '</tt>', 'error');
}
