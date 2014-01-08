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
    function __construct($hostToScan = null)
    {
        parent::__construct();
        $this->service = new IEService();
        $this->service->setmyKeyColumn('tcp_port');
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
        $Success = 0;
        foreach ($this->results as $port){
            if($port == 80){
                if($this->host->favToIcon()){
                    $this->host->saveToMySQL();
                }
            }
            $this->service->loadFromMySQL($port);
            $this->service->addMember('host_name', $this->host->getId(), $this->host->getName());
            if($this->service->saveToMySQL()){
                $this->addStatusMessage(sprintf(_('Přidána sledovaná služba: %s'),  $this->service->getName()),'success');
                $Success++;
            } else {
                $this->addStatusMessage(sprintf(_('Přidání sledované služby: %s se nezdařilo'),  $this->service->getName()),'error');
            }
        }
        return $Success;
    }

    
    /**
     * Vrací porty služeb k dispozici
     */
    public function getServicePorts()
    {
        $Ports = $this->service->getColumnsFromMySQL('tcp_port', 'tcp_port IS NOT NULL','tcp_port','tcp_port');
        return array_keys($Ports);
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

        foreach ($this->ports as $Port) {
            if ($this->scan($Port))
                $this->results[$Port] = $Port;
        }
        return count($this->ports);
    }

    /**
     * Zkusí se připojit k portu
     * 
     * @param int $Port
     */
    function scan($Port)
    {
        $fp = @fsockopen($this->host->getDataValue('address'), $Port,$errno, $errstr, 2);
        @fclose($fp);
        return $fp;
    }

}

?>
