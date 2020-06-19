<?php

use Phinx\Migration\AbstractMigration;

class DefaultPublic extends AbstractMigration {

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
        $service = $this->table('service');
        $service->changeColumn('public', 'boolean', ['default' => false])
                ->save();
        $command = $this->table('command');
        $command->changeColumn('public', 'boolean', ['default' => false])
                ->save();
        $contact = $this->table('contact');
        $contact->changeColumn('public', 'boolean', ['default' => false])
                ->save();
        $host = $this->table('host');
        $host->changeColumn('public', 'boolean', ['default' => false])
                ->save();
        $script = $this->table('script');
        $script->changeColumn('public', 'boolean', ['default' => false])
                ->save();
        $tp = $this->table('timperiod');
        $tp->changeColumn('public', 'boolean', ['default' => false])
                ->save();
    }

}
