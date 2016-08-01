<?php

namespace Icinga\Editor\UI;

/**
 * Formulář průvodce založením nového hosta
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */

/**
 * Description of NewPassiveCheckedHostForm
 *
 * @author vitex
 */
class PassiveCheckedHostForm extends \Ease\TWB\Form
{

    function afterAdd()
    {
        $this->addItem(new \Ease\TWB\FormGroup(_('Jméno'),
            new \Ease\Html\InputTextTag('host_name',
            \Ease\Shared::webPage()->getRequestValue('host_name')),
            _('hostname'), _('DOMAIN\machine'), _('Název sledovaného stroje')));
        $this->addItem(new \Ease\TWB\FormGroup(_('Platforma'),
            new PlatformSelector('platform'), null,
            _('Platforma sledovaného stroje')));
        $this->addItem(new \Ease\TWB\SubmitButton(_('Založit').'&nbsp'.\Ease\TWB\Part::GlyphIcon('forward'),
            'success'));
        $this->addItem(new \Ease\Html\InputHiddenTag('host_group',
            \Ease\Shared::webPage()->getRequestValue('host_group')));
    }

}
