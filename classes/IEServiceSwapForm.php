<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'classes/IEServiceSelect.php';

/**
 * Description of IEServiceDeleteForm
 *
 * @author vitex
 */
class IEServiceSwapForm extends EaseTWBForm
{

    /**
     * Služba
     * @var IEService
     */
    public $service = null;

    function __construct($service)
    {
        parent::__construct('swapService', null, 'GET');
        $this->service = $service;
    }

    function finalize()
    {
        $addNewItem = new IEServiceSelect('new_service_id');

        $this->addItem(new EaseTWBFormGroup(_('Náhradní služba'), $addNewItem, _('Jméno služby'), sprintf(_('Tato služba se zamění za právě zvolenou službu <strong>%s</strong> u všech hostů kteří ji používají'), $this->service->getName())
        ));

        $this->addItem(new EaseTWBFormGroup(_('Vyměnit'), new EaseTWSubmitButton(_('Vyměnit službu') . ' ' . EaseTWBPart::GlyphIcon('flash'), 'info')));

        $this->addItem(new EaseHtmlInputHiddenTag('service_id', $this->service->getID()));
        $this->addItem(new EaseHtmlInputHiddenTag('action', 'swap'));
    }

}
