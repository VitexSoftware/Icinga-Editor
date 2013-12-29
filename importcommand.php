<?php

/**
 * Import ze souboru
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'IECommand.php';

if($oPage->isPosted()){
    $Importer = new IECommand();
} else {
    $oPage->addStatusMessage(_('Zadejte konfigurační fragment příkazu, nebo zvolte soubor k importu'));
}

$CfgText = $oPage->getRequestValue('cfgtext');
if($CfgText){
    $Importer->importText($CfgText, array('command_type'=> $oPage->getRequestValue('type')));
}

if(isset($_FILES['cfgfile']['tmp_name']) && strlen(trim($_FILES['cfgfile']['tmp_name']))){
    $Importer->importFile($_FILES['cfgfile']['tmp_name'],array('command_type'=> $oPage->getRequestValue('type')));
} 

$oPage->addItem(new IEPageTop(_('Načtení příkazů ze souboru')));

$FileForm =  new EaseHtmlForm('CfgFileUp',null,'POST',null,array('class' => 'form-horizontal','enctype'=>'multipart/form-data'));
$FileForm->addItem(new EaseLabeledTextarea('cfgtext','',_('konfigurační fragment')));
$FileForm->addItem(new EaseLabeledFileInput('cfgfile',null,_('konfigurační soubor')));

$TypeSelector = new EaseLabeledSelect('type', 'check', _('druh vkládaných příkazů'));
$TypeSelector->addItems(array('check'=>'check','notify'=>'notify','handler'=>'handler'));

$FileForm->addItem( $TypeSelector );

$FileForm->addItem(new EaseJQuerySubmitButton('Submit', _('importovat'), _('zahájí import příkazů')));

$oPage->columnII->addItem( new EaseHtmlFieldSet(_('Import konfigurace'), $FileForm) );

$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
