<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Ease/EaseHtml.php';

/**
 * Description of IEContactConfigurator
 *
 * @author vitex
 */
class IEContactConfigurator extends EaseHtmlDivTag
{

    /**
     * Objekt tweakeru
     * @var IEContactTweaker
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

    public $commonFields = array('host_notifications_enabled','','service_notifications_enabled');

    /**
     * Obecný modul pro konfiguraci služby
     * @param IEContactTweaker $tweaker
     */
    public function __construct($tweaker)
    {
        parent::__construct();
        $this->tweaker = &$tweaker;
        if(!$this->tweaker->contact->getDataValue('DatSave')){
            if($this->init()){
                $this->tweaker->contact->saveToMySQL();
                EaseShared::webPage()->addStatusMessage(_('Prosím potvrďte nastavení kontaktu'));
            }
        }
        
    }

    /**
     * Výchozí konfigurace služby těsně po naklonování
     * 
     * @return boolean
     */
    public function init(){
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
                $this->tweaker->contact->setDataValue($cf,null);
            } else {
                $this->tweaker->contact->setDataValue($cf,$value);
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
                $contactID = $this->tweaker->contact->saveToMySQL();
                if (is_null($contactID)) {
                    $this->addStatusMessage(_('Služba nebyla uložena'), 'error');
                } else {
                    $this->addStatusMessage(_('Služba byla uložena'), 'success');
                }
            } else {
                $this->addStatusMessage(_('Formulář nebyl uložen'), 'warning');
            }
        }

        $this->commandParams = explode('!', $this->tweaker->contact->getDataValue('check_command-params'));
        $this->addItem(new EaseHtmlDivTag(null, _('Uloženo') . ': ' . $this->tweaker->contact->getDataValue('DatSave')));
        $this->addItem(new EaseHtmlDivTag(null, _('Založeno') . ': ' . $this->tweaker->contact->getDataValue('DatCreate')));
        $this->form = $this->addItem(new EaseTWBForm('servconf'));
        $this->form();

        foreach ($this->commonFields as $cf) {
            $this->form->addItem(new IECfgEditor($this->tweaker->contact,$cf));
        }
        $this->form->addItem(new EaseHtmlInputHiddenTag($this->tweaker->contact->getMyKeyColumn(), $this->tweaker->contact->getMyKey()));
        $this->form->addItem(new EaseHtmlInputHiddenTag($this->tweaker->host->getMyKeyColumn(), $this->tweaker->host->getMyKey()));
        $this->form->addItem(new EaseHtmlInputHiddenTag('action','tweak'));
        $this->form->addItem('<br/>');
        $this->form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));
    }

}
