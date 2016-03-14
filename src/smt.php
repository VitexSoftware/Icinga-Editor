<?php
namespace Icinga\Editor;

/**
 * Icinga Editor služby
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEService.php';


$service = new IEService($oPage->getRequestValue('service_id', 'int'));


$oPage->addItem(new UI\PageTop(_('Migrace služby') . ' ' . $service->getName()));
$oPage->addPageColumns();

$oPage->columnII->addItem(new \Ease\Html\H3Tag(array(new IEPlatformIcon($service->getDataValue('platform')), $service->getName())));


$form = $oPage->columnII->addItem(new \Ease\TWB\Form('Service', 'importer.php?class=service', 'POST'));
$form->setTagID($form->getTagName());
if (!is_null($service->getMyKey())) {
    $form->addItem(new \Ease\Html\InputHiddenTag($service->getMyKeyColumn(), $service->getMyKey()));
}
$form->addItem('<br>');

foreach ($service->data as $key => $value) {
    if (is_null($value)) {
        continue;
    }
    if (is_array($value)) {
        $form->addItem(new \Ease\Html\TextareaTag($key, serialize($value)));
    } else {
        $form->addItem(new \Ease\Html\InputTextTag($key, $value));
    }
}

$form->addItem(new \Ease\TWB\SubmitButton(_('Uložit'), 'success'));
$oPage->AddCss('
input.ui-button { width: 100%; }
');


$oPage->addItem(new UI\PageBottom());

$oPage->draw();
