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
        $ui   = ['0' => _('Systémový uživatel')];
        foreach ($user->getAllFromSQL(\Ease\Shared::user()->getMyTable(),
            ['id', 'login'], null, 'login', 'id') as $UserInfo) {
            $ui[$UserInfo['id']] = $UserInfo['login'];
        }

        return $ui;
    }
}