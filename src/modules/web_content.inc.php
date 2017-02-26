<?php
/**
 * Formulář testu webového obsahu
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
class web_content extends \Icinga\Editor\UI\ServiceConfigurator
{

    /**
     * Initialize Module
     *
     * @return boolean
     */
    function init()
    {
        $hostname = $this->tweaker->host->getDataValue('host_name');
        $command  = 'http://'.$hostname.'/!'._('Vše v pořádku');
        $this->tweaker->service->setDataValue('check_command-params', $command);
        return TRUE;
    }

    /**
     * Form to check URL
     */
    public function form()
    {
        $testUrl = $this->commandParams[0];
        $reqText = isset($this->commandParams[1]) ? $this->commandParams[1] : '';
        $errText = isset($this->commandParams[2]) ? $this->commandParams[2] : '';

        $this->form->addItem(new \Ease\TWB\FormGroup(_('Watched URL'),
            new \Ease\Html\InputTextTag('testUrl', $testUrl), '',
            _('Web Page Address')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('Content needed'),
            new \Ease\Html\InputTextTag('reqText', $reqText), '',
            _('Text on healthy page')));
        $this->form->addItem(new \Ease\TWB\FormGroup(_('Content unneeded'),
            new \Ease\Html\InputTextTag('errText', $errText), '',
            _('Unwanted text, ex. "Error" ord another fragment of error message')));
    }

    /**
     * Zpracování formuláře
     * 
     * @return boolean
     */
    public function reconfigureService()
    {
        $success = false;
        $page    = \Ease\Shared::webPage();
        $testUrl = $page->getRequestValue('testUrl');
        $reqText = $page->getRequestValue('reqText');
        $errText = $page->getRequestValue('errText');


        if ($testUrl && $reqText) {
            if (substr($testUrl, 0, 4) != 'http') {
                $testUrl = 'http://'.$testUrl;
            }

            $regex = "((https?|ftp)\:\/\/)?"; // SCHEME
            $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
            $regex .= "([a-z0-9-.]*)\.([a-z]{2,3})"; // Host or IP
            $regex .= "(\:[0-9]{2,5})?"; // Port
            $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path
            $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
            $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor 
            if (!preg_match("/^$regex$/", $testUrl)) {
                $page->addStatusMessage(_('Invalid URL'), 'error');
                return false;
            }

            $command = $testUrl.'!'.$reqText;
            $command .= '!'.$errText;
            $command .= '!10'; //Timeout


            $this->tweaker->service->setDataValue('check_command-params',
                $command);

            $success = parent::reconfigureService();
        }

        return $success;
    }

}
