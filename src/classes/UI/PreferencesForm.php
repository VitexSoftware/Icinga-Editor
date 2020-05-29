<?php

namespace Icinga\Editor\UI;

/**
 * Description of IEPreferencesForm
 *
 * @author vitex
 */
class PreferencesForm extends \Ease\TWB\Form {

    function __construct($formName, $formAction = null, $formMethod = 'post',
            $formContents = null, $tagProperties = null) {
        parent::__construct($formName, $formAction, $formMethod, $formContents,
                $tagProperties);

        $this->addItem(new \Ease\TWB\FormGroup(_('Server IP address'),
                        new \Ease\Html\InputTextTag('serverip'), '',
                        $_SERVER['SERVER_ADDR'],
                        _('Address where live Icinga itself. Here recieve NSCA tests and from here allow NRPE access')));
        $this->addItem(new \Ease\TWB\FormGroup(_('nsca password'),
                        new \Ease\Html\InputTextTag('nscapassword'), '', '',
                        _('password used by NSCA ')));

        $this->addItem(new \Ease\TWB\SubmitButton(_('Save') . '&nbsp' . \Ease\TWB\Part::GlyphIcon('save'),
                        'success'));
    }

}
