<?php

/**
 * Icinga external Commands injector
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2016 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor;

/**
 * Send external commands to icinga using  /usr/bin/toicmdfile.sh
 *
 * @author vitex
 */
class ExternalCommand extends \Ease\Atom {

    /**
     * Command list
     * @var array
     */
    public $commands = [];

    /**
     * Add command to execution Quee
     * @param type $command
     */
    public function addCommand($command) {
        $this->commands[] = $command;
    }

    /**
     * Send all commands in queue to icinga
     */
    public function executeAll() {
        $results = [];
        foreach ($this->commands as $id => $command) {
            $result = $this->execute($command);
            if ($result !== false) {
                unset($this->commands[$id]);
                $results[$command] = $result;
            }
        }
        return $results;
    }

    /**
     * Send an external command to Icinga
     *
     * @param string $command
     * @return string|false execution result
     */
    public function execute($command) {
        $this->addStatusMessage('External command: ' . $command);
        ob_start();
        $result = system('sudo /usr/bin/toicmdfile.sh "[' . time() . '] ' . $command . '"');
        ob_end_clean();
        return $result;
    }

    /**
     * Execute All commands in stack
     */
    public function __destruct() {
        if (count($this->commands)) {
            $this->executeAll();
        }
    }

}
