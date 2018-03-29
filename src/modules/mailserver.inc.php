<?php
/**
 * Mailserver check config dialog
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2014-2017 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\modules;

/**
 * WebContent configuration dialog
 *
 * @author vitex
 */
class mailserver extends \Icinga\Editor\UI\ServiceConfigurator
{

    /**
     * Initialize Module
     *
     * @return boolean
     */
    function init()
    {
        return TRUE;
    }

    /**
     * Form to check URL
     */
    public function form()
    {
        $server   = $this->commandParams[0];
        $login    = isset($this->commandParams[1]) ? $this->commandParams[1] : '';
        $password = isset($this->commandParams[2]) ? $this->commandParams[2] : '';

        $this->form->addItem(new \Ease\TWB\FormGroup(_('Mailserver Host'),
                new \Ease\Html\InputTextTag('server', $server), '',
                _('Hostname of checked host')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('Mail User'),
                new \Ease\Html\InputTextTag('login', $login), '',
                _('Username for mailcheck')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('Mail Password'),
                new \Ease\Html\InputTextTag('password', $password), '',
                _('Pasword for mailcheck')));
    }

    /**
     * Zpracování formuláře
     * 
     * @return boolean
     */
    public function reconfigureService()
    {
        $success  = false;
        $page     = \Ease\Shared::webPage();
        $mailhost = $page->getRequestValue('server');
        $login    = $page->getRequestValue('login');
        $password = $page->getRequestValue('password');

        $ValidHostnameRegex = "^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$";

        if ($mailhost && $login) {

            if (!preg_match("/^$ValidHostnameRegex/", $mailhost)) {
                $page->addStatusMessage(_('Invalid URL'), 'error');
                return false;
            }

            $command = $mailhost.'!'.$login;
            $command .= '!'.$password;
            $command .= '!10'; //Timeout


            $this->tweaker->service->setDataValue('check_command-params',
                $command);

            $success = parent::reconfigureService();
        }

        return $success;
    }
}
