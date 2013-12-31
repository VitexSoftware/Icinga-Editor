<?php

/**
 * Uloží data 
 * @package IcingaEditor
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

if (!$oUser->GetUserID()) {
    die(_('nejprve se prosím přihlaš'));
}

$SaverClass = $oPage->GetRequestValue('SaverClass');
if(!$SaverClass){
    $SaverClass = 'LBSaver';
}


if(file_exists('classes/'.$SaverClass.'.php')){
    require_once 'classes/'.$SaverClass.'.php';
} else {
    $oUser->addStatusMessage(_('Načítání souboru: classes/'.$SaverClass.'.php'),'warning');
}

$Field = $oPage->getRequestValue('Field');
$value = $oPage->getRequestValue('Value');

if (is_null($SaverClass) || is_null($Field) || is_null($value)) {
    die(_('Chybné volání'));
}

$Saver = new $SaverClass($Field);
$Saver->setUpUser($oUser);
$Saver->setDataValue($Field, $value);

if(is_null($Saver->SaveToMySql())){
    header('HTTP/1.0 501 Not Implemented',501);
    $oUser->addStatusMessage(_('Chyba ukládání do databáze: '). ' ' . $Saver->myDbLink->ErrorText . ': ' . 
            _('Třída').': <strong>'.$SaverClass.'</strong> '. 
            _('Tabulka').': <strong>'.$Saver->myTable.'</strong> '. 
            _('Pole').': <strong>'.$Field.'</strong> '.
            _('Hodnota').': <strong>'.$value.'</strong> <tt>'.$Saver->myDbLink->LastQuery.'</tt>','error');
}
?>
