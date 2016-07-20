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
     * @param \Icinga\Editor\Engine\Service $service
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
        $this->addItem(new \Ease\TWB\FormGroup(_('Platforma'),
            new PlatformSelector('platform', null, $platform),
            _('Platforma sledovaného stroje')));


        $user = \Ease\Shared::user();
        if ($user->getSettingValue('admin')) {
            $this->addInput(new TWBSwitch('register',
                $this->service->getDataValue('user_id'), 1,
                ['onText' => _('Sluzba'), 'offText' => _('Predloha')]),
                _('Typ konfigurace'));

            $this->addInput(new \Ease\Html\InputTextTag('name',
                $this->service->getDataValue('name')), _('Jmeno'), _('Nazev'),
                _('Nazev zakladane sluzby nebo predlohy '));

            $this->addInput(new TemplateSelect('use', $this->service,
                $this->service->getDataValue('use'))
                , _('Pouzit predlohu'));

            $this->addInput(new UserSelect('user_id', null,
                $this->service->getDataValue('user_id'))
                , _('Vlastnik'));
        } else {
            $this->addItem(new \Ease\Html\InputHiddenTag('user_id',
                $user->getUserID()));
            $this->addItem(new \Ease\TWB\FormGroup(_('Jméno'),
                new \Ease\Html\InputTextTag('service_name',
                $this->service->getName()), $this->service->getName(),
                _('Název služby testu')));
        }



        $addNewItem = new \Ease\Html\InputSearchTag('check_command-remote',
            $this->service->getDataValue('check_command-remote'),
            ['class' => 'search-input', 'title' => _('vzdálený test')]);
        $addNewItem->setDataSource('jsoncommand.php?maxRows=20&platform='.$platform);

        $this->addItem(new \Ease\TWB\FormGroup(_('Vzdálený Příkaz'),
            $addNewItem, _('Hledej příkazy pro: ').$platform,
            _('Příkaz vykonávaný vzdáleným senzorem NRPE/NSCP.exe')));

        $this->addItem(new \Ease\TWB\FormGroup(_('Parametry'),
            new \Ease\Html\InputTextTag('check_command-params',
            $this->service->getDataValue('check_command-params')),
            $this->service->getDataValue('command-params'),
            _('Parametry vzdáleného příkazu. (Pro nrpe oddělované vykřičníkem.)')));

        $this->addItem(new \Ease\TWB\SubmitButton(_('Založit').'&nbsp'.\Ease\TWB\Part::GlyphIcon('forward'),
            'success'));
        $serviceId = $this->service->getId();
        if ($serviceId) {
            $this->addItem(new \Ease\Html\InputHiddenTag('service_id',
                $serviceId));
        }
    }
}