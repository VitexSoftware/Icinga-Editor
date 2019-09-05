<?php

namespace Icinga\Editor\UI;

/**
 * Volba předlohy sledovaných služeb
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class StemplateSelect extends \Ease\Html\Select
{

    function loadItems()
    {
        $tpls      = ['' => _('choose preset …')];
        $stemplate = new \Icinga\Editor\Stemplate();
        $templates = $stemplate->getColumnsFromSQL([$stemplate->getKeyColumn(),
            $stemplate->nameColumn]);
        foreach ($templates as $template_id => $template_info) {
            $tpls[$template_info[$stemplate->keyColumn]] = $template_info[$stemplate->nameColumn];
        }
        return $tpls;
    }

}
