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

    public $commonFields = array('check_period');

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
    public function configure()
    {
        foreach ($this->commonFields as $cf) {
            $value = EaseShared::webPage()->getRequestValue($cf);
            if ($value == 'NULL') {
                $this->tweaker->service->setDataValue($cf,null);
            } else {
                $this->tweaker->service->setDataValue($cf,$value);
            }
        }

        return true;
    }

    /**
     * Po přidání do stránky
     */
    public function afterAdd()
    {
        if (EaseShared::webPage()->isPosted() && (EaseShared::webPage()->getRequestValue('action') == 'tweak') ) {
            if ($this->configure()) {
                $serviceID = $this->tweaker->service->saveToMySQL();
                if (is_null($serviceID)) {
                    $this->addStatusMessage(_('Služba nebyla uložena'), 'error');
                } else {
                    $this->addStatusMessage(_('Služba byla uložena'), 'success');
                }
            } else {
                $this->addStatusMessage(_('Formulář nebyl uložen'), 'warning');
            }
        }

        $this->commandParams = explode('!', $this->tweaker->service->getDataValue('check_command-params'));
        $this->addItem(new EaseHtmlDivTag(null, _('Uloženo') . ': ' . $this->tweaker->service->getDataValue('DatSave')));
        $this->addItem(new EaseHtmlDivTag(null, _('Založeno') . ': ' . $this->tweaker->service->getDataValue('DatCreate')));
        $this->form = $this->addItem(new EaseTWBForm('servconf'));
        $this->form();

        foreach ($this->commonFields as $cf) {
            $this->form->addItem(new IECfgEditor($this->tweaker->service,$cf));
        }
        $this->form->addItem(new EaseHtmlInputHiddenTag($this->tweaker->service->getMyKeyColumn(), $this->tweaker->service->getMyKey()));
        $this->form->addItem(new EaseHtmlInputHiddenTag($this->tweaker->host->getMyKeyColumn(), $this->tweaker->host->getMyKey()));
        $this->form->addItem(new EaseHtmlInputHiddenTag('action','tweak'));
        $this->form->addItem('<br/>');
        $this->form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));
    }

}
