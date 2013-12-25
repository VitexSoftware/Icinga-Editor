<?php

/**
 * Icinga Editor hosta
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEHost.php';
require_once 'classes/IECfgEditor.php';
require_once 'classes/IEServiceSelector.php';

$oPage->onlyForLogged();

$Host = new IEHost($oPage->getRequestValue('host_id', 'int'));


switch ($oPage->getRequestValue('action')) {
    case 'populate':
        $Host->autoPopulateServices();
        break;

    case 'rename':
        $newname = $oPage->getRequestValue('newname');
        if(strlen($newname)){
            if($Host->rename($newname)){
                $oUser->addStatusMessage(_('Host byl přejmenován'), 'warning');
            } else {
                $oUser->addStatusMessage(_('Host nebyl přejmenován'), 'success');
            }
        }
        break;
    default:
        if ($oPage->isPosted()) {
            $Host->takeData($_POST);
            $HostID = $Host->saveToMySQL();
            if (is_null($HostID)) {
                $oUser->addStatusMessage(_('Host nebyl uložen'), 'warning');
            } else {
                $oUser->addStatusMessage(_('Host byl uložen'), 'success');
            }
        } else {
            $use = $oPage->getGetValue('use');
            if ($use) {
                if ($Host->loadTemplate($use)) {
                    $Host->setDataValue('use', $use);
                    $Host->setDataValue('register', 1);
                }
            }

            $delete = $oPage->getGetValue('delete', 'bool');
            if ($delete == 'true') {
                $Host->delete();
            }

            IEServiceSelector::saveMembers($_REQUEST);
            $Host->saveMembers();
        }
        break;
}

$oPage->addItem(new IEPageTop(_('Editace hosta') . ' ' . $Host->getName()));


$HostEdit = new IECfgEditor($Host);

$form = $oPage->column2->addItem(new EaseHtmlForm('Host', 'host.php', 'POST', $HostEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
$form->addItem(new EaseHtmlInputHiddenTag($Host->getMyKeyColumn(), $Host->getMyKey()));
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));
$oPage->AddCss('
input.ui-button { width: 100%; }
');

$oPage->column3->addItem(new IEServiceSelector($Host));

$oPage->column3->addItem($Host->deleteButton());

$oPage->column3->addItem(new EaseTWBLinkButton('?action=populate&host_id=' . $Host->getID(), _('Oskenovat a sledovat služby')));

$RenameForm = new EaseTWBForm('Rename','?action=rename&host_id=' . $Host->getID());
$RenameForm->addItem( new EaseHtmlInputTextTag('newname'), $Host->getName(), array('class'=>'form-control') );
$RenameForm->addItem( new EaseTWSubmitButton(_('Přejmenovat'), 'success') );

$oPage->column1->addItem( new EaseHtmlFieldSet(_('Přejmenování'), $RenameForm ));


if ($Host->getId()) {
    $oPage->column1->addItem($Host->ownerLinkButton());
}

//$OPage->column3->addItem(new EaseHtmlH4Tag('Rozšířené info'));

$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
