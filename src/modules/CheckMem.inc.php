<?php
/**
 * Formulář pro test Disku windows
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
class CheckMem extends IEServiceConfigurator
{

    /**
     *
     */
    public function form()
    {
        $config = [
            'ShowAll' => null,
            'MaxWarn' => NULL,
            'MaxCrit' => NULL,
            'MinWarn' => NULL,
            'MinCrit' => NULL,
            'warn' => NULL,
            'crit' => NULL,
            'type' => NULL
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


        $types = ['physical' => _('Physical memory (RAM)'), 'committed' => _('total memory (RAM+PAGE)')];

        $this->form->addInput(new \Ease\Html\Select('type', $types,
            str_replace(':', '', $config['type'])), _('Typ'), '',
            _('Typ sledované paměti'));

        $this->form->addItem(new \Ease\TWB\FormGroup(_('MaxWarn'),
            new \Ease\Html\InputTextTag('MaxWarn', $config['MaxWarn']), '80%',
            _('Maximum value before a warning is returned.')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('MaxCrit'),
            new \Ease\Html\InputTextTag('MaxCrit', $config['MaxCrit']), '95%',
            _('Maximum value before a critical is returned.')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('MinWarn'),
            new \Ease\Html\InputTextTag('MinWarn', $config['MinWarn']), '10%',
            _('Minimum value before a warning is returned.')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('MinCrit'),
            new \Ease\Html\InputTextTag('MinCrit', $config['MinCrit']), '5%',
            _('Minimum value before a critical is returned.')));

        $this->form->addItem(new \Ease\TWB\FormGroup(_('warn'),
            new \Ease\Html\InputTextTag('warn', $config['warn']), '5%',
            _('Maximum value before a warning is returned.')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('crit'),
            new \Ease\Html\InputTextTag('crit', $config['crit']), '5%',
            _('Maximum value before a critcal is returned.')));


        $this->form->addInput(new UI\TWBSwitch('ShowAll', $config['ShowAll']),
            _('Zobrazit vše'), null,
            _('Configures display format (if set shows all items not only failures, if set to long shows all cores).'));

        //    $this->form->addInput(new \Ease\Html\InputTextTag('orig', $this->commandParams[0], array('disabled')));
    }

    /**
     * Zpracování formuláře
     *
     * @return boolean
     */
    public function reconfigureService()
    {
        $config = [];
        $page   = \Ease\Shared::webPage();

        foreach ($page->getRequestValues() as $key => $value) {
            switch ($key) {
                case 'ShowAll':
                    if ($value) {
                        $config[] = 'ShowAll';
                    }
                    break;
                case 'MaxWarn':
                case 'MaxCrit':
                case 'MinWarn':
                case 'MinCrit':
                case 'warn':
                case 'crit':
                case 'type':
                    if ($value) {
                        $config[] = $key.'='.$value;
                    }
                    break;

                default:
                    break;
            }
        }



        if (count($config)) {

            $this->tweaker->service->setDataValue('check_command-params',
                implode(' ', $config));

            return parent::reconfigureService();
        }

        return FALSE;
    }
}