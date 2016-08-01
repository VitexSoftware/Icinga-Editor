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

        $this->form->addItem(new \Ease\TWB\FormGroup(_('prodleva varování'),
            new \Ease\Html\InputTextTag('wt', $warningValues[0]), '100.0',
            _('Čas v milisekundách, po jehož překročení při testu bude hlášeno varování')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('ztráta varování'),
            new \Ease\Html\InputTextTag('wp', $warningValues[1]), '20 %',
            _('Procento ztracených paketů, po jehož překročení při testu bude hlášeno varování')));

        $this->form->addItem(new \Ease\TWB\FormGroup(_('prodleva kritické chyby'),
            new \Ease\Html\InputTextTag('ct', $criticalValues[0]), '500.0',
            _('Čas v milisekundách, po jehož překročení při testu bude hlášena kritická chyba')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('ztráta kritické chyby'),
            new \Ease\Html\InputTextTag('cp', $criticalValues[1]), '60 %',
            _('Procento ztracených paketů, po jehož překročení při testu bude hlášena kritická chyba')));
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
