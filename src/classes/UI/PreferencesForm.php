<?php

namespace Icinga\Editor\UI;

/**
 * Description of IEPreferencesForm
 *
 * @author vitex
 */
class PreferencesForm extends \Ease\TWB\Form
{

    function __construct($formName, $formAction = null, $formMethod = 'post',
                         $formContents = null, $tagProperties = null)
    {
        parent::__construct($formName, $formAction, $formMethod, $formContents,
            $tagProperties);

        $this->addItem(new \Ease\TWB\FormGroup(_('IP adresa serveru'),
            new \Ease\Html\InputTextTag('serverip'), '',
            $_SERVER['SERVER_ADDR'],
            _('Adresa na níž běží icinga. Sem se posílají NSCA testy a z této adresy je povoleno se dotazovat NRPE pluginů')));
        $this->addItem(new \Ease\TWB\FormGroup(_('nsca heslo'),
            new \Ease\Html\InputTextTag('nscapassword'), '', '',
            _('Heslo kterým je šifrována NSCA komunikace')));

        $this->addItem(new \Ease\TWB\SubmitButton(_('Uložit').'&nbsp'.\Ease\TWB\Part::GlyphIcon('save'),
            'success'));
    }

}
