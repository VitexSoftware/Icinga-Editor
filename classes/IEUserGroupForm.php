<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'classes/IEUserGroup.php';

/**
 * Description of IEUserGroupForm
 *
 * @author vitex
 */
class IEUserGroupForm extends EaseTWBForm
{

    /**
     * Objekt skupiny uživatelů
     * @var IEUserGroup
     */
    public $userGroup = null;

    /**
     * Formulář skupiny uživatelů
     * @param IEUserGroup $userGroup
     */
    function __construct($userGroup)
    {
        $this->userGroup = $userGroup;
        parent::__construct('usergroup', $userGroup->keyword . '.php', 'POST');
    }

    function afterAdd()
    {
        $group_name = $this->userGroup->getDataValue('usergroup_name');
        $this->addItem(new EaseTWBFormGroup(_('Jméno'), new EaseHtmlInputTextTag('usergroup_name', $group_name), $group_name, _('Název skupiny'), _('Adminové')));
        $this->addItem($this->userGroup->memberSelector());
        $usergroup_id = $this->userGroup->getMyKey();
        if ($usergroup_id) {
            $this->addItem(new EaseHtmlInputHiddenTag($this->userGroup->getMyKeyColumn(), $usergroup_id));
        }
        if ($usergroup_id) {
            $this->addItem(new EaseTWSubmitButton(_('Uložit') . '&nbsp' . EaseTWBPart::GlyphIcon('save'), 'success'));
        } else {
            $this->addItem(new EaseTWSubmitButton(_('Založit') . '&nbsp' . EaseTWBPart::GlyphIcon('forward'), 'success'));
        }
    }

}
