<?php

/**
 * SSH test form
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2014-2018 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\modules;

/**
 * Description of ssh
 *
 * @author vitex
 */
class ssh extends \Icinga\Editor\UI\ServiceConfigurator {

    /**
     *
     */
    public function form() {
        $port = isset($this->commandParams[0]) ? $this->commandParams[0] : 22;
        $this->form->addItem(new \Ease\TWB\FormGroup(_('SSH Port'),
                        new \Ease\Html\InputTextTag('port', $port), '22', _('Default SSH port is 22')));
    }

    /**
     * Zpracování formuláře
     * 
     * @return boolean
     */
    public function reconfigureService() {
        $page = \Ease\Shared::webPage();
        $port = $page->getRequestValue('port', 'int');

        if ($port) {
            $this->tweaker->service->setDataValue('check_command-params', $port);

            return parent::reconfigureService();
        }

        return FALSE;
    }

}
