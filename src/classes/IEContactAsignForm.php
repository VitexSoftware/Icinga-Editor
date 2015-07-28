<?php

/**
 * Formulář pro přiřazení kontaktu
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class IEContactAsignForm extends EaseTWBForm
{

    /**
     * Formulář pro výběr kontaktu
     * @param string $columnName
     */
    public function __construct($columnName = 'contact_id')
    {
        parent::__construct('ContactAsign', null, 'POST');
        $this->addItem(new IEContactSelect($columnName));
        $this->addItem(new EaseHtmlInputHiddenTag('action', 'contactAsign'));
    }

    public function finalize()
    {
        $this->addItem(new EaseTWSubmitButton(_('Přiřadit kontakt'), 'success'));
        parent::finalize();
    }

}
