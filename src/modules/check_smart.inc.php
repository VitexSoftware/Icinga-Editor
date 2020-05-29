<?php

/**
 * Windows Drive test form
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2014 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\modules;

/**
 * Description of ping
 *
 * @author vitex
 */
class check_smart extends \Icinga\Editor\UI\ServiceConfigurator {

    /**
     *
     */
    public function form() {
        $config = [
            '--device' => '/dev/sda',
            '--interface' => 'scsi'
        ];
        foreach (explode(' ', $this->commandParams[0]) as $cfg) {
            if (strstr($cfg, '=')) {
                list($key, $value) = explode('=', $cfg);
                $config[$key] = $value;
            }
        }


        $this->form->addInput(new \Ease\Html\Select('Disk', $drives,
                        str_replace(':', '', $config['--device'])), _('Disk'),
                '/dev/sdX:', _('Choose drive Letter'));

        $this->form->addItem(new \Ease\TWB\FormGroup(_('Warning treshold'),
                        new \Ease\Html\InputTextTag('MaxWarn', $config['MaxWarn']),
                        '80%', _('Maximum value before a warning is returned.')));
    }

    /**
     * Zpracování formuláře
     *
     * @return boolean
     */
    public function reconfigureService() {
        $configResult = false;
        $config = [];
        $page = \Ease\Shared::webPage();

        foreach ($page->getRequestValues() as $key => $value) {
            switch ($key) {
                case '--device':
                    $config['--device'] = '--device ' . $value;
                    $this->tweaker->service->setDataValue($this->tweaker->service->nameColumn,
                            _('Disk') . ' ' . $value . ':');
                    $this->tweaker->service->setDataValue('display_name',
                            sprintf(_('SMART disk status %s: '), $value));
                    break;
                case '--interface':
                    $config['--interface'] = '--interface ' . $value;
                    break;
                default :
                    break;
            }
        }



        if (count($config)) {
            $this->tweaker->service->setDataValue('check_command-params',
                    implode(' ', $config));

            $configResult = parent::reconfigureService();
        }

        return $configResult;
    }

}
