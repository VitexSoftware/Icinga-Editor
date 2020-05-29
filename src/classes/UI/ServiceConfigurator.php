<?php

namespace Icinga\Editor\UI;

/**
 * Description of ServiceConfigurator
 *
 * @author vitex
 */
class ServiceConfigurator extends \Ease\Html\DivTag {

    /**
     * Tweaker Object
     * @var ServiceTweaker
     */
    public $tweaker = null;

    /**
     * Form Object
     * @var \Ease\TWB\Form
     */
    public $form = null;

    /**
     * Service configuration parameters
     * @var array
     */
    public $commandParams = null;

    /**
     * Fields to tweak
     * @var array
     */
    public $commonFields = ['check_interval', 'check_command-params'];

    /**
     * Common service tweaking module
     * @param ServiceTweaker $tweaker
     */
    public function __construct($tweaker) {
        parent::__construct();
        $this->tweaker = &$tweaker;
        if (!$this->tweaker->service->getDataValue('DatSave')) {
            if ($this->init()) {
                $this->tweaker->service->saveToSQL();
                \Ease\WebPage::singleton()->addStatusMessage(_('Please confirm service setup'));
            }
        }

        if ($this->tweaker->service->getDataValue('passive_checks_enabled')) {
            $this->commonFields[] = 'freshness_threshold';
        }
    }

    /**
     * Initial configuration after cloning
     *
     * @return boolean
     */
    public function init() {
        return FALSE;
    }

    /**
     * Form draw functions
     */
    public function form() {
        
    }

    /**
     * Form filelds processor
     */
    public function reconfigureService() {
        foreach ($this->commonFields as $cf) {
            $value = \Ease\WebPage::singleton()->getRequestValue($cf);
            if (is_null($value)) {
                continue;
            }
            if ($value == 'NULL') {
                $this->tweaker->service->setDataValue($cf, null);
            } else {
                $this->tweaker->service->setDataValue($cf, $value);
            }
        }

        return true;
    }

    /**
     * Add Tweaker form into page
     */
    public function afterAdd() {
        $webPage = WebPage::singleton();
        if ($webPage->isPosted() && ($webPage->getRequestValue('action') == 'tweak')) {
            if ($this->reconfigureService()) {

                if ($webPage->getRequestValue('clone')) {
                    $oldService = new \Icinga\Editor\Engine\Service($this->tweaker->service->getId());
                    $oldService->delMember(
                            'host_name', $this->tweaker->host->getId(),
                            $this->tweaker->host->getName()
                    );
                    if ($oldService->saveToSQL()) {
                        $oldService->addStatusMessage(_('Original service was modified'));
                    }

                    $this->tweaker->service->setDataValue('parent_id',
                            $this->tweaker->service->getId());
                    $this->tweaker->service->unsetDataValue($this->tweaker->service->getKeyColumn());

                    $this->tweaker->service->addMember(
                            'host_name', $this->tweaker->host->getId(),
                            $this->tweaker->host->getName()
                    );

                    $this->tweaker->service->setDataValue('hostgroup_name', []);
                    $this->tweaker->service->setDataValue('user_id',
                            \Ease\Shared::user()->getID());
                    $this->tweaker->service->setDataValue($this->tweaker->service->nameColumn,
                            _('Clone') . ' ' . $this->tweaker->service->getName());
                    if ($this->tweaker->service->saveToSQL()) {
                        $this->tweaker->service->addStatusMessage(_('Service was saved as clone'),
                                'success');
                        $webPage->redirect('servicetweak.php?service_id=' . $this->tweaker->service->getId() . '&host_id=' . $this->tweaker->host->getId());
                    } else {
                        $this->tweaker->service->addStatusMessage(_('Service cloning failed'),
                                'warning');
                    }
                } else {
                    $serviceID = $this->tweaker->service->saveToSQL();
                    if (is_null($serviceID)) {
                        $this->addStatusMessage(_('Service saving failed'),
                                'error');
                    } else {
                        $this->addStatusMessage(_('Service was saved'),
                                'success');
                    }
                }
            } else {
                $this->addStatusMessage(_('Form was not saved'), 'warning');
            }
        }

        $this->commandParams = explode('!',
                $this->tweaker->service->getDataValue('check_command-params'));
        $this->addItem(new \Ease\Html\DivTag(_('Service') . ': <strong>' . $this->tweaker->service->getName() . '</strong>'));
        $this->addItem(new \Ease\Html\DivTag(_('Saved') . ': ' . $this->tweaker->service->getDataValue('DatSave')));
        $this->addItem(new \Ease\Html\DivTag(_('Created') . ': ' . $this->tweaker->service->getDataValue('DatCreate')));

        $parent_id = (int) $this->tweaker->service->getDataValue('parent_id');
        if ($parent_id) {
            $parent_service = new \Icinga\Editor\Engine\Service($parent_id);
            $this->addItem(new \Ease\TWB\Label('info',
                            sprintf(_('This is derived service fom %s'),
                                    '<a href="service.php?service_id=' . $parent_id . '">' . $parent_service->getName() . '</a>')));
        } else {
            $this->addItem(new \Ease\TWB\Label('info',
                            _('This is primary service.')));
        }



        $this->form = $this->addItem(new \Ease\TWB\Form(['name'=>'servconf']));
        $this->form();

        foreach ($this->commonFields as $cf) {
            $this->form->addItem(new CfgEditor($this->tweaker->service, $cf));
        }
        $this->form->addItem(new \Ease\Html\InputHiddenTag($this->tweaker->service->getKeyColumn(),
                $this->tweaker->service->getMyKey()));
        $this->form->addItem(new \Ease\Html\InputHiddenTag($this->tweaker->host->getKeyColumn(),
                $this->tweaker->host->getMyKey()));
        $this->form->addItem(new \Ease\Html\InputHiddenTag('action', 'tweak'));
        $this->form->addItem('<br/>');

        $this->form->addItem(new \Ease\TWB\SubmitButton(_('Save modified service as'),
                'success'));
        $this->form->addItem(new YesNoSwitch('clone', false, 'true',
                ['onText' => _('Clone'), 'offText' => _('Original')]));

        $this->form->addItem(new \Ease\Html\LabelTag('In case of saving of Service as Clone The Watched hosts use this new one.'));
    }
}