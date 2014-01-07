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
require_once 'classes/IEIconSelector.php';
require_once 'classes/IEHostOverview.php';

$oPage->onlyForLogged();

$host = new IEHost($oPage->getRequestValue('host_id', 'int'));

switch ($oPage->getRequestValue('action')) {
    case 'populate':
        $host->autoPopulateServices();
        break;
    case 'icon':

        $icourl = $oPage->getRequestValue('icourl');
        if (strlen($icourl)) {
            $tmpfilename = sys_get_temp_dir() . '/' . EaseSand::randomString();
            $fp = fopen($tmpfilename, 'w');
            $ch = curl_init($icourl);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            $data = curl_exec($ch);

            $downloadErr = curl_error($ch);
            if ($downloadErr) {
                $oPage->addStatusMessage($downloadErr, 'warning');
            }
            curl_close($ch);
            fclose($fp);

            if (!file_exists($tmpfilename)) {
                $oPage->addStatusMessage(sprintf(_('Soubor %s se nepodařilo stahnout'), $icourlurl));
            }
        } else {
            if (isset($_FILES) && count($_FILES)) {
                $tmpfilename = $_FILES['icofile']['tmp_name'];
            } else {
                if($oPage->isPosted()){
                    $oPage->addStatusMessage(_('Nebyl vybrán soubor s ikonou hosta'), 'warning');
                }
            }
        }
        if (isset($tmpfilename)) {
            if (IEIconSelector::imageTypeOK($tmpfilename)) {
                $newicon = IEIconSelector::saveIcon($tmpfilename, $host);
            } else {
                unlink($tmpfilename);
                $oPage->addStatusMessage(_('toto není obrázek požadovaného typu'), 'warning');
            }
        }
    case 'newicon':
        if (!isset($newicon)) {
            $newicon = $oPage->getRequestValue('newicon');
        }
        if (strlen($newicon)) {
            $host->setDataValue('icon_image', $newicon);
            $host->setDataValue('statusmap_image', $newicon);
            $host->setDataValue('icon_image_alt', $oPage->getRequestValue('icon_image_alt'));
            if ($host->saveToMySQL()) {
                $oUser->addStatusMessage(_('Ikona byla přiřazena'), 'success');
            } else {
                $oUser->addStatusMessage(_('Ikona nebyla přiřazena'), 'warning');
            }
        }
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
            $newParent = EaseShared::myDbLink()->queryToValue('SELECT `' . $host->nameColumn . '` FROM ' . $host->myTable . ' '
                    . 'WHERE `' . $host->nameColumn . '` = \'' . addSlashes($np) . '\' '
                    . 'OR `alias` = \'' . addSlashes($np) . '\' '
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
    case 'icon':
        $oPage->columnII->addItem(new IEIconSelector($host));
        break;
}

$oPage->columnII->addItem(new IEHostOverview($host));


$hostEdit = new IECfgEditor($host);
$form = $oPage->columnII->addItem(new EaseHtmlForm('Host', 'host.php', 'POST', $hostEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
$form->addItem(new EaseHtmlInputHiddenTag($host->getmyKeyColumn(), $host->getMyKey()));
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));
$oPage->AddCss('
input.ui-button { width: 100%; }
');


$oPage->columnIII->addItem($host->deleteButton());

$oPage->columnIII->addItem(new EaseTWBLinkButton('?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby')));

$renameForm = new EaseTWBForm('Rename', '?action=rename&amp;host_id=' . $host->getID());
$renameForm->addItem(new EaseHtmlInputTextTag('newname'), $host->getName(), array('class' => 'form-control'));
$renameForm->addItem(new EaseTWSubmitButton(_('Přejmenovat'), 'success'));

$oPage->columnIII->addItem(new EaseHtmlFieldSet(_('Přejmenování'), $renameForm));
$oPage->columnI->addItem(new EaseTWBLinkButton('?action=parent&host_id=' . $host->getId(), _('Přiřadit rodiče'), 'success'));
$oPage->columnI->addItem(new EaseTWBLinkButton('?action=icon&host_id=' . $host->getId(), _('Změnit ikonu'), 'success'));

$oPage->columnI->addItem(new IEServiceSelector($host));

if ($host->getId()) {
    $oPage->columnI->addItem($host->ownerLinkButton());
}

//$OPage->column3->addItem(new EaseHtmlH4Tag('Rozšířené info'));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
