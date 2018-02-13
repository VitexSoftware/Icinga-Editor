<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - Service template editor
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2017 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$stemplate = new Stemplate($oPage->getRequestValue('stemplate_id', 'int'));

switch ($oPage->getRequestValue('action')) {
    case 'new':
        $stemplate->setDataValue($stemplate->nameColumn,
            _('New  service template'));
        $stemplate->insertToSQL();
        $stemplate->setDataValue($stemplate->nameColumn,
            _('New template').' #'.$stemplate->getId());
        if ($oPage->isPosted()) {
            $stemplate->updateToSQL();
        }

        break;
    case 'copyhost':
        $host = new Engine\Host($oPage->getRequestValue('host_id', 'int'));

        $stemplate->setDataValue($stemplate->nameColumn, $host->getName());
        $stemplate->setDataValue('services', $host->getServices());
        if ($stemplate->saveToSQL()) {
            $stemplate->addStatusMessage(sprintf(_('New watched services template: %s was created '),
                    $stemplate->getName()), 'success');
        } else {
            $stemplate->addStatusMessage(sprintf(_('New template was not created')),
                'warning');
        }

        break;
    case 'export':
        $stemplate->transfer($oPage->getRequestValue('destination'));
        break;
    default :
        if ($oPage->isPosted()) {
            $stemplate->takeData($_POST);
            if (!$stemplate->getName()) {
                $oUser->addStatusMessage(_('No name was set'), 'warning');
            }
            $stemplateID = $stemplate->saveToSQL();

            if (is_null($stemplateID)) {
                $oUser->addStatusMessage(_('Services template was not saved'),
                    'warning');
            } else {
                $oUser->addStatusMessage(_('Services template was saved'),
                    'success');
            }
        }
}



$delete = $oPage->getGetValue('delete', 'string');
if ($delete == 'true') {
    $stemplate->delete();
    $oPage->redirect('stemplates.php');
}

$oPage->addItem(new UI\PageTop(_('Service template editor').' '.$stemplate->getName()));
$oPage->addPageColumns();

if ($stemplate->getId()) {
    $oPage->columnIII->addItem($stemplate->deleteButton());
}

switch ($oPage->getRequestValue('action')) {
    case 'delete':

        $oPage->columnII->addItem(new \Ease\Html\H2Tag($stemplate->getName()));

        $confirmator = $oPage->columnII->addItem(new \Ease\TWB\Panel(_('Do you really delete?')),
            'danger');
        $confirmator->addItem(new \Ease\TWB\LinkButton('?'.$stemplate->keyColumn.'='.$stemplate->getID(),
                _('No').' '.\Ease\TWB\Part::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new \Ease\TWB\LinkButton('?delete=true&'.$stemplate->keyColumn.'='.$stemplate->getID(),
                _('Yes').' '.\Ease\TWB\Part::glyphIcon('remove'), 'danger'));


        break;
    default :
        $stemplateEditor = new UI\CfgEditor($stemplate);

        $form = $oPage->columnII->addItem(new \Ease\Html\Form('Stemplate',
                'stemplate.php', 'POST', $stemplateEditor,
                ['class' => 'form-horizontal']));

        if (!$stemplate->getId()) {
            $form->addItem(new \Ease\TWB\SubmitButton(_('Create'), 'success'));
        } else {
            $form->addItem(new \Ease\TWB\SubmitButton(_('Save'), 'success'));
        }
        $oPage->columnIII->addItem(new \Ease\TWB\Panel(_('Transfer'), 'warning',
                $stemplate->transferForm()));
        break;
}


$oPage->addItem(new UI\PageBottom());

$oPage->draw();
