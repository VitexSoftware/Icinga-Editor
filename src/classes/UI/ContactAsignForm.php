<?php

namespace Icinga\Editor\UI;

/**
 * Formulář pro přiřazení kontaktu
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class ContactAsignForm extends \Ease\TWB\Form
{

    /**
     * Formulář pro výběr kontaktu
     * @param string $columnName
     */
    public function __construct($columnName = 'contact_id')
    {
        parent::__construct('ContactAsign', null, 'POST');
        $this->addItem(new ContactSelect($columnName));
        $this->addItem(new \Ease\Html\InputHiddenTag('action', 'contactAsign'));
    }

    public function finalize()
    {
        $this->addItem(new \Ease\TWB\SubmitButton(_('Přiřadit kontakt'),
            'success'));
        parent::finalize();
    }

}
