<?php

namespace Icinga\Editor\UI;

/**
 * Hlavní menu
 *
 * @package    VitexSoftware
 * @author     Vitex <vitex@hippy.cz>
 */
class MainMenu extends \Ease\Html\DivTag
{

    /**
     * Create Main Menu
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTagID('MainMenu');
    }

    /**
     * Add "Apply Changes" Button into menu
     *
     * @param BootstrapMenu $nav
     */
    private function changesButton($nav)
    {
        $user = \Ease\Shared::user();
        if ($user->isAdmin()) {
            if ($user->getSettingValue('unsaved') == true) {
                $nav->addMenuItem(
                    new \Ease\TWB\LinkButton(
                    'regenall.php', _('Regenerate All'), 'warning'
                    ), 'right'
                );
            } else {
                $nav->addMenuItem(
                    new \Ease\TWB\LinkButton(
                    'regenall.php', _('Regenerate All'), 'default'
                    ), 'right'
                );
            }
        }


        if ($user->getSettingValue('unsaved') == true) {
            $nav->addMenuItem(
                new \Ease\Html\ATag(
                'apply.php', _('Apply changes'), ['class' => 'btn btn-success']
                ), 'right'
            );
        } else {
            $nav->addMenuItem(new \Ease\Html\ATag('apply.php',
                _('Apply Changes'), ['class' => 'btn btn-inverse']), 'right');
        }
    }

    /**
     * Add Groups/Hosts into menu
     *
     * @param BootstrapMenu $nav
     */
    private function groupsHostsMenu($nav)
    {

        \Ease\Shared::webPage()->addCss('.dropdown-menu { overflow-y: auto } ');
        \Ease\Shared::webPage()->addJavaScript("$('.dropdown-menu').css('max-height',$(window).height()-100);",
            null, true);

        $user            = \Ease\Shared::user();
        $host            = new \Icinga\Editor\Engine\Host();
        $hosts           = $host->getListing(null, null,
            ['icon_image', 'platform']);
        $hostsNotInGroup = [];
        $hnames          = [];
        foreach ($hosts as $hID => $hInfo) {
            $hnames[$hInfo['host_name']]          = & $hosts[$hID];
            $hostsNotInGroup[$hInfo['host_name']] = $hInfo;
        }
        $topItems                           = [
            'wizard-host.php' => \Ease\TWB\Part::GlyphIcon('forward').' '._('New Host wizard'),
        ];
        $topItems['wizard-active-host.php'] = \Ease\TWB\Part::GlyphIcon('star').' '._('New Active Host');

        $hostgroup                 = new \Icinga\Editor\Engine\Hostgroup();
        $topItems['hostgroup.php'] = \Ease\TWB\Part::GlyphIcon('plus').' '._('New hostgroup'); /* ,
          'exthostinfo.php' => _('Rozšířené informace hostů'),
          'hostdependency.php' => _('Závislosti hostů'),
          'hostescalation.php' => _('Eskalace hostů') */

        $pocHostgroup      = $hostgroup->getMyRecordsCount(null,
            $user->isAdmin());
        $hostGroupMenuItem = [];

        if ($pocHostgroup) {
            //$hostgroups = $hostgroup->dblink->queryToArray('SELECT ' . $hostgroup->getKeyColumn() . ', hostgroup_name, DatSave FROM ' . $hostgroup->myTable . ' WHERE user_id=' . $user->getUserID(), 'hostgroup_id');
            $hostgroups = $hostgroup->getListing(null, null, ['members']);

            foreach ($hostgroups as $cID => $hgInfo) {
                $hostGroupMenuItem['hostgroup.php?hostgroup_id='.$hgInfo['hostgroup_id']]
                    = \Ease\TWB\Part::GlyphIcon('cloud').' '.$hgInfo['hostgroup_name'];
                if (is_array($hgInfo['members'])) {
                    foreach ($hgInfo['members'] as $hgMember) {
                        if ($hgMember == '*') {
                            $image = null;
                        } else {
                            $hInfo = & $hnames[$hgMember];
                            $image = $hInfo['icon_image'];
                            unset($hostsNotInGroup[$hgMember]);
                        }
                        if (!$image) {
                            $image = 'unknown.gif';
                        }

                        if (isset($hInfo) && !is_null($hInfo)) {
                            $hostGroupMenuItem['host.php?host_id='.$hInfo['host_id']]
                                = '&nbsp;'.new HostIcon($hInfo).' '.
                                $hInfo['host_name'].' '.
                                new PlatformIcon($hInfo['platform']);
                        }
                    }
                }
            }
            $topItems['hostgroups.php'] = \Ease\TWB\Part::GlyphIcon('list-alt').' '._('Hostgroup Overview');
        } else {
            if (count($hostGroupMenuItem)) {
                $hostGroupMenuItem[] = '';
            }
        }

        if (count($hostsNotInGroup)) {

            foreach ($hostsNotInGroup as $menuHost) {
                $hostGroupMenuItem['host.php?host_id='.$menuHost['host_id']] = '&nbsp;'.new HostIcon($menuHost).' '.
                    $menuHost['host_name'].' '.
                    new PlatformIcon($menuHost['platform']);
            }
        }


        $topItems['hosts.php'] = \Ease\TWB\Part::GlyphIcon('list').' '._('Detail host overview');

        $topItems['map.php'] = \Ease\TWB\Part::GlyphIcon('globe').' '._('Topology');

        $nav->addDropDownMenu(_('Hosts'),
            array_merge($topItems, ['' => ''], $hostGroupMenuItem));
    }

