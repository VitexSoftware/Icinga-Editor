<?php

namespace Icinga\Editor;

/**
 * Description of PortScanner
 *
 * @author vitex
 */
class PortScanner extends \Ease\Sand {

    /**
     * Ports to scan
     * @var array
     */
    public $ports = [];

    /**
     * Scanning results
     * @var array
     */
    public $results = [];

    /**
     * Used service object
     * @var Engine\Service
     */
    public $service = null;

    /**
     * Host Object
     * @var Engine\Host
     */
    public $host = null;

    /**
     * Scan host for open ports of registered services
     *
     * @param Engine\Host $hostToScan
     */
    public function __construct($hostToScan = null) {
        parent::__construct();
        $this->service = new Engine\Service();
        if (is_object($hostToScan)) {
            $this->host = &$hostToScan;
            $this->performScan();
        }
    }

    /**
     * Assign scan results to services
     */
    public function assignServices() {
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

                default :
                    break;
            }
            $this->service->setKeyColumn('tcp_port');
            $this->service->loadFromSQL($port);
            $this->service->setKeyColumn('service_id');
            $this->service->addMember('host_name', $this->host->getId(),
                    $this->host->getName());
            if ($this->service->saveToSQL()) {
                $this->addStatusMessage(sprintf(_('Added watched services: %s'),
                                $this->service->getName()), 'success');
                $success++;
            } else {
                $this->addStatusMessage(sprintf(_('Adding watched service: %s failed'),
                                $this->service->getName()), 'error');
            }
        }
        if ($hostmod) {
            $this->host->saveToSQL();
        }

        return $success;
    }

    /**
     * Obtain known ports
     */
    public function getServicePorts() {
        $ports = $this->service->getColumnsFromSQL('tcp_port',
                'tcp_port IS NOT NULL AND public = 1', 'tcp_port', 'tcp_port');

        return array_keys($ports);
    }

    /**
     * TCP Scan of ports
     *
     * @return int scanned ports count
     */
    public function performScan() {
        $this->results = [];
        if (!count($this->ports)) {
            $this->ports = $this->getServicePorts();
        }

        foreach ($this->ports as $port) {
            if ($this->scan($port)) {
                $this->results[$port] = $port;
            }
        }

        return count($this->ports);
    }

    /**
     * Try to connect to port
     *
     * @param int $port
     */
    public function scan($port) {
        $fp = @fsockopen($this->host->getDataValue('address'), $port, $errno,
                        $errstr, 2);
        @fclose($fp);

        return $fp;
    }

}
