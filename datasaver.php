<?php

/**
 * Uloží data 
 * @package IcingaEditor
 */
require_once 'includes/IEInit.php';

$OPage->onlyForLogged();

if (!$OUser->GetUserID()) {
    die(_('nejprve se prosím přihlaš'));
}

$SaverClass = $OPage->GetRequestValue('SaverClass');
if(!$SaverClass){
    $SaverClass = 'LBSaver';
}


if(file_exists('classes/'.$SaverClass.'.php')){
    require_once 'classes/'.$SaverClass.'.php';
} else {
    $OUser->addStatusMessage(_('Načítání souboru: classes/'.$SaverClass.'.php'),'warning');
}

$Field = $OPage->getRequestValue('Field');
$Value = $OPage->getRequestValue('Value');

if (is_null($SaverClass) || is_null($Field) || is_null($Value)) {
    die(_('Chybné volání'));
}

$Saver = new $SaverClass($Field);
$Saver->setUpUser($OUser);
$Saver->setDataValue($Field, $Value);

if(is_null($Saver->SaveToMySql())){
    header('HTTP/1.0 501 Not Implemented',501);
    $OUser->addStatusMessage(_('Chyba ukládání do databáze: '). ' ' . $Saver->MyDbLink->ErrorText . ': ' . 
            _('Třída').': <strong>'.$SaverClass.'</strong> '. 
            _('Tabulka').': <strong>'.$Saver->MyTable.'</strong> '. 
            _('Pole').': <strong>'.$Field.'</strong> '.
            _('Hodnota').': <strong>'.$Value.'</strong> <tt>'.$Saver->MyDbLink->LastQuery.'</tt>','error');
}
?>
