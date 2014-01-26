<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IEPortScanner
 *
 * @author vitex
 */
class IEPortScanner extends EaseSand
{

    /**
     * Porty k oskenování
     * @var array
     */
    public $ports = array();

    /**
     * Výsledky scanu
     * @var array
     */
    public $results = array();

    /**
     * Objekt služby
     * @var IEService
     */
    public $service = null;

    /**
     * Objekt hosta
     * @var IEHost
     */
    public $host = null;

    /**
     * Oskenuje hosta v argumentu na otevřené porty registrovaných služeb
     *
     * @param IEHost $hostToScan
     */
    public function __construct($hostToScan = null)
    {
        parent::__construct();
        $this->service = new IEService();
        if (is_object($hostToScan)) {
            $this->host = &$hostToScan;
            $this->performScan();
        }
    }

    /**
     * Přiřadí služby k hostům podle výsledků scannu
     */
    public function assignServices()
    {
        $success = 0;
        $hostmod = false;
        foreach ($this->results as $port) {
            switch ($port) {
                case 80:
                    if ($this->host->favToIcon()) {
                        $hostmod = true;
                    }
                    break;
                case 5666:
                    $this->host->setDataValue('platform', 'linux');
                    $hostmod = true;
                    break;
                case 12489:
                    $this->host->setDataValue('platform', 'windows');
                    $hostmod = true;
                    break;

                default:
                    break;
            }
            $this->service->setmyKeyColumn('tcp_port');
            $this->service->loadFromMySQL($port);
            $this->service->setmyKeyColumn('service_id');
            $this->service->addMember('host_name', $this->host->getId(), $this->host->getName());
            if ($this->service->saveToMySQL()) {
                $this->addStatusMessage(sprintf(_('Přidána sledovaná služba: %s'), $this->service->getName()), 'success');
                $success++;
            } else {
                $this->addStatusMessage(sprintf(_('Přidání sledované služby: %s se nezdařilo'), $this->service->getName()), 'error');
            }
        }
        if ($hostmod) {
            $this->host->saveToMySQL();
        }
        return $success;
    }

    /**
     * Vrací porty služeb k dispozici
     */
    public function getServicePorts()
    {
        $ports = $this->service->getColumnsFromMySQL('tcp_port', 'tcp_port IS NOT NULL AND public = 1', 'tcp_port', 'tcp_port');

        return array_keys($ports);
    }

    /**
     * Oskenuje porty
     *
     * @return int počet otestovaných portů
     */
    public function performScan()
    {
        $this->results = array();
        if (!count($this->ports)) {
            $this->ports = $this->getServicePorts();
        }

        foreach ($this->ports as $port) {
            if ($this->scan($port))
                $this->results[$port] = $port;
        }

        return count($this->ports);
    }

    /**
     * Zkusí se připojit k portu
     *
     * @param int $port
     */
    public function scan($port)
    {
        $fp = @fsockopen($this->host->getDataValue('address'), $port, $errno, $errstr, 2);
        @fclose($fp);

        return $fp;
    }

}
