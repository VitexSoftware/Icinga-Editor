<?php

namespace Icinga\Editor\UI;

/**
 * Volba služeb patřičných k hostu
 *
 * @todo dodělat
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class UserSelect extends \Ease\Html\Select
{

    public function loadItems()
    {
        $user = new \Icinga\Editor\User();
        $ui   = ['null' => _('Systémový uživatel')];
        foreach ($user->getAllFromSQL(\Ease\Shared::user()->getMyTable(),
            ['id', 'login'], null, 'login', 'id') as $userInfo) {
            $ui[$userInfo['id']] = $userInfo['login'];
        }
        unset($ui[0]);
        return $ui;
    }

}
