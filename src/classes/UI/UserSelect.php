<?php

namespace Icinga\Editor\UI;

/**
 * Select one of Users
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class UserSelect extends \Ease\Html\Select
{

    public function loadItems()
    {
        $user = new \Icinga\Editor\User();
        $ui   = ['null' => _('System user')];
        foreach ($user->getAllFromSQL(\Ease\Shared::user()->getMyTable(),
            ['id', 'login'], null, 'login', 'id') as $userInfo) {
            $ui[$userInfo['id']] = $userInfo['login'];
        }
        unset($ui[0]);
        return $ui;
    }

}
