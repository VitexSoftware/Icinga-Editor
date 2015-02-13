<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IEDbFixer
 *
 * @author vitex
 */
class IEDbFixer extends EaseHtmlUlTag
{

    public function __construct()
    {
        parent::__construct();
        $this->fixHostNameIDs();
        $this->setTagClass('list-group');
    }

    public function fixHostNameIDs()
    {
        $hostsOK = array();
        $hostsErr = array();

        $service = new IEService;
        $host = new IEHost;
        $services = $service->getListing(0);
        foreach ($services as $serviceId => $serviceInfo) {
            $service->loadFromMySQL($serviceId);
            foreach ($service->getDataValue('host_name') as $hostId => $hostName) {
                $hostFound = $host->loadFromMySQL($hostName);
                if ($hostId != $host->getId()) {
                    if ($service->delMember('host_name', $hostId, $hostName) && $service->addMember('host_name', $host->getId(), $hostName)) {
                        $hostsOK[] = $hostName;
                    } else {
                        $hostsErr[] = $hostName;
                    }
                }
            }
            if (count($hostsOK)) {
                if ($service->saveToMySQL()) {
                    $this->addItemSmart(sprintf(_('<strong>%s</strong> : %s'), $service->getName(), implode(',', $hostsOK)), array('class' => 'list-group-item'));
                    $this->addStatusMessage(sprintf(_('%s : %s'), $service->getName(), implode(',', $hostsOK)), 'success');
                    $hostsOK = array();
                }
            }
        }
    }

}
