<?php

/**
 * Volba služeb patřičných k hostu
 *
 * @todo dodělat
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IEUserSelect extends EaseHtmlSelect
{

    public function loadItems()
    {
        $user = new IEUser();
        $ui = array('0' => _('Systémový uživatel'));
        foreach ($user->getAllFromMySQL(EaseShared::user()->getMyTable(), array('id', 'login'), null, 'login', 'id') as $UserInfo) {
            $ui[$UserInfo['id']] = $UserInfo['login'];
        }

        return $ui;
    }

}
