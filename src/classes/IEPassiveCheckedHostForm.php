<?php

/**
 * Formulář průvodce založením nového hosta
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
require_once 'classes/IEPlatformSelector.php';
require_once 'Ease/EaseTWBootstrap.php';

/**
 * Description of NewPassiveCheckedHostForm
 *
 * @author vitex
 */
class IEPassiveCheckedHostForm extends EaseTWBForm
{

    function afterAdd()
    {
        $this->addItem(new EaseTWBFormGroup(_('Jméno'), new EaseHtmlInputTextTag('host_name', EaseShared::webPage()->getRequestValue('host_name')), _('hostname'), _('DOMAIN\machine'), _('Název sledovaného stroje')));
        $this->addItem(new EaseTWBFormGroup(_('Platforma'), new IEPlatformSelector('platform'), null, _('Platforma sledovaného stroje')));
        $this->addItem(new EaseTWSubmitButton(_('Založit') . '&nbsp' . EaseTWBPart::GlyphIcon('forward'), 'success'));
        $this->addItem(new EaseHtmlInputHiddenTag('host_group', EaseShared::webPage()->getRequestValue('host_group')));
    }

}
