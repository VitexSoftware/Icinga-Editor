<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - Recerate database
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2017 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Icinga Editor - recereate database structure')));
$oPage->addPageColumns();


if ($oPage->getRequestValue('dbinit')) {
    $importer = new Engine\Importer;
    $importer->dbInit();
    $oPage->columnII->addItem(new \Ease\TWB\LinkButton('wizard.php',
            _('Create configuration')));
    $oPage->columnIII->addItem(new \Ease\TWB\LinkButton('import.php',
            _('Import configuration')));
} else {
    $importForm = new \Ease\Html\Form('ImportForm');
    $oUser->addStatusMessage(_('This action irreversibly deletes any configuration. Do you really want to do this?'));
    $importForm->addItem(new \Ease\TWB\FormGroup(_('I know what i do'),
            new UI\YesNoSwitch('dbinit')));
    $importForm->addItem(new \Ease\TWB\SubmitButton(_('Go!')));

    $oPage->columnII->addItem(new \Ease\Html\FieldSet(_('Create again database structure'),
            $importForm));
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
