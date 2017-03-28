<?php

namespace Icinga\Editor;

/**
 * Icinga Host Editor
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2016 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$hostId = $oPage->getRequestValue('host_id', 'int');

$host = new Engine\Host($hostId);

switch ($oPage->getRequestValue('action')) {
    case 'applystemplate':
        $stemplate = new Stemplate($oPage->getRequestValue('stemplate_id', 'int'));
        $services = $stemplate->getDataValue('services');
        if (count($services)) {
            $service = new Engine\Service();
            foreach ($services as $service_id => $service_name) {
                if ($service->loadFromSQL($service_id)) {
                    $service->addMember('host_name', $host->getId(), $host->getName());
                    $service->saveToSQL();
                    $service->dataReset();
                } else {
                    $service->addStatusMessage(_(sprintf(_('Service used in template (#%s: %s) not found'), $service_id, $service_name)));
                }
            }
        }
        $contacts = $stemplate->getDataValue('contacts');
        if (count($contacts)) {
            foreach ($contacts as $contact_id => $contact_name) {
                $host->addMember('contacts', $contact_id, $contact_name);
            }
            $host->saveToSQL();
        }

        break;
    case 'populate':
        $host->autoPopulateServices();
        break;
    case 'icon':

        $icourl = $oPage->getRequestValue('icourl');
        if (strlen($icourl)) {
            $tmpfilename = sys_get_temp_dir() . '/' . \Ease\Sand::randomString();
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
                $oPage->addStatusMessage(sprintf(_('Cannot download file %s'), $icourlurl));
            }
        } else {
            if (isset($_FILES) && count($_FILES)) {
                $tmpfilename = $_FILES['icofile']['tmp_name'];
            } else {
                if ($oPage->isPosted()) {
                    $oPage->addStatusMessage(_('Icon file not selected'), 'warning');
                }
            }
        }
        if (isset($tmpfilename)) {
            if (UI\IconSelector::imageTypeOK($tmpfilename)) {
                $newicon = UI\IconSelector::saveIcon($tmpfilename, $host);
            } else {
                unlink($tmpfilename);
                $oPage->addStatusMessage(_('this is not requied image file type'), 'warning');
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
            if ($host->saveToSQL()) {
                $oUser->addStatusMessage(_('Icon was assigned'), 'success');
            } else {
                $oUser->addStatusMessage(_('Icon was not assigned'), 'warning');
            }
        }
        break;
    case 'rename':
        $newname = $oPage->getRequestValue('newname');
        if (strlen($newname)) {
            if ($host->rename($newname)) {
                $oUser->addStatusMessage(_('Host was renamed'), 'success');
            } else {
                $oUser->addStatusMessage(_('Host was not renamed'), 'warning');
            }
        }
        break;
    case 'parent':
        $np = $oPage->getRequestValue('newparent');
        if ($np) {
            $newParent = \Ease\Shared::db()->queryToValue('SELECT `' . $host->nameColumn . '` FROM ' . $host->myTable . ' '
                . 'WHERE `' . $host->nameColumn . '` = \'' . addSlashes($np) . '\' '
                . 'OR `alias` = \'' . addSlashes($np) . '\' '
                . 'OR `address` = \'' . addSlashes($np) . '\' '
                . 'OR `address6` = \'' . addSlashes($np) . '\' ');
            if (!$newParent) {
                $oUser->addStatusMessage(_('Parent not found'), 'warning');
                $oPage->redirect('watchroute.php?action=parent&host_id=' . $host->getId() . '&ip=' . $np);
                exit;
            } else {
                $currentParents = $host->getDataValue('parents');
                $currentParents[] = $newParent;
                $host->setDataValue('parents', $currentParents);
                $hostID = $host->saveToSQL();
                if (is_null($hostID)) {
                    $oUser->addStatusMessage(_('Parent was not added'), 'warning');
                } else {
                    $oUser->addStatusMessage(_('Parent was added'), 'success');
                }
            }
        }
        break;
    default :
        if ($oPage->isPosted()) {
            $host->takeData($_POST);
            $hostID = $host->saveToSQL();
            if (is_null($hostID)) {
                $oUser->addStatusMessage(_('Host was not saved'), 'warning');
            } else {
                $oUser->addStatusMessage(_('Host was saved'), 'success');
            }
        } else {
            $use = $oPage->getGetValue('use');
            if ($use) {
                if ($host->loadTemplate($use)) {
                    $host->setDataValue('use', $use);
                    $host->setDataValue('register', 1);
                }
            }

            $delete = $oPage->getGetValue('delete', 'string');
            if ($delete === 'true') {
                $host->delete();
                $oPage->redirect('hosts.php');
                exit();
            }

            UI\UsedServiceSelector::saveMembers($_REQUEST);
            $host->saveMembers();
        }
        break;
}

$delcnt = $oPage->getGetValue('delcontact');
if ($delcnt) {
    $host->delMember(
        'contacts', $oPage->getGetValue('contact_id', 'int'), $delcnt
    );
    $host->saveToSQL();
}

$addcnt = $oPage->getGetValue('addcontact');
if ($addcnt) {
    $host->addMember(
        'contacts', $oPage->getGetValue('contact_id', 'int'), $addcnt
    );
    $host->saveToSQL();
}

$oPage->addItem(new UI\PageTop(_('Host Editor') . ' ' . $host->getName()));

$infopanel = new UI\InfoBox($host);
$tools = new \Ease\TWB\Panel(_('Tools'), 'warning');

$pageRow = new \Ease\TWB\Row;
$pageRow->addColumn(2, $infopanel);
$mainPanel = $pageRow->addColumn(6);
$pageRow->addColumn(4, $tools);
$oPage->container->addItem($pageRow);


$hostPanel = $mainPanel->addItem(new \Ease\TWB\Panel(new \Ease\Html\H1Tag($host->getDataValue('alias') . ' <small>' . $host->getName() . '</small>'), 'info', null, nl2br(trim($host->getDataValue('notes')))));

$hostTabs = $hostPanel->addItem(new \Ease\TWB\Tabs('hostTabs'));
$commonTab = $hostTabs->addTab(_('Common'));
$hostParams = $hostTabs->addTab(_('Configuration'));

switch ($oPage->getRequestValue('action')) {
    case 'parent':
        $commonTab->addItem(new UI\ParentSelector($host));
        break;
    case 'icon':
        $commonTab->addItem(new UI\IconSelector($host));
        break;
    case 'delete':
        $confirmator = $mainPanel->addItem(new \Ease\TWB\Panel(_('Are you sure to delete ?')), 'danger');
        $confirmator->addItem(new UI\RecordShow($host));
        $confirmator->addItem(new \Ease\TWB\LinkButton('?' . $host->myKeyColumn . '=' . $host->getID(), _('Ne') . ' ' . \Ease\TWB\Part::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new \Ease\TWB\LinkButton('?delete=true&' . $host->myKeyColumn . '=' . $host->getID(), _('Ano') . ' ' . \Ease\TWB\Part::glyphIcon('remove'), 'danger'));

        $infopanel->addItem($host->ownerLinkButton());

        break;
    default :

        $hostEdit = new UI\CfgEditor($host);
        $form = $hostParams->addItem(new \Ease\Html\Form('Host', 'host.php', 'POST', $hostEdit, ['class' => 'form-horizontal']));
        $form->setTagID($form->getTagName());
        $form->addItem('<br>');
        $form->addItem(new \Ease\TWB\SubmitButton(_('Uložit'), 'success'));
        $oPage->addCss('
input.ui-button { width: 100%; }
');

        $tools->addItem($host->deleteButton());
        $oPage->addItem(new \Ease\Html\Div(new UI\FXPreloader(), ['class' => 'fuelux', 'id' => 'preload']));

        if ($host->getDataValue('active_checks_enabled') == '1') {
            $tools->addItem(new \Ease\TWB\LinkButton('?action=populate&host_id=' . $host->getID(), _('Scan and watch services'), null, ['onClick' => "$('#preload').css('visibility', 'visible');"]));
        }

        $renameForm = new \Ease\TWB\Form('Rename', '?action=rename&amp;host_id=' . $host->getID());
        $renameForm->addItem(new \Ease\Html\InputTextTag('newname'), $host->getName(), ['class' => 'form-control']);
        $renameForm->addItem(new \Ease\TWB\SubmitButton(_('Rename'), 'success'));

        $tools->addItem(new \Ease\TWB\Panel(_('Renaming'), 'info', $renameForm));

        if (count($host->getDataValue('parents'))) {
            $tools->addItem(new \Ease\TWB\LinkButton('?action=parent&host_id=' . $host->getId(), _('Assign parents'), 'default'));
        } else {
            $tools->addItem(new \Ease\TWB\LinkButton('?action=parent&host_id=' . $host->getId(), _('Assign parents'), 'success'));
        }

        if ($host->getDataValue('icon_image')) {
            $tools->addItem(new \Ease\TWB\LinkButton('?action=icon&host_id=' . $host->getId(), _('Change icon'), 'default'));
        } else {
            $tools->addItem(new \Ease\TWB\LinkButton('?action=icon&host_id=' . $host->getId(), _('Set icon'), 'success'));
        }

        if ($host->getDataValue('address')) {
            $tools->addItem(new \Ease\TWB\LinkButton('watchroute.php?host_id=' . $host->getId(), _('Watch the route'), 'success', ['onClick' => "$('#preload').css('visibility', 'visible');"]));
        }




        if ($host->getDataValue('platform') != 'generic') {
            $status = null;
            $status_code = $host->getSensorStatus();
            switch ($status_code) {
                case 2:
                    $status = _('Sendor OK');
                    $type = 'default';
                    break;
                case 1:
                    $status = _('Actialize Sensor');
                    $type = 'success';
                    break;
                case 0:
                default :
                    $status = _('Deploy sensor');
                    $type = 'warning';
                    break;
            }
            $tools->addItem(new \Ease\TWB\LinkButton('sensor.php?host_id=' . $host->getId(), $status, $type));
        }

        $commonTab->addItem(new UI\UsedServiceSelector($host));
        $commonTab->addItem(new UI\ContactSelector($host));

        break;
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
