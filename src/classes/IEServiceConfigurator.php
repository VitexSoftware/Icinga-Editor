<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Ease/EaseHtml.php';

/**
 * Description of IEServiceConfigurator
 *
 * @author vitex
 */
class IEServiceConfigurator extends EaseHtmlDivTag
{

    /**
     * Objekt tweakeru
     * @var IEServiceTweaker
     */
    public $tweaker = null;

    /**
     * Objekt formuláře
     * @var EaseTWBForm
     */
    public $form = null;

    /**
     * Pole konfiguračních parametrů příkazu služby
     * @var array
     */
    public $commandParams = null;

    /**
     * Položky vždy určené k tweakování
     * @var array
     */
    public $commonFields = array('check_interval');

    /**
     * Obecný modul pro konfiguraci služby
     * @param IEServiceTweaker $tweaker
     */
    public function __construct($tweaker)
    {
        parent::__construct();
        $this->tweaker = &$tweaker;
        if (!$this->tweaker->service->getDataValue('DatSave')) {
            if ($this->init()) {
                $this->tweaker->service->saveToMySQL();
                EaseShared::webPage()->addStatusMessage(_('Prosím potvrďte nastavení služby'));
            }
        }

        if ($this->tweaker->service->getDataValue('passive_checks_enabled')) {
            $this->commonFields[] = 'freshness_threshold';
        }
    }

    /**
     * Výchozí konfigurace služby těsně po naklonování
     *
     * @return boolean
     */
    public function init()
    {
        return FALSE;
    }

    /**
     * Funkce pro vykreslení formuláře
     */
    public function form()
    {

    }

    /**
     * funkce pro zpracování hodnot formuláře
     */
    public function reconfigureService()
    {
        foreach ($this->commonFields as $cf) {
            $value = EaseShared::webPage()->getRequestValue($cf);
            if ($value == 'NULL') {
                $this->tweaker->service->setDataValue($cf, null);
            } else {
                $this->tweaker->service->setDataValue($cf, $value);
            }
        }

        return true;
    }

    /**
     * Po přidání do stránky
     */
    public function afterAdd()
    {
        $webPage = EaseShared::webPage();
        if ($webPage->isPosted() && ($webPage->getRequestValue('action') == 'tweak')) {
            if ($this->reconfigureService()) {

                if ($webPage->getRequestValue('clone')) {
                    $oldService = new IEService($this->tweaker->service->getId());
                    $oldService->delMember(
                        'host_name', $this->tweaker->host->getId(), $this->tweaker->host->getName()
                    );
                    if ($oldService->saveToMySQL()) {
                        $oldService->addStatusMessage(_('Původní služba byla upravena'));
                    }

                    $this->tweaker->service->setDataValue('parent_id', $this->tweaker->service->getId());
                    $this->tweaker->service->unsetDataValue($this->tweaker->service->getmyKeyColumn());

                    $this->tweaker->service->addMember(
                        'host_name', $this->tweaker->host->getId(), $this->tweaker->host->getName()
                    );

                    $this->tweaker->service->setDataValue('hostgroup_name', array());
                    $this->tweaker->service->setDataValue('user_id', EaseShared::user()->getID());
                    $this->tweaker->service->setDataValue($this->tweaker->service->nameColumn, _('Klon') . ' ' . $this->tweaker->service->getName());
                    if ($this->tweaker->service->saveToMySQL()) {
                        $this->tweaker->service->addStatusMessage(_('Služba byla uložena jako klon'), 'success');
                        $webPage->redirect('servicetweak.php?service_id=' . $this->tweaker->service->getId() . '&host_id=' . $this->tweaker->host->getId());
                    } else {
                        $this->tweaker->service->addStatusMessage(_('Sužba nebyla naklonována'), 'warning');
                    }
                } else {
                    $serviceID = $this->tweaker->service->saveToMySQL();
                    if (is_null($serviceID)) {
                        $this->addStatusMessage(_('Služba nebyla uložena'), 'error');
                    } else {
                        $this->addStatusMessage(_('Služba byla uložena'), 'success');
                    }
                }
            } else {
                $this->addStatusMessage(_('Formulář nebyl uložen'), 'warning');
            }
        }

        $this->commandParams = explode('!', $this->tweaker->service->getDataValue('check_command-params'));
        $this->addItem(new EaseHtmlDivTag(null, _('Služba') . ': <strong>' . $this->tweaker->service->getName() . '</strong>'));
        $this->addItem(new EaseHtmlDivTag(null, _('Uloženo') . ': ' . $this->tweaker->service->getDataValue('DatSave')));
        $this->addItem(new EaseHtmlDivTag(null, _('Založeno') . ': ' . $this->tweaker->service->getDataValue('DatCreate')));

        $parent_id = (int) $this->tweaker->service->getDataValue('parent_id');
        if ($parent_id) {
            $parent_service = new IEService($parent_id);
            $this->addItem(new EaseTWBLabel('info', sprintf(_('Toto je odvozená služba od %s'), '<a href="service.php?service_id=' . $parent_id . '">' . $parent_service->getName() . '</a>')));
        } else {
            $this->addItem(new EaseTWBLabel('info', _('Toto je primární služba.')));
        }



        $this->form = $this->addItem(new EaseTWBForm('servconf'));
        $this->form();

        foreach ($this->commonFields as $cf) {
            $this->form->addItem(new IECfgEditor($this->tweaker->service, $cf));
        }
        $this->form->addItem(new EaseHtmlInputHiddenTag($this->tweaker->service->getMyKeyColumn(), $this->tweaker->service->getMyKey()));
        $this->form->addItem(new EaseHtmlInputHiddenTag($this->tweaker->host->getMyKeyColumn(), $this->tweaker->host->getMyKey()));
        $this->form->addItem(new EaseHtmlInputHiddenTag('action', 'tweak'));
        $this->form->addItem('<br/>');

        $this->form->addItem(new EaseTWSubmitButton(_('Uložit upravenou službu jako '), 'success'));
        $this->form->addItem(new IEYesNoSwitch('clone', false, 'true', array('onText' => _('Klon'), 'offText' => _('originál'))));

        $this->form->addItem(new EaseHtmlLabelTag('V případě uložení kopie přestane sledovaný host používat původní službu.'));
    }

}
