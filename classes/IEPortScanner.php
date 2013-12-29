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
    public $Ports = array();

    /**
     * Výsledky scanu
     * @var array 
     */
    public $Results = array();

    /**
     * Objekt služby
     * @var IEService 
     */
    public $Service = null;

    /**
     * Objekt hosta
     * @var IEHost 
     */
    public $Host = null;

    /**
     * Oskenuje hosta v argumentu na otevřené porty registrovaných služeb
     * 
     * @param IEHost $HostToScan
     */
    function __construct($HostToScan = null)
    {
        parent::__construct();
        $this->Service = new IEService();
        $this->Service->setmyKeyColumn('tcp_port');
        if (is_object($HostToScan)) {
            $this->Host = &$HostToScan;
            $this->performScan();
        }
    }

    /**
     * Přiřadí služby k hostům podle výsledků scannu
     */
    public function assignServices()
    {
        $Success = 0;
        foreach ($this->Results as $Port){
            $this->Service->loadFromMySQL($Port);
            $this->Service->addMember('host_name', $this->Host->getId(), $this->Host->getName());
            if($this->Service->saveToMySQL()){
                $this->addStatusMessage(sprintf(_('Přidána sledovaná služba: %s'),  $this->Service->getName()),'success');
                $Success++;
            } else {
                $this->addStatusMessage(sprintf(_('Přidání sledované služby: %s se nezdařilo'),  $this->Service->getName()),'error');
            }
        }
        return $Success;
    }

    /**
     * Vrací porty služeb k dispozici
     */
    public function getServicePorts()
    {
        $Ports = $this->Service->getColumnsFromMySQL('tcp_port', 'tcp_port IS NOT NULL','tcp_port','tcp_port');
        return array_keys($Ports);
    }

    /**
     * Oskenuje porty 
     * 
     * @return int počet otestovaných portů
     */
    public function performScan()
    {
        $this->Results = array();
        if (!count($this->Ports)) {
            $this->Ports = $this->getServicePorts();
        }

        foreach ($this->Ports as $Port) {
            if ($this->scan($Port))
                $this->Results[$Port] = $Port;
        }
        return count($this->Ports);
    }

    /**
     * Zkusí se připojit k portu
     * 
     * @param int $Port
     */
    function scan($Port)
    {
        $fp = @fsockopen($this->Host->getDataValue('address'), $Port,$errno, $errstr, 2);
        @fclose($fp);
        return $fp;
    }

}

?>
