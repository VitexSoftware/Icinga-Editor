<?php

/**
 * Formulář pro test Disku windows
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
class CheckDriveSize extends \Icinga\Editor\UI\ServiceConfigurator {

    /**
     *
     */
    public function form() {
        $config = [
            'Drive' => null,
            'ShowAll' => null,
            'MaxWarn' => NULL,
            'MaxCrit' => NULL,
            'MinWarn' => NULL,
            'MinCrit' => NULL,
            'MaxWarnFree' => NULL,
            'MaxCritFree' => NULL,
            'MinWarnFree' => NULL,
            'MinCritFree' => NULL,
            'MaxWarnUsed' => NULL,
            'MaxCritUsed' => NULL,
            'MinWarnUsed' => NULL,
            'MinCritUsed' => NULL
        ];
        foreach (explode(' ', $this->commandParams[0]) as $cfg) {
            if (strstr($cfg, '=')) {
                list($key, $value) = explode('=', $cfg);
                $config[$key] = $value;
            } else {
                if ($cfg == 'ShowAll') {
                    $config[$cfg] = true;
                } else {
                    $config[$cfg] = null;
                }
            }
        }


        $drives = array_merge(['CheckAll' => _('All Drives')],
                array_combine(range('a', 'z'), range('A', 'Z')),
                ['\\\\' => _('Network Path')]);
        unset($drives[1]);
        foreach ($drives as $did => $dname) {
            if ($did != 'CheckAll') {
                $drives[$did] = $drives[$did] . ':';
            }
        }

        if (strstr($config['Drive'], '\\\\')) {
            $this->form->addInput(new \Ease\Html\SelectTag('Drive', $drives, '\\\\'),
                    _('Disk'), 'X:', _('Disk drive letter select'));
        } else {
            $this->form->addInput(new \Ease\Html\SelectTag('Drive', $drives,
                            str_replace(':', '', $config['Drive'])), _('Disk'), 'X:',
                    _('Disk drive letter select'));
        }

        if (!strstr($config['Drive'], '\\\\')) {
            $config['Drive'] = '';
        }
        $this->form->addItem(new \Ease\TWB\FormGroup(_('NetDrive'),
                        new \Ease\Html\InputTextTag('NetDrive', $config['Drive']),
                        '\\\\server\\path\\', _('Network drive path')));


        $this->form->addItem(new \Ease\TWB\FormGroup(_('MaxWarn'),
                        new \Ease\Html\InputTextTag('MaxWarn', $config['MaxWarn']),
                        '80%', _('Maximum value before a warning is returned.')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('MaxCrit'),
                        new \Ease\Html\InputTextTag('MaxCrit', $config['MaxCrit']),
                        '95%', _('Maximum value before a critical is returned.')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('MinWarn'),
                        new \Ease\Html\InputTextTag('MinWarn', $config['MinWarn']),
                        '10%', _('Minimum value before a warning is returned.')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('MinCrit'),
                        new \Ease\Html\InputTextTag('MinCrit', $config['MinCrit']),
                        '5%', _('Minimum value before a critical is returned.')));

        $this->form->addItem(new \Ease\TWB\FormGroup(_('MaxWarnFree'),
                        new \Ease\Html\InputTextTag('MaxWarnFree',
                                $config['MaxWarnFree']), '5%',
                        _('Maximum value before a warning is returned.')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('MaxCritFree'),
                        new \Ease\Html\InputTextTag('MaxCritFree',
                                $config['MaxCritFree']), '5%',
                        _('Maximum value before a critcal is returned.')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('MinWarnFree'),
                        new \Ease\Html\InputTextTag('MinWarnFree',
                                $config['MinWarnFree']), '5%',
                        _('Minimum value before a warning is returned.')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('MinCritFree'),
                        new \Ease\Html\InputTextTag('MinCritFree',
                                $config['MinCritFree']), '5%',
                        _('Minimum value before a critcal is returned.')));

        $this->form->addItem(new \Ease\TWB\FormGroup(_('MaxWarnUsed'),
                        new \Ease\Html\InputTextTag('MaxWarnUsed',
                                $config['MaxWarnUsed']), '5%',
                        _('Maximum value before a warning is returned.')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('MaxCritUsed'),
                        new \Ease\Html\InputTextTag('MaxCritUsed',
                                $config['MaxCritUsed']), '5%',
                        _('Maximum value before a critcal is returned.')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('MinWarnUsed'),
                        new \Ease\Html\InputTextTag('MinWarnUsed',
                                $config['MinWarnUsed']), '5%',
                        _('Minimum value before a warning is returned.')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('MinCritUsed'),
                        new \Ease\Html\InputTextTag('MinCritUsed',
                                $config['MinCritUsed']), '5%',
                        _('Minimum value before a critcal is returned.')));

        $this->form->addInput(new \Icinga\Editor\UI\TWBSwitch('ShowAll',
                        $config['ShowAll']), _('Show All'), null,
                _('Configures display format (if set shows all items not only failures, if set to long shows all cores).'));

        //    $this->form->addInput(new \Ease\Html\InputTextTag('orig', $this->commandParams[0], array('disabled')));
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
                case 'NetDrive':
                case 'Drive':
                    if ($value == 'CheckAll') {
                        $config['Drive'] = 'CheckAll';
                        $this->tweaker->service->setDataValue($this->tweaker->service->nameColumn,
                                _('Všechny jednotky'));
                    } else {
                        if (strlen(trim($value)) && ($value != '\\\\')) {
                            if (strstr($value, '\\\\')) {
                                $config['Drive'] = 'Drive=' . $value;
                                $this->tweaker->service->setDataValue($this->tweaker->service->nameColumn,
                                        \Icinga\Editor\NSCPConfigBatGenerator::stripServiceName(_('NetDisk') . ' ' . $value));
                                $this->tweaker->service->setDataValue('display_name',
                                        sprintf(_('Disk drive %s empty space: '),
                                                $value));
                            } else {
                                $config['Drive'] = 'Drive=' . $value . ':';
                                $this->tweaker->service->setDataValue($this->tweaker->service->nameColumn,
                                        _('Disk') . ' ' . strtoupper($value) . ':');
                                $this->tweaker->service->setDataValue('display_name',
                                        sprintf(_('Disk drive %s empty space: '),
                                                strtoupper($value)));
                            }
                        }
                    }
                    break;
                case 'ShowAll':
                    if ($value) {
                        $config[] = 'ShowAll';
                    }
                    break;
                case 'MaxWarn':
                case 'MaxCrit':
                case 'MinWarn':
                case 'MinCrit':
                case 'MaxWarnFree':
                case 'MaxCritFree':
                case 'MinWarnFree':
                case 'MinCritFree':
                case 'MaxWarnUsed':
                case 'MaxCritUsed':
                case 'MinWarnUsed':
                case 'MinCritUsed':
                    if ($value) {
                        $config[] = $key . '=' . $value;
                    }
                    break;

                default :
                    break;
            }
        }



        if (count($config)) {

            $this->tweaker->service->setDataValue('check_command-params',
                    implode(' ', $config));
            $this->addStatusMessage($this->tweaker->service->getDataValue('check_command-params'));
            return parent::reconfigureService();
        }

        return FALSE;
    }

}
