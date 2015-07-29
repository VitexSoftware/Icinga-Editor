<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IEPreferencesForm
 *
 * @author vitex
 */
class IEPreferencesForm extends EaseTWBForm
{

    function __construct($formName, $formAction = null, $formMethod = 'post', $formContents = null, $tagProperties = null)
    {
        parent::__construct($formName, $formAction, $formMethod, $formContents, $tagProperties);

        $this->addItem(new EaseTWBFormGroup(_('IP adresa serveru'), new EaseHtmlInputTextTag('serverip'), '', $_SERVER['SERVER_ADDR'], _('Adresa na níž běží icinga. Sem se posílají NSCA testy a z této adresy je povoleno se dotazovat NRPE pluginů')));
        $this->addItem(new EaseTWBFormGroup(_('nsca heslo'), new EaseHtmlInputTextTag('nscapassword'), '', '', _('Heslo kterým je šifrována NSCA komunikace')));

        $this->addItem(new EaseTWSubmitButton(_('Uložit') . '&nbsp' . EaseTWBPart::GlyphIcon('save'), 'success'));
    }

}
