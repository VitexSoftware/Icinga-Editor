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

$host = new IEHost($oPage->getRequestValue('host_id', 'int'));

switch ($oPage->getRequestValue('action')) {
    case 'populate':
        $host->autoPopulateServices();
        break;

    case 'rename':
        $newname = $oPage->getRequestValue('newname');
        if (strlen($newname)) {
            if ($host->rename($newname)) {
                $oUser->addStatusMessage(_('Host byl přejmenován'), 'warning');
            } else {
                $oUser->addStatusMessage(_('Host nebyl přejmenován'), 'success');
            }
        }
        break;
    case 'parent':
        $np = $oPage->getRequestValue('newparent');
        if ($np) {
            $newParent = EaseShared::myDbLink()->queryToValue('SELECT `alias` FROM ' . $host->myTable . ' '
                    . 'WHERE `' . $host->NameColumn . '` = \'' . addSlashes($np) . '\' '
                    . 'OR `address` = \'' . addSlashes($np) . '\' '
                    . 'OR `address6` = \'' . addSlashes($np) . '\' ');
            if (!$newParent) {
                $oUser->addStatusMessage(_('Rodič nebyl nalezen'), 'warning');
            } else {
                $currentParents = $host->getDataValue('parents');
                $currentParents[] = $newParent;
                $host->setDataValue('parents', $currentParents);
                $hostID = $host->saveToMySQL();
                if (is_null($hostID)) {
                    $oUser->addStatusMessage(_('Rodič nebyl přidán'), 'warning');
                } else {
                    $oUser->addStatusMessage(_('Rodič byl přidán'), 'success');
                }
            }
        }
        break;
    default:
        if ($oPage->isPosted()) {
            $host->takeData($_POST);
            $hostID = $host->saveToMySQL();
            if (is_null($hostID)) {
                $oUser->addStatusMessage(_('Host nebyl uložen'), 'warning');
            } else {
                $oUser->addStatusMessage(_('Host byl uložen'), 'success');
            }
        } else {
            $use = $oPage->getGetValue('use');
            if ($use) {
                if ($host->loadTemplate($use)) {
                    $host->setDataValue('use', $use);
                    $host->setDataValue('register', 1);
                }
            }

            $delete = $oPage->getGetValue('delete', 'bool');
            if ($delete == 'true') {
                $host->delete();
            }

            IEServiceSelector::saveMembers($_REQUEST);
            $host->saveMembers();
        }
        break;
}

$oPage->addItem(new IEPageTop(_('Editace hosta') . ' ' . $host->getName()));

switch ($oPage->getRequestValue('action')) {
    case 'parent':
        require_once 'classes/IEParentSelector.php';
        $oPage->columnII->addItem(new IEParentSelector($host));
        break;
}

$hostEdit = new IECfgEditor($host);

$form = $oPage->columnII->addItem(new EaseHtmlForm('Host', 'host.php', 'POST', $hostEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
$form->addItem(new EaseHtmlInputHiddenTag($host->getMyKeyColumn(), $host->getMyKey()));
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));
$oPage->AddCss('
input.ui-button { width: 100%; }
');

$oPage->columnIII->addItem(new IEServiceSelector($host));

$oPage->columnIII->addItem($host->deleteButton());

$oPage->columnIII->addItem(new EaseTWBLinkButton('?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby')));

$renameForm = new EaseTWBForm('Rename', '?action=rename&host_id=' . $host->getID());
$renameForm->addItem(new EaseHtmlInputTextTag('newname'), $host->getName(), array('class' => 'form-control'));
$renameForm->addItem(new EaseTWSubmitButton(_('Přejmenovat'), 'success'));

$oPage->columnI->addItem(new EaseHtmlFieldSet(_('Přejmenování'), $renameForm));
$oPage->columnIII->addItem(new EaseTWBLinkButton('?action=parent&host_id=' . $host->getId(), _('Přiřadit rodiče'), 'success'));

if ($host->getId()) {
    $oPage->columnI->addItem($host->ownerLinkButton());
}

//$OPage->column3->addItem(new EaseHtmlH4Tag('Rozšířené info'));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
