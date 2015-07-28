<?php

/**
 * Formulář průvodce založením nové služby
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
require_once 'classes/IEPlatformSelector.php';
require_once 'Ease/EaseTWBootstrap.php';

/**
 * Description of NewPassiveCheckedHostForm
 *
 * @author vitex
 */
class IEServiceWizardForm extends EaseTWBForm
{

    /**
     * Objekt služby
     * @var IEService
     */
    public $service = null;

    /**
     * Formulář založení pasivní služby
     *
     * @param IEService $service
     */
    function __construct($service)
    {
        parent::__construct('passive-service');
        $this->service = $service;
    }

    function finalize()
    {
        parent::finalize();
        $platform = $this->service->getDataValue('platform');
        $this->addItem(new EaseTWBFormGroup(_('Jméno'), new EaseHtmlInputTextTag('service_name', $this->service->getName()), $this->service->getName(), _('Název služby testu')));

        $addNewItem = new EaseHtmlInputSearchTag('check_command-remote', $this->service->getDataValue('check_command-remote'), array('class' => 'search-input', 'title' => _('vzdálený test')));
        $addNewItem->setDataSource('jsoncommand.php?maxRows=20&platform=' . $platform);

        $this->addItem(new EaseTWBFormGroup(_('Vzdálený Příkaz'), $addNewItem, _('Hledej příkazy pro: ') . $platform, _('Příkaz vykonávaný vzdáleným senzorem NRPE/NSCP.exe')));

        $this->addItem(new EaseTWBFormGroup(_('Parametry'), new EaseHtmlInputTextTag('check_command-params', $this->service->getDataValue('check_command-params')), $this->service->getDataValue('command-params'), _('Parametry vzdáleného příkazu. (Pro nrpe oddělované vykřičníkem.)')));


        $this->addItem(new EaseTWBFormGroup(_('Platforma'), new IEPlatformSelector('platform', null, $platform), _('Platforma sledovaného stroje')));

        $this->addItem(new EaseTWSubmitButton(_('Založit') . '&nbsp' . EaseTWBPart::GlyphIcon('forward'), 'success'));
        $serviceId = $this->service->getId();
        if ($serviceId) {
            $this->addItem(new EaseHtmlInputHiddenTag('service_id', $serviceId));
        }
    }

}
