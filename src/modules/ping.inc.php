<?php
/**
 * Formulář testu IMCP odezvy
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
class ping extends \Icinga\Editor\UI\ServiceConfigurator
{

    /**
     *
     */
    public function form()
    {
        $warningValues  = explode(',', $this->commandParams[0]);
        $criticalValues = explode(',', $this->commandParams[1]);

        $this->form->addItem(new \Ease\TWB\FormGroup(_('Warning delay'),
                new \Ease\Html\InputTextTag('wt', $warningValues[0]), '100.0',
            _('The time in milliseconds, after which the warning will be reported')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('Warning Packet Loss'),
                new \Ease\Html\InputTextTag('wp', $warningValues[1]), '20 %',
            _('The percentage of lost packets after which the warning will be reported when the test is exceeded')));

        $this->form->addItem(new \Ease\TWB\FormGroup(_('Critical error delay'),
                new \Ease\Html\InputTextTag('ct', $criticalValues[0]), '500.0',
            _('The time in milliseconds after which the test will exceed the critical error')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('Critical Packet Loss'),
                new \Ease\Html\InputTextTag('cp', $criticalValues[1]), '60 %',
            _('The percentage of lost packets after which a critical error is reported in the test')));
    }

    /**
     * Zpracování formuláře
     * 
     * @return boolean
     */
    public function reconfigureService()
    {
        $page = \Ease\Shared::webPage();
        $wt   = $page->getRequestValue('wt', 'float');
        $ct   = $page->getRequestValue('ct', 'float');
        $wp   = str_replace('%', '', $page->getRequestValue('wp'));
        $cp   = str_replace('%', '', $page->getRequestValue('cp'));

        if ($wt && $ct && $wp && $cp) {

            $command = $wt.','.$wp.'%!'.$ct.','.$cp.'%';

            $this->tweaker->service->setDataValue('check_command-params',
                $command);

            return parent::reconfigureService();
        }

        return FALSE;
    }

}
