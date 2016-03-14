<?php

namespace Icinga\Editor\UI;

/**
 * Formulář průvodce založením nové služby
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class ServiceWizardForm extends \Ease\TWB\Form
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
        $this->addItem(new \Ease\TWB\FormGroup(_('Jméno'),
            new \Ease\Html\InputTextTag('service_name',
            $this->service->getName()), $this->service->getName(),
            _('Název služby testu')));

        $addNewItem = new \Ease\Html\InputSearchTag('check_command-remote',
            $this->service->getDataValue('check_command-remote'),
            array('class' => 'search-input', 'title' => _('vzdálený test')));
        $addNewItem->setDataSource('jsoncommand.php?maxRows=20&platform='.$platform);

        $this->addItem(new \Ease\TWB\FormGroup(_('Vzdálený Příkaz'),
            $addNewItem, _('Hledej příkazy pro: ').$platform,
            _('Příkaz vykonávaný vzdáleným senzorem NRPE/NSCP.exe')));

        $this->addItem(new \Ease\TWB\FormGroup(_('Parametry'),
            new \Ease\Html\InputTextTag('check_command-params',
            $this->service->getDataValue('check_command-params')),
            $this->service->getDataValue('command-params'),
            _('Parametry vzdáleného příkazu. (Pro nrpe oddělované vykřičníkem.)')));


        $this->addItem(new \Ease\TWB\FormGroup(_('Platforma'),
            new IEPlatformSelector('platform', null, $platform),
            _('Platforma sledovaného stroje')));

        $this->addItem(new \Ease\TWB\SubmitButton(_('Založit').'&nbsp'.\Ease\TWB\Part::GlyphIcon('forward'),
            'success'));
        $serviceId = $this->service->getId();
        if ($serviceId) {
            $this->addItem(new \Ease\Html\InputHiddenTag('service_id',
                $serviceId));
        }
    }
}