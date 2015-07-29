<?php

/**
 * Volba předlohy sledovaných služeb
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class IEStemplateSelect extends EaseHtmlSelect
{

    function loadItems()
    {
        $tpls = array('' => _('zvol ze seznamu'));
        $stemplate = new IEStemplate;
        $templates = $stemplate->getColumnsFromMySQL(array($stemplate->getmyKeyColumn(), $stemplate->nameColumn));
        foreach ($templates as $template_id => $template_info) {
            $tpls[$template_info[$stemplate->myKeyColumn]] = $template_info[$stemplate->nameColumn];
        }
        return $tpls;
    }

}
