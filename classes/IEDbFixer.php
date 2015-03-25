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
        //$this->fixContactIDs();
        $this->fixHostNameIDs();
        $this->setTagClass('list-group');
    }

    public function fixHostNameIDs()
    {
        $hostsOK = array();
        $hostsErr = array();

        $host = new IEHost;

        $service = new IEService;
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

        $hostgroup = new IEHostgroup;
        $hostgroups = $hostgroup->getListing();
        foreach ($hostgroups as $hostgroupId => $hostgroupInfo) {
            $hostgroup->loadFromMySQL($hostgroupId);
            foreach ($hostgroup->getDataValue('members') as $hostId => $hostName) {
                $hostFound = $host->loadFromMySQL($hostName);
                if ($hostId != $host->getId()) {
                    if ($hostgroup->delMember('members', $hostId, $hostName) && $hostgroup->addMember('members', $host->getId(), $hostName)) {
                        $hostsOK[] = $hostName;
                    } else {
                        $hostsErr[] = $hostName;
                    }
                }
            }
            if (count($hostsOK)) {
                if ($hostgroup->saveToMySQL()) {
                    $this->addItemSmart(sprintf(_('<strong>%s</strong> : %s'), $hostgroup->getName(), implode(',', $hostsOK)), array('class' => 'list-group-item'));
                    $this->addStatusMessage(sprintf(_('%s : %s'), $hostgroup->getName(), implode(',', $hostsOK)), 'success');
                    $hostsOK = array();
                }
            }
        }
    }

    public function fixContactIDs()
    {
        $contactsOK = array();
        $contactsErr = array();

        $service = new IEService;
        $contact = new IEContact;
        $services = $service->getListing(0);
        foreach ($services as $serviceId => $serviceInfo) {
            $service->loadFromMySQL($serviceId);
            foreach ($service->getDataValue('contact_name') as $contactId => $contactName) {
                $contactFound = $contact->loadFromMySQL($contactName);
                if ($contactId != $contact->getId()) {
                    if ($service->delMember('contact_name', $contactId, $contactName) && $service->addMember('contact_name', $contact->getId(), $contactName)) {
                        $contactsOK[] = $contactName;
                    } else {
                        $contactsErr[] = $contactName;
                    }
                }
            }
            if (count($contactsOK)) {
                if ($service->saveToMySQL()) {
                    $this->addItemSmart(sprintf(_('<strong>%s</strong> : %s'), $service->getName(), implode(',', $contactsOK)), array('class' => 'list-group-item'));
                    $this->addStatusMessage(sprintf(_('%s : %s'), $service->getName(), implode(',', $contactsOK)), 'success');
                    $contactsOK = array();
                }
            }
        }
    }

}
