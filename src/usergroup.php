<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - usergroup
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2017 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$usergroup_name = $oPage->getRequestValue('usergroup_name');
$usergroup_id   = $oPage->getRequestValue('usergroup_id', 'int');
$member_id      = $oPage->getRequestValue('member_id', 'int');

$userGroup = new Engine\UserGroup($usergroup_id);

switch ($oPage->getRequestValue('action')) {
    case 'addmember':
        if ($userGroup->addMember(null, $member_id)) {
            $userGroup->addStatusMessage(_('member wasp added to group'),
                'success');
        } else {
            $userGroup->addStatusMessage(_('member was not add to group'),
                'warning');
        }

        break;
    case 'delmember':
        if ($userGroup->delMember(null, $member_id)) {
            $userGroup->addStatusMessage(_('member was removed from group'),
                'success');
        } else {
            $userGroup->addStatusMessage(_('member was not removed from group'),
                'warning');
        }
        break;
    default :

        if ($usergroup_name) {
            $userGroup->setDataValue('usergroup_name', $usergroup_name);
            if ($userGroup->saveToSQL()) {
                $userGroup->addStatusMessage(_('group was saved'), 'success');
            } else {
                $userGroup->addStatusMessage(_('group was not saved'), 'warning');
            }
        }

        $delete = $oPage->getGetValue('delete', 'string');
        if ($delete == 'true') {
            $userGroup->delete();
            $oPage->redirect('usergroups.php');
            exit();
        }


        break;
}



$oPage->addItem(new UI\PageTop(_('User Group')));
$oPage->addPageColumns();


switch ($oPage->getRequestValue('action')) {
    case 'delete':
        $confirmator = $oPage->columnII->addItem(new \Ease\TWB\Panel(_('Are you sure ?')),
            'danger');
        $confirmator->addItem(new \Ease\TWB\LinkButton('?'.$userGroup->keyColumn.'='.$userGroup->getID(),
                _('No').' '.\Ease\TWB\Part::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new \Ease\TWB\LinkButton('?delete=true&'.$userGroup->keyColumn.'='.$userGroup->getID(),
                _('Yes').' '.\Ease\TWB\Part::glyphIcon('remove'), 'danger'));

        $oPage->columnI->addItem($userGroup->ownerLinkButton());


        break;
    default :
        $oPage->columnII->addItem(new \Icinga\Editor\UI\UserGroupForm($userGroup));
        if ($userGroup->getMyKey()) {
            $oPage->columnIII->addItem($userGroup->deleteButton());
        }
        $oPage->columnI->addItem(new \Ease\TWB\Panel(_('User Group'), 'info',
                _('All group members can wiew and edit configurations owned by other members.')));
}




$oPage->addItem(new UI\PageBottom());

$oPage->draw();
