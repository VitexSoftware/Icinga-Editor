<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'classes/IECommand.php';
require_once 'classes/IEContactConfigurator.php';

/**
 * Description of IEContactTweaker
 *
 * @author vitex
 */
class IEContactTweaker extends EaseHtmlDivTag
{

    /**
     * Objekt služby
     * @var IEContact
     */
    public $contact = null;

    /**
     * Objekt Hosta
     * @var IEHost
     */
    public $host = null;

    /**
     *
     * @var type
     */
    public $configurator = null;

    /**
     * Umožňuje měnit parametry služeb
     *
     * @param IEContact $contact
     * @param IEHost    $host    ObjektHostu
     */
    public function __construct($contact, $host)
    {
        parent::__construct();

        $this->contact = $contact;
        $this->host = $host;

        $this->configurator = $this->addItem(new IEContactConfigurator($this));
    }
}
