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
     * Obecný modul pro konfiguraci služby
     * @param IEServiceTweaker $tweaker
     */
    function __construct($tweaker)
    {
        parent::__construct();
        $this->tweaker = &$tweaker;
    }

    /**
     * Funkce pro vykreslení formuláře
     */
    function form()
    {
        
    }

    /**
     * funkce pro zpracování hodnot formuláře
     */
    function configure()
    {
        return false;
    }

    /**
     * Po přidání do stránky
     */
    function afterAdd()
    {
        if (EaseShared::webPage()->isPosted()) {
            if ($this->configure()) {
                $serviceID = $this->tweaker->service->saveToMySQL();
                if (is_null($serviceID)) {
                    $this->addStatusMessage(_('Služba nebyla uložena'), 'error');
                } else {
                    $this->addStatusMessage(_('Služba byla uložena'), 'success');
                }
            } else {
                $this->addStatusMessage(_('Formulář nebyl zpracován'), 'warning');
            }
        }
        
        $this->commandParams = explode('!', $this->tweaker->service->getDataValue('check_command-params'));
        $this->form = $this->addItem(new EaseTWBForm('servconf'));
        $this->form();
        $this->form->addItem(new EaseHtmlInputHiddenTag($this->tweaker->service->getMyKeyColumn(), $this->tweaker->service->getMyKey()));
        $this->form->addItem(new EaseHtmlInputHiddenTag($this->tweaker->host->getMyKeyColumn(), $this->tweaker->host->getMyKey()));
        $this->form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));
    }

}
