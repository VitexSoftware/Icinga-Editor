<?php

namespace Icinga\Editor;

/**
 * Databse fixer
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class DbFixer extends \Ease\Html\UlTag {

    public function __construct() {
        parent::__construct();
        $this->fixContactIDs();
        $this->fixHostNameIDs();
        $this->fixHostHostgroupID();
        $this->cleanUnusedServices();
        $this->cleanUnusedHosts();
        $this->setTagClass('list-group');
    }

    public function cleanUnusedHosts() {
        $services = \Ease\Shared::db()->queryToArray('SELECT service_id,service_description,host_name FROM service',
                'service_id');
        if (count($services)) {

            foreach ($services as $serviceID => $service) {

                if ($serviceID == 411) {
                    echo '';
                }

                if (($service['host_name'] == 'a:0:{}') || (!strlen(trim($service['host_name'])))) {
                    continue;
                }
                $serviceHosts = unserialize($service['host_name']);
                if (count($serviceHosts)) {
                    $ok = true;
                    foreach ($serviceHosts as $serviceHostID => $serviceHost) {
                        if (!\Ease\Shared::db()->queryToCount('SELECT alias FROM host WHERE host_name like "' . $serviceHost . '"  AND host_id=' . $serviceHostID)) {
                            $ok = false;
                            unset($serviceHosts[$serviceHostID]);
                            $this->addStatusMessage(sprintf(_('Unexistent host %s in service %s: fixing'),
                                            $service['service_description'],
                                            $serviceHost));
                        }
                    }
                    if ($ok === false) {
                        $servicer = new Engine\Service($serviceID);
                        $servicer->setDataValue('host_name', $serviceHosts);
                        if ($servicer->saveToSQL()) {
                            $servicer->addStatusMessage(sprintf(_('Service %s was fixed'),
                                            $servicer->getName()), 'success');
                        } else {
                            $servicer->addStatusMessage(sprintf(_('Service %s was not fixed'),
                                            $servicer->getName()), 'warning');
                        }
                    }
                }
            }
        }
    }

    /**
     * Delete unused child services
     */
    public function cleanUnusedServices() {
        $which = 'host_name = \'a:0:{}\' AND parent_id IS NOT NULL';
        $services = \Ease\Shared::db()->queryToArray('SELECT service_description FROM service WHERE ' . $which);
        if (count($services)) {
            \Ease\Shared::db()->exeQuery('DELETE FROM service WHERE ' . $which);
            foreach ($services as $service) {
                $servicesDeleted[] = current($service);
            }
            $this->addItemSmart(_('Delete unused services') . ': ' . implode(' , ',
                            $servicesDeleted), ['class' => 'list-group-item']);
        }
    }

    public function fixHostNameIDs() {
        $hostsOK = [];
        $hostsErr = [];

        $host = new Engine\Host();

        $service = new Engine\Service();
        $services = $service->getColumnsFromSQL([$service->keyColumn, $service->nameColumn,
            'host_name'], null, null, $service->keyColumn);
        foreach ($services as $serviceId => $serviceInfo) {
            $service->loadFromSQL($serviceId);

            foreach ($service->getDataValue('host_name') as $hostId => $hostName) {
                if (!strlen($hostName)) {
                    unset($service->data['host_name'][$hostId]);
                    $hostsOK[] = '(undefined)';
                }
                $hostFound = $host->loadFromSQL($hostName);
                if ($hostId != $host->getId()) {
                    if ($service->delMember('host_name', $hostId, $hostName) && $service->addMember('host_name',
                                    $host->getId(), $hostName)) {
                        $hostsOK[] = $hostName;
                    } else {
                        $hostsErr[] = $hostName;
                    }
                }
            }
            if (count($hostsOK)) {
                if ($service->saveToSQL()) {
                    $this->addItemSmart(sprintf(_('<strong>%s</strong> : %s'),
                                    $service->getName(), implode(',', $hostsOK)),
                            ['class' => 'list-group-item']);
                    $this->addStatusMessage(sprintf(_('%s : %s'),
                                    $service->getName(), implode(',', $hostsOK)),
                            'success');
                    $hostsOK = [];
                }
            }
        }

        $hostgroup = new Engine\Hostgroup();
        $hostgroups = $hostgroup->getListing();
        foreach ($hostgroups as $hostgroupId => $hostgroupInfo) {
            $hostgroup->loadFromSQL($hostgroupId);
            foreach ($hostgroup->getDataValue('members') as $hostId => $hostName) {
                $hostFound = $host->loadFromSQL($hostName);
                if ($hostId != $host->getId()) {
                    if ($hostgroup->delMember('members', $hostId, $hostName) && $hostgroup->addMember('members',
                                    $host->getId(), $hostName)) {
                        $hostsOK[] = $hostName;
                    } else {
                        $hostsErr[] = $hostName;
                    }
                }
            }
            if (count($hostsOK)) {
                if ($hostgroup->saveToSQL()) {
                    $this->addItemSmart(sprintf(_('<strong>%s</strong> : %s'),
                                    $hostgroup->getName(), implode(',', $hostsOK)),
                            ['class' => 'list-group-item']);
                    $this->addStatusMessage(sprintf(_('%s : %s'),
                                    $hostgroup->getName(), implode(',', $hostsOK)),
                            'success');
                    $hostsOK = [];
                }
            }
        }

        $childsAssigned = $host->dblink->queryToArray('SELECT ' . $host->keyColumn . ',' . $host->nameColumn . ' FROM ' . $host->myTable . ' WHERE '
                . 'parents' . ' IS NOT NULL && parents !=\'a:0:{}\'', $host->keyColumn);
        foreach ($childsAssigned as $chid_id => $child_info) {
            $child = new Engine\Host($chid_id);
            $parents = $child->getDataValue('parents');
            foreach ($parents as $parent_id => $parent_name) {
                $parent = new Engine\Host($parent_name);
                if ($parent->getId()) {
                    //Ok Host toho jména existuje
                    if ($parent->getId() != $parent_id) { //Ale nesedí ID
                        $child->delMember('parents', $parent_id, $parent_name);
                        $child->addMember('parents', $parent->getId(),
                                $parent_name);
                        $child->saveToSQL();
                        $this->addItemSmart(sprintf(_('Parent <strong>%s</strong> of host %s with wrong id ID'),
                                        $parent_name, $child_info[$host->nameColumn]),
                                ['class' => 'list-group-item']);
                    }
                } else {
                    //Host tohoto jména neexistuje, nemůže být tedy PARENT
                    $this->addItemSmart(sprintf(_('Parent <strong>%s</strong> of hosta %s does not exist'),
                                    $parent_name, $child_info[$host->nameColumn]),
                            ['class' => 'list-group-item']);
                    $child->delMember('parents', $parent->getId(), $parent_name);
                    $child->saveToSQL();
                }
            }
        }
    }

    function fixContactIDs() {
        $contactsOK = [];
        $contactsErr = [];

        $contact = new Engine\Contact;
        $service = new Engine\Service;
        $services = $service->getColumnsFromSQL([$service->keyColumn]);
        foreach ($services as $serviceId => $serviceInfo) {
            $serviceId = intval(current($serviceInfo));
            $service->loadFromSQL($serviceId);
            $contactNames = $service->getDataValue('contacts');
            if ($contactNames) {
                foreach ($contactNames as $contactId => $contactName) {
                    $contactFound = $contact->loadFromSQL($contactName);
                    if ($contactId != $contact->getId()) {
                        if ($service->delMember('contacts', $contactId,
                                        $contactName) && $service->addMember('contacts',
                                        $contact->getId(), $contactName)) {
                            $contactsOK[] = $contactName;
                        } else {
                            $contactsErr[] = $contactName;
                        }
                    }
                }
            }
            if (count($contactsOK)) {
                if ($service->saveToSQL()) {
                    $this->addItemSmart(sprintf(_('<strong>%s</strong> : %s'),
                                    $service->getName(), implode(',', $contactsOK)),
                            ['class' => 'list-group-item']);
                    $this->addStatusMessage(sprintf(_('%s : %s'),
                                    $service->getName(), implode(',', $contactsOK)),
                            'success');
                    $contactsOK = [];
                }
            }
        }

        $host = new Engine\Host;
        $hosts = $host->getColumnsFromSQL([$host->keyColumn]);
        foreach ($hosts as $hostInfo) {
            $hostId = intval(current($hostInfo));
            $host->loadFromSQL($hostId);
            $contactNames = $host->getDataValue('contacts');
            if ($contactNames) {
                foreach ($contactNames as $contactId => $contactName) {
                    $contactFound = $contact->loadFromSQL($contactName);
                    if ($contactId != $contact->getId()) {
                        if ($host->delMember('contacts', $contactId,
                                        $contactName) && $host->addMember('contacts',
                                        $contact->getId(), $contactName)) {
                            $contactsOK[] = $contactName;
                        } else {
                            $contactsErr[] = $contactName;
                        }
                    }
                }
            }
            if (count($contactsOK)) {
                if ($host->saveToSQL()) {
                    $this->addItemSmart(sprintf(_('<strong>%s</strong> : %s'),
                                    $host->getName(), implode(',', $contactsOK)),
                            ['class' => 'list-group-item']);
                    $this->addStatusMessage(sprintf(_('%s : %s'),
                                    $host->getName(), implode(',', $contactsOK)),
                            'success');
                    $contactsOK = [];
                }
            }
        }
    }

    function fixHostHostgroupID() {
        $hostgroupsOK = [];
        $hostgroupsErr = [];
        $host = new Engine\Host;
        $hostgroup = new Engine\Hostgroup;
        $hosts = $host->getColumnsFromSQL([$host->keyColumn]);
        foreach ($hosts as $hostInfo) {
            $hostId = intval(current($hostInfo));
            $host->loadFromSQL($hostId);
            $hostgroupNames = $host->getDataValue('hostgroups');
            if ($hostgroupNames) {
                foreach ($hostgroupNames as $hostgroupId => $hostgroupName) {
                    $hostgroupFound = $hostgroup->loadFromSQL($hostgroupName);
                    if ($hostgroupId != $hostgroup->getId()) {
                        if ($host->delMember('hostgroups', $hostgroupId,
                                        $hostgroupName) && $host->addMember('hostgroups',
                                        $hostgroup->getId(), $hostgroupName)) {
                            $hostgroupsOK[] = $hostgroupName;
                        } else {
                            $hostgroupsErr[] = $hostgroupName;
                        }
                    }
                }
            }
            if (count($hostgroupsOK)) {
                if ($host->saveToSQL()) {
                    $this->addItemSmart(sprintf(_('<strong>%s</strong> : %s'),
                                    $host->getName(), implode(',', $hostgroupsOK)),
                            ['class' => 'list-group-item']);
                    $this->addStatusMessage(sprintf(_('%s : %s'),
                                    $host->getName(), implode(',', $hostgroupsOK)),
                            'success');
                    $hostgroupsOK = [];
                }
            }
        }
    }

}