    /**
     * Vložení menu
     */
    public function afterAdd()
    {
        $nav    = $this->addItem(new BootstrapMenu());
        $user   = \Ease\Shared::user();
        $userID = $user->getUserID();
        if ($userID) { //Authenticated user
            $nav->addMenuItem(new NavBarSearchBox('search', 'search.php'));


            if ($user->getSettingValue('admin')) {

                $users = $user->getColumnsFromSQL(['id', 'login'],
                    ['id' => '!0'], 'login', $user->getKeyColumn());

                $userList = [];
                if ($users) {
                    foreach ($users as $uID => $uInfo) {
                        $userList['userinfo.php?user_id='.$uInfo['id']] = \Ease\TWB\Part::GlyphIcon('user').'&nbsp;'.$uInfo['login'];
                    }
                    if (count($userList)) {
                        $userList[] = '';
                    }
                }

//                $usergroups = $this->dblink->queryToArray('SELECT * FROM user_groups' . 'usergroup_id');


                $nav->addDropDownMenu(_('Users'),
                    array_merge($userList,
                        [
                    'createaccount.php' => \Ease\TWB\Part::GlyphIcon('plus').' '._('New User'),
                    'users.php' => \Ease\TWB\Part::GlyphIcon('list').'&nbsp;'._('Users Overview'),
                    'usergroup.php' => \Ease\TWB\Part::GlyphIcon('plus').' '._('New Usergroup'),
                    'usergroups.php' => \Ease\TWB\Part::GlyphIcon('list').'&nbsp;'._('Usergroup overview'),
                    ])
                );
            }

            $this->changesButton($nav);




            $this->groupsHostsMenu($nav);

//            $nav->addDropDownMenu(_('Hosti'), $hostGroupHostsMenuItem);
            if (\Ease\Shared::user()->getSettingValue('admin')) {
                $nav->addDropDownMenu(_('Services'),
                    [
                    'wizard-service.php' => \Ease\TWB\Part::GlyphIcon('forward').' '._('New Service Wizard'),
                    'service.php' => \Ease\TWB\Part::GlyphIcon('plus').' '._('New service'),
                    'services.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Services overview'),
                    'servicegroup.php' => \Ease\TWB\Part::GlyphIcon('plus').' '._('New servicegroup'),
                    'servicegroups.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Servicegroups overview'), /*
                      'servicedependency.php' => _('Závislosti služeb'),
                      'extserviceinfo.php' => _('Rozšířené informace služeb'),
                      'serviceescalation.php' => _('Eskalace služeb') */
                    '' => '',
                    'stemplate.php?action=new' => \Ease\TWB\Part::GlyphIcon('plus').' '._('New watched services set'),
                    'stemplates.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Watched services sets overview')
                    ]
                );
            } else {
                $service  = new \Icinga\Editor\Engine\Service();
                $services = $service->getListing(null, null,
                    ['icon_image', 'platform']);

                if (count($services)) {
                    $services_menu = ['services.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Services overview')];
                    foreach ($services as $serviceID => $serviceInfo) {
                        $services_menu['servicetweak.php?service_id='.$serviceID]
                            = $serviceInfo[$service->nameColumn];
                    }
                    $nav->addDropDownMenu(_('Služby'), $services_menu);
                }
            }

            $contact  = new \Icinga\Editor\Engine\Contact();
            $contacts = $contact->getListing(null, null, ['parent_id']);
            foreach ($contacts as $contactID => $contactInfo) { //Vyfiltrovat pouze primární kontakty
                if ($contactInfo['parent_id']) {
                    unset($contacts[$contactID]);
                }
            }

            if (count($contacts)) {
                $contacts_menu = ['contacts.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Contacts overview')];
                foreach ($contacts as $contactID => $contactInfo) {
                    $contacts_menu['contacttweak.php?contact_id='.$contactID] = $contactInfo[$contact->nameColumn];
                }
                $contacts_menu[] = '';
            } else {
                $contacts_menu = [];
            }

            $nav->addDropDownMenu(_('Contacts'),
                array_merge($contacts_menu,
                    [
                'contacts.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Contacts Overview'),
                'newcontact.php' => \Ease\TWB\Part::GlyphIcon('plus').' '._('New Contact'),
                'contactgroups.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Contactgroups overview'),
                'contactgroup.php' => \Ease\TWB\Part::GlyphIcon('edit').' '._('New contactgroup')]
            ));

            if ($user->getSettingValue('admin')) {
                $nav->addDropDownMenu(_('Command'),
                    [
                    'command.php' => \Ease\TWB\Part::GlyphIcon('edit').' '._('New command'),
                    'commands.php' => \Ease\TWB\Part::GlyphIcon('list-alt').' '._('Commands overview'),
                    'importcommand.php' => \Ease\TWB\Part::GlyphIcon('import').' '._('Import'),
                    '',
                    'script.php' => \Ease\TWB\Part::GlyphIcon('edit').' '._('New script'),
                    'scripts.php' => \Ease\TWB\Part::GlyphIcon('list-alt').' '._('Scripts Overview')]
                );
                $nav->addDropDownMenu(_('Advanced'),
                    [
                    'timeperiods.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Timeperioods overview'),
                    'timeperiod.php' => \Ease\TWB\Part::GlyphIcon('plus').' '._('New Timeperiod'),
                    'preferences.php' => \Ease\TWB\Part::GlyphIcon('wrench').' '._('Icinga Settings'),
                    'regenall.php' => \Ease\TWB\Part::GlyphIcon('ok').' '._('Regenerate All Config files'),
                    'reset.php' => \Ease\TWB\Part::GlyphIcon('cog').' '._('Objects reset'),
                    'dbrecreate.php' => \Ease\TWB\Part::GlyphIcon('wrench').' '._('Reinicialise database'),
                    'fixer.php' => \Ease\TWB\Part::GlyphIcon('ok-circle').' '._('Database fix'),
                    'import.php' => \Ease\TWB\Part::GlyphIcon('import').' '._('Configuration import')
                    /* 'module.php' => _('definice modulů') */                    ]
                );
            }
            $results = [
                'nagstamon.php' => \Ease\TWB\Part::GlyphIcon('info').' '._('PC Lin/Win/Mac'),
                'anag.php' => \Ease\TWB\Part::GlyphIcon('info').' '._('Android'),
                'wpnag.php' => \Ease\TWB\Part::GlyphIcon('Info').' '._('Win Phone')];

            if (file_exists('/etc/apache2/conf-enabled/icinga-web.conf')) {
                $results['/icinga-web/'] = \Ease\TWB\Part::GlyphIcon('Info').' '._('Icinga Web');
            }

            if (file_exists('/etc/apache2/conf-enabled/icinga.conf')) {
                $results['/icinga/'] = \Ease\TWB\Part::GlyphIcon('Info').' '._('Icinga Classic');
            }

            $nav->addDropDownMenu(_('Tests results'), $results);
        }
    }

    /**
     * Přidá do stránky javascript pro skrývání oblasti stavových zpráv
     */
    public function finalize()
    {
        \Ease\TWB\Part::jQueryze($this);
        $this->includeJavaScript('js/slideupmessages.js');
    }

}
