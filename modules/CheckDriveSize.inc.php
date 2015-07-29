<?php

/**
 * Formulář testu IMCP odezvy
 *
 * @package    IcingaEditor
 * @subpackage plugins
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2014 Vitex@hippy.cz (G)
 */
require_once 'classes/IEServiceConfigurator.php';

/**
 * Description of ping
 *
 * @author vitex
 */
class CheckDriveSize extends IEServiceConfigurator
{

    /**
     *
     */
    public function form()
    {
        $config = array(
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
        );
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


        $drives = array_merge(array('CheckAll' => _('Všechny disky')), array_combine(range('a', 'z'), range('A', 'Z')));
        unset($drives[1]);
        foreach ($drives as $did => $dname) {
            if ($did != 'CheckAll') {
                $drives[$did] = $drives[$did] . ':';
            }
        }

        $this->form->addInput(new EaseHtmlSelect('Drive', $drives, str_replace(':', '', $config['Drive'])), _('Disk'), 'X:', _('Volba písmene sledované diskové jednotky'));

        $this->form->addItem(new EaseTWBFormGroup(_('MaxWarn'), new EaseHtmlInputTextTag('MaxWarn', $config['MaxWarn']), '80%', _('Maximum value before a warning is returned.')));
        $this->form->addItem(new EaseTWBFormGroup(_('MaxCrit'), new EaseHtmlInputTextTag('MaxCrit', $config['MaxCrit']), '95%', _('Maximum value before a critical is returned.')));
        $this->form->addItem(new EaseTWBFormGroup(_('MinWarn'), new EaseHtmlInputTextTag('MinWarn', $config['MinWarn']), '10%', _('Minimum value before a warning is returned.')));
        $this->form->addItem(new EaseTWBFormGroup(_('MinCrit'), new EaseHtmlInputTextTag('MinCrit', $config['MinCrit']), '5%', _('Minimum value before a critical is returned.')));

        $this->form->addItem(new EaseTWBFormGroup(_('MaxWarnFree'), new EaseHtmlInputTextTag('MaxWarnFree', $config['MaxWarnFree']), '5%', _('Maximum value before a warning is returned.')));
        $this->form->addItem(new EaseTWBFormGroup(_('MaxCritFree'), new EaseHtmlInputTextTag('MaxCritFree', $config['MaxCritFree']), '5%', _('Maximum value before a critcal is returned.')));
        $this->form->addItem(new EaseTWBFormGroup(_('MinWarnFree'), new EaseHtmlInputTextTag('MinWarnFree', $config['MinWarnFree']), '5%', _('Minimum value before a warning is returned.')));
        $this->form->addItem(new EaseTWBFormGroup(_('MinCritFree'), new EaseHtmlInputTextTag('MinCritFree', $config['MinCritFree']), '5%', _('Minimum value before a critcal is returned.')));

        $this->form->addItem(new EaseTWBFormGroup(_('MaxWarnUsed'), new EaseHtmlInputTextTag('MaxWarnUsed', $config['MaxWarnUsed']), '5%', _('Maximum value before a warning is returned.')));
        $this->form->addItem(new EaseTWBFormGroup(_('MaxCritUsed'), new EaseHtmlInputTextTag('MaxCritUsed', $config['MaxCritUsed']), '5%', _('Maximum value before a critcal is returned.')));
        $this->form->addItem(new EaseTWBFormGroup(_('MinWarnUsed'), new EaseHtmlInputTextTag('MinWarnUsed', $config['MinWarnUsed']), '5%', _('Minimum value before a warning is returned.')));
        $this->form->addItem(new EaseTWBFormGroup(_('MinCritUsed'), new EaseHtmlInputTextTag('MinCritUsed', $config['MinCritUsed']), '5%', _('Minimum value before a critcal is returned.')));

        $this->form->addInput(new EaseTWBSwitch('ShowAll', $config['ShowAll']), _('Zobrazit vše'), null, _('Configures display format (if set shows all items not only failures, if set to long shows all cores).'));

        //    $this->form->addInput(new EaseHtmlInputTextTag('orig', $this->commandParams[0], array('disabled')));
    }

    /**
     * Zpracování formuláře
     *
     * @return boolean
     */
    public function configure()
    {
        $config = array();
        $page = EaseShared::webPage();

        foreach ($page->getRequestValues() as $key => $value) {
            switch ($key) {
                case 'Drive':
                    if ($value == 'CheckAll') {
                        $config[] = 'CheckAll';
                        $this->tweaker->service->setDataValue($this->tweaker->service->nameColumn, _('Všechny jednotky'));
                    } else {
                        $config[] = 'Drive=' . $value . ':';
                        $this->tweaker->service->setDataValue($this->tweaker->service->nameColumn, _('Disk') . ' ' . strtoupper($value) . ':');
                        $this->tweaker->service->setDataValue('service_description', sprintf(_('Volné místo disku %s: '), strtoupper($value)));
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

                default:
                    break;
            }
        }



        if (count($config)) {

            $this->tweaker->service->setDataValue('check_command-params', implode(' ', $config));

            return parent::configure();
        }

        return FALSE;
    }

}
