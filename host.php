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

$OPage->onlyForLogged();

$Host = new IEHost($OPage->getRequestValue('host_id', 'int'));


switch ($OPage->getRequestValue('action')) {
    case 'populate':
        $Host->autoPopulateServices();
        break;

    case 'rename':
        $newname = $OPage->getRequestValue('newname');
        if(strlen($newname)){
            if($Host->rename($newname)){
                $OUser->addStatusMessage(_('Host byl přejmenován'), 'warning');
            } else {
                $OUser->addStatusMessage(_('Host nebyl přejmenován'), 'success');
            }
        }
        break;
    default:
        if ($OPage->isPosted()) {
            $Host->takeData($_POST);
            $HostID = $Host->saveToMySQL();
            if (is_null($HostID)) {
                $OUser->addStatusMessage(_('Host nebyl uložen'), 'warning');
            } else {
                $OUser->addStatusMessage(_('Host byl uložen'), 'success');
            }
        } else {
            $Use = $OPage->getGetValue('use');
            if ($Use) {
                if ($Host->loadTemplate($Use)) {
                    $Host->setDataValue('use', $Use);
                    $Host->setDataValue('register', 1);
                }
            }

            $Delete = $OPage->getGetValue('delete', 'bool');
            if ($Delete == 'true') {
                $Host->delete();
            }

            IEServiceSelector::saveMembers($_REQUEST);
            $Host->saveMembers();
        }
        break;
}

$OPage->addItem(new IEPageTop(_('Editace hosta') . ' ' . $Host->getName()));


$HostEdit = new IECfgEditor($Host);

$Form = $OPage->column2->addItem(new EaseHtmlForm('Host', 'host.php', 'POST', $HostEdit, array('class' => 'form-horizontal')));
$Form->setTagID($Form->getTagName());
$Form->addItem(new EaseHtmlInputHiddenTag($Host->getMyKeyColumn(), $Host->getMyKey()));
$Form->addItem('<br>');
$Form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));
$OPage->AddCss('
input.ui-button { width: 100%; }
');

$OPage->column3->addItem(new IEServiceSelector($Host));

$OPage->column3->addItem($Host->deleteButton());

$OPage->column3->addItem(new EaseTWBLinkButton('?action=populate&host_id=' . $Host->getID(), _('Oskenovat a sledovat služby')));

$RenameForm = new EaseTWBForm('Rename','?action=rename&host_id=' . $Host->getID());
$RenameForm->addItem( new EaseHtmlInputTextTag('newname'), $Host->getName(), array('class'=>'form-control') );
$RenameForm->addItem( new EaseTWSubmitButton(_('Přejmenovat'), 'success') );

$OPage->column1->addItem( new EaseHtmlFieldSet(_('Přejmenování'), $RenameForm ));


if ($Host->getId()) {
    $OPage->column1->addItem($Host->ownerLinkButton());
}

//$OPage->column3->addItem(new EaseHtmlH4Tag('Rozšířené info'));

$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
