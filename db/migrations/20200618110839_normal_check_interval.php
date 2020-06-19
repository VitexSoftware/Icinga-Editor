<?php

use Phinx\Migration\AbstractMigration;

class NormalCheckInterval extends AbstractMigration
{
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
    public function change()
    {
        $table  = $this->table('service');
            $table
                    ->addColumn('normal_check_interval', 'boolean', ['null' => true,'default'=>60])
                    ->addColumn('retry_check_interval', 'boolean', ['null' => true,'default'=>60])
                    
                ->update();
        
    }
}
