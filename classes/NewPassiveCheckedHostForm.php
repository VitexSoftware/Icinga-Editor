<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'classes/IEPlatformSelector.php';
require_once 'Ease/EaseTWBootstrap.php';

/**
 * Description of NewPassiveCheckedHostForm
 *
 * @author vitex
 */
class NewPassiveCheckedHostForm extends EaseTWBForm
{

    function afterAdd()
    {
        $this->addItem(new EaseTWBFormGroup(_('Jméno'), new EaseHtmlInputTextTag('host_name'), $this->webPage->getRequestValue('host_name'), _('DOMAIN\machine'), _('Název sledovaného stroje')));
        $this->addItem(new EaseTWBFormGroup(_('Platforma'), new IEPlatformSelector('platform'), null, _('Platforma sledovaného stroje')));
        $this->addItem(new EaseTWSubmitButton(_('Založit') . '&nbsp' . EaseTWBPart::GlyphIcon('forward'), 'success'));
    }

}
