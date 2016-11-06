<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Icinga\Editor\UI;

/**
 * Description of FileEditor
 *
 * @author vitex
 */
class FileEditor extends \Ease\TWB\Form
{
    public function __construct($file, $line = null)
    {
        parent::__construct('cfgfile', '', 'POST',
            new CfgTextarea('cfg', file_get_contents($file),
            ['data-lineno' => $line]));
        $this->addItem(new \Ease\Html\InputHiddenTag('file', $file));
        $this->addItem(new \Ease\TWB\SubmitButton(_('Save'), 'success'));
    }
}
