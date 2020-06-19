<?php

use Phinx\Migration\AbstractMigration;

class BiggerNotifOpts extends AbstractMigration {

    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change() {

        //set sql_mode = 'STRICT_ALL_TABLES';

//        $this->getAdapter()->execute('SET sql_mode="STRICT_ALL_TABLES"');
//
//        $hostgroup = $this->table('service');
//        $hostgroup->changeColumn('notification_options', 'string', ['limit' => 10])
//                ->save();
    }

}
