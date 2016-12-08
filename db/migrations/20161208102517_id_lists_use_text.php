<?php

use Phinx\Migration\AbstractMigration;

class IdListsUseText extends AbstractMigration
{

    /**
     */
    public function change()
    {
        $contact      = $this->table('contact');
        $contact->changeColumn('host_notification_commands', 'text')
            ->changeColumn('service_notification_commands', 'text')
            ->save();
        $contactgroup = $this->table('contactgroup');
        $contactgroup->changeColumn('members', 'text')
            ->changeColumn('contactgroup_members', 'text')
            ->save();

        $host = $this->table('host');
        $host->changeColumn('parents', 'text')
            ->changeColumn('hostgroups', 'text')
            ->changeColumn('contacts', 'text')
            ->changeColumn('contact_groups', 'text')
            ->save();

        $hostgroup = $this->table('hostgroup');
        $hostgroup->changeColumn('members', 'text')
            ->changeColumn('hostgroup_members', 'text')
            ->save();

        $service = $this->table('service');
        $service->changeColumn('host_name', 'text')
            ->changeColumn('hostgroup_name', 'text')
            ->changeColumn('servicegroups', 'text')
            ->changeColumn('contacts', 'text')
            ->changeColumn('contact_groups', 'text')
            ->save();

        $servicegroup = $this->table('servicegroup');
        $servicegroup->changeColumn('members', 'text')
            ->changeColumn('servicegroup_members', 'text')
            ->save();
    }

}
