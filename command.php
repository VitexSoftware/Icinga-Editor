<?php

/**
 * Icinga Editor - titulní strana
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IECommand.php';
require_once 'classes/IECfgEditor.php';

$OPage->onlyForLogged();

$Command = new IECommand($OPage->getRequestValue('command_id', 'int'));

if ($OPage->isPosted()) {
    $Command->takeData($_POST);
    $CommandID = $Command->saveToMySQL();
    if (is_null($CommandID)) {
        $OUser->addStatusMessage(_('Příkaz nebyl uložen'), 'warning');
    } else {
        $OUser->addStatusMessage(_('Příkaz byl uložen'), 'success');
    }
}

$Delete = $OPage->getGetValue('delete', 'bool');
if ($Delete == 'true') {
    $Command->delete();
}


$OPage->addItem(new IEPageTop(_('Editace příkazu') . ' ' . $Command->getName()));

$CommandEdit = new IECfgEditor($Command);

$Form = $OPage->column2->addItem(new EaseHtmlForm('Command', 'command.php', 'POST', $CommandEdit, array('class' => 'form-horizontal')));
$Form->setTagID($Form->getTagName());
if (!is_null($Command->getMyKey())) {
    $Form->addItem(new EaseHtmlInputHiddenTag($Command->getMyKeyColumn(), $Command->getMyKey()));
}
$Form->addItem('<br>');
$Form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));
$OPage->AddCss('
input.ui-button { width: 100%; }
');


$OPage->column3->addItem($Command->deleteButton());

if ($Command->getId()) {
    $OPage->column1->addItem($Command->ownerLinkButton());
}

$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
