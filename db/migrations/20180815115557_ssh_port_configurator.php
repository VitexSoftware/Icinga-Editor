<?php


use Phinx\Migration\AbstractMigration;

class SshPortConfigurator extends AbstractMigration
{
    /**
     */
    public function change()
    {
        $this->execute("UPDATE `service` SET `configurator` = 'ssh' WHERE `service`.`service_description` = 'SSH'");
        $this->execute("UPDATE `command` SET `command_line` = '/usr/lib/nagios/plugins/check_ssh -p \'$ARG1$\' \'$HOSTADDRESS$\'' WHERE `command`.`command_name` = 'check_ssh' ");
    }
}
