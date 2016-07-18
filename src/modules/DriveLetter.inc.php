<?php

/**
 * Formulář pro vyber pismene Disku windows
 *
 * @package    IcingaEditor
 * @subpackage plugins
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2014 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\modules;

/**
 * Description of ping
 *
 * @author vitex
 */
class DriveLetter extends \Icinga\Editor\UI\ServiceConfigurator {

    /**
     *
     */
    public function form() {
        $config = [
            'Drive' => null
        ];
        foreach (explode(' ', $this->commandParams[0]) as $cfg) {
            if (strstr($cfg, '=')) {
                list($key, $value) = explode('=', $cfg);
                $config[$key] = $value;
            } else {
                $config[$cfg] = null;
            }
        }

        $drives = array_combine(range('a', 'z'), range('A', 'Z'));

        unset($drives[1]);
        foreach ($drives as $did => $dname) {
            $drives[$did] = $drives[$did] . ':';
        }



        if (strstr($config['Drive'], '\\\\')) {
            $this->form->addInput(new \Ease\Html\Select('Drive', $drives, '\\\\'), _('Disk'), 'X:', _('Volba písmene sledované diskové jednotky'));
        } else {
            $this->form->addInput(new \Ease\Html\Select('Drive', $drives, str_replace(':', '', $config['Drive'])), _('Disk'), 'X:', _('Volba písmene sledované diskové jednotky'));
        }
    }

    /**
     * Zpracování formuláře
     *
     * @return boolean
     */
    public function reconfigureService() {
        $config = [];
        $page = \Ease\Shared::webPage();

        foreach ($page->getRequestValues() as $key => $value) {
            switch ($key) {
                case 'Drive':

                    if (strlen(trim($value)) && ($value != '\\\\')) {
                            $config['Drive'] = $value . ':';
                            $this->tweaker->service->setDataValue($this->tweaker->service->nameColumn, _('Disk') . ' ' . strtoupper($value) . ':');
                            $this->tweaker->service->setDataValue('display_name', sprintf(_('Volné místo disku %s: '), strtoupper($value)));
                    }

                    break;

                default:
                    break;
            }
        }



        if (count($config)) {

            $this->tweaker->service->setDataValue('check_command-params', implode(' ', $config));

            return parent::reconfigureService();
        }

        return FALSE;
    }

}
