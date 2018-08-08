<?php

namespace Icinga\Editor\UI;

/**
 * Formulář průvodce založením nové služby
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class ServiceWizardFormRemote extends \Ease\TWB\Form
{
    /**
     * Objekt služby
     * @var \Icinga\Editor\Engine\Service
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
        $this->addItem(new \Ease\TWB\FormGroup(_('Platform'),
            new PlatformSelector('platform', null, $platform),
            _('Watched machine platform')));


        $user = \Ease\Shared::user();
        if ($user->getSettingValue('admin')) {
            $this->addInput(new TWBSwitch('register',
                $this->service->getDataValue('user_id'), 1,
                ['onText' => _('Service'), 'offText' => _('Preset')]),
                _('Configuration type'));

            $this->addInput(new \Ease\Html\InputTextTag('name',
                $this->service->getDataValue('name')), _('Name'), _('Name'),
                _('Service or preset name '));

            $this->addInput(new TemplateSelect('use', $this->service,
                $this->service->getDataValue('use'))
                , _('Use preset'));

            $this->addInput(new UserSelect('user_id', null,
                $this->service->getDataValue('user_id'))
                , _('Owner'));

            $this->addInput(new YesNoSwitch('autocfg', false, 1),
                _('Show configuration dialog'));
        } else {
            $this->addItem(new \Ease\Html\InputHiddenTag('user_id',
                $user->getUserID()));
            $this->addItem(new \Ease\TWB\FormGroup(_('Name'),
                new \Ease\Html\InputTextTag('service_name',
                $this->service->getName()), $this->service->getName(),
                _('Test service name')));
        }



        $addNewItem = new \Ease\Html\InputSearchTag('check_command-remote',
            $this->service->getDataValue('check_command-remote'),
            ['class' => 'search-input', 'title' => _('remote test')]);
        $addNewItem->setDataSource('jsoncommand.php?maxRows=20&platform='.$platform);

        $this->addItem(new \Ease\TWB\FormGroup(_('Remote commands'),
            $addNewItem, _('Search commands for: ').$platform,
            _('Command executed by remote NRPE/NSCP.exe')));

        $this->addItem(new \Ease\TWB\FormGroup(_('Parameters'),
            new \Ease\Html\InputTextTag('check_command-params',
            $this->service->getDataValue('check_command-params')),
            $this->service->getDataValue('command-params'),
            _('Remote command params. (For nrpe divided by "!")')));

        $this->addItem(new \Ease\TWB\SubmitButton(_('Create').'&nbsp'.\Ease\TWB\Part::GlyphIcon('forward'),
            'success'));
        $serviceId = $this->service->getId();
        if ($serviceId) {
            $this->addItem(new \Ease\Html\InputHiddenTag('service_id',
                $serviceId));
        }
    }
}
