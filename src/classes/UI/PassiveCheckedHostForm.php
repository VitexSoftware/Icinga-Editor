<?php

namespace Icinga\Editor\UI;

/**
 * New Passive Host Wizard Form
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015-2016 Vitex@hippy.cz (G)
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
            _('hostname'), _('DOMAIN\machine'), _('Watched host name')));
        $this->addItem(new \Ease\TWB\FormGroup(_('Platform'),
            new PlatformSelector('platform'), null, _('Watched host platform')));

        $this->addInput(new UI\TWBSwitch('host_is_server', $check_method, true,
            ['handleWidth' => '200px', 'onText' => _('Yes'), 'offText' => _('No')]),
            _('Still running'), _('Still running ?'),
            _('<strong>Yes</strong> host is still Up. <br><strong>No</strong> device every night down (notebook or PC etc.)'));


        $this->addItem(new \Ease\TWB\SubmitButton(_('Create').'&nbsp'.\Ease\TWB\Part::GlyphIcon('forward'),
            'success'));
        $this->addItem(new \Ease\Html\InputHiddenTag('host_group',
            \Ease\Shared::webPage()->getRequestValue('host_group')));
    }

}
