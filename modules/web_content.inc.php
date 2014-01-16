<?php

/**
 * Formulář testu webového obsahu
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
class web_content extends IEServiceConfigurator
{

    function init()
    {
        $hostname = $this->tweaker->host->getDataValue('host_name');
        $command = 'http://' . $hostname . '/!' . _('Vše v pořádku');
        $this->tweaker->service->setDataValue('check_command-params', $command);
        return TRUE;
    }

    /**
     *
     */
    public function form()
    {
        $testUrl = $this->commandParams[0];
        $reqText = $this->commandParams[1];
        if (isset($this->commandParams[2])) {
            $errText = $this->commandParams[2];
        } else {
            $errText = '';
        }

        $this->form->addItem(new EaseTWBFormGroup(_('Sledované url'), new EaseHtmlInputTextTag('testUrl', $testUrl), '', _('Adresa sledované stránky')));
        $this->form->addItem(new EaseTWBFormGroup(_('Očekávaný obsah'), new EaseHtmlInputTextTag('reqText', $reqText), '', _('Text očekávaný na stránce v rámci bezchybného obsahu')));
        $this->form->addItem(new EaseTWBFormGroup(_('Nechtěný obsah'), new EaseHtmlInputTextTag('errText', $errText), '', _('Neočekávaný text, např. "Error" nebo jiný fragment chybového hlášení')));
    }

    /**
     * Zpracování formuláře
     * 
     * @return boolean
     */
    public function configure()
    {
        $page = EaseShared::webPage();
        $testUrl = $page->getRequestValue('testUrl');
        $reqText = $page->getRequestValue('reqText');
        $errText = $page->getRequestValue('errText');


        if ($testUrl && $reqText) {
            if (substr($testUrl, 0, 4) != 'http') {
                $testUrl = 'http://' . $testUrl;
            }

            $regex = "((https?|ftp)\:\/\/)?"; // SCHEME
            $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
            $regex .= "([a-z0-9-.]*)\.([a-z]{2,3})"; // Host or IP
            $regex .= "(\:[0-9]{2,5})?"; // Port
            $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path
            $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
            $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor 
            if (!preg_match("/^$regex$/", $testUrl)) {
                $page->addStatusMessage(_('Neplatné url'),'error');
                return false;
            }

            $command = $testUrl . '!' . $reqText;
            $command .= '!' . $errText;
            $command .= '!10'; //Timeout
            

            $this->tweaker->service->setDataValue('check_command-params', $command);

            return parent::configure();
        }

        return FALSE;
    }

}
