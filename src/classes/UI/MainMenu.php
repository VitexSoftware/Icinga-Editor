<?php

namespace Icinga\Editor\UI;

/**
 * Hlavní menu
 *
 * @package    VitexSoftware
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 */
class MainMenu extends \Ease\Html\Div
{

    /**
     * Vytvoří hlavní menu
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTagID('MainMenu');
    }

    /**
     * Add "Apply Changes" Button into menu
     *
     * @param IEBootstrapMenu $nav
     */
    private function changesButton($nav)
    {
        if (\Ease\Shared::user()->getSettingValue('unsaved') == true) {
            $nav->addMenuItem(
                new \Ease\Html\ATag(
                'apply.php', _('Uplatnit změny'), ['class' => 'btn btn-success']
                ), 'right'
            );
        } else {
            $nav->addMenuItem(new \Ease\Html\ATag('apply.php',
                _('Uplatnit změny'), ['class' => 'btn btn-inverse']), 'right');
        }
    }

    /**
     * Add Groups/Hosts into menu
     *
     * @param IEBootstrapMenu $nav
     */
    private function groupsHostsMenu($nav)
    {

        \Ease\Shared::webPage()->addCss('.dropdown-menu { overflow-y: auto } ');
        \Ease\Shared::webPage()->addJavaScript("$('.dropdown-menu').css('max-height',$(window).height()-100);",
            null, true);

        $user            = \Ease\Shared::user();
        $host            = new \Icinga\Editor\Engine\IEHost();
        $hosts           = $host->getListing(null, null,
            ['icon_image', 'platform']);
        $hostsNotInGroup = [];
        $hnames          = [];
        foreach ($hosts as $hID => $hInfo) {
            $hnames[$hInfo['host_name']]          = & $hosts[$hID];
            $hostsNotInGroup[$hInfo['host_name']] = $hInfo;
        }
        $topItems                           = [
            'wizard-host.php' => \Ease\TWB\Part::GlyphIcon('forward').' '._('Průvodce založením hostu'),
        ];
        $topItems['wizard-active-host.php'] = \Ease\TWB\Part::GlyphIcon('star').' '._('Nový aktivní Host');

        $hostgroup                 = new \Icinga\Editor\Engine\IEHostgroup();
        $topItems['hostgroup.php'] = \Ease\TWB\Part::GlyphIcon('plus').' '._('Nová skupina hostů'); /* ,
          'exthostinfo.php' => _('Rozšířené informace hostů'),
          'hostdependency.php' => _('Závislosti hostů'),
          'hostescalation.php' => _('Eskalace hostů') */

        $pocHostgroup      = $hostgroup->getMyRecordsCount();
        $hostGroupMenuItem = [];

        if ($pocHostgroup) {
            //$hostgroups = $hostgroup->dblink->queryToArray('SELECT ' . $hostgroup->getmyKeyColumn() . ', hostgroup_name, DatSave FROM ' . $hostgroup->myTable . ' WHERE user_id=' . $user->getUserID(), 'hostgroup_id');
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
            $topItems['hostgroups.php'] = \Ease\TWB\Part::GlyphIcon('list-alt').' '._('Přehled skupin hostů');
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


        $topItems['hosts.php'] = \Ease\TWB\Part::GlyphIcon('list').' '._('Detailní přehled hostů');

        $topItems['map.php'] = \Ease\TWB\Part::GlyphIcon('globe').' '._('Topologie');

        $nav->addDropDownMenu(_('Hosti'),
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
                    ['id' => '!0'], 'login', $user->getmyKeyColumn());

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


                $nav->addDropDownMenu(_('Uživatelé'),
                    array_merge($userList,
                        [
                    'createaccount.php' => \Ease\TWB\Part::GlyphIcon('plus').' '._('Nový uživatel'),
                    'users.php' => \Ease\TWB\Part::GlyphIcon('list').'&nbsp;'._('Přehled uživatelů'),
                    'usergroup.php' => \Ease\TWB\Part::GlyphIcon('plus').' '._('Nová skupina uživatelů'),
                    'usergroups.php' => \Ease\TWB\Part::GlyphIcon('list').'&nbsp;'._('Přehled skupin uživatelů'),
                    ])
                );
            }

            $this->changesButton($nav);




            $this->groupsHostsMenu($nav);

//            $nav->addDropDownMenu(_('Hosti'), $hostGroupHostsMenuItem);
            if (\Ease\Shared::user()->getSettingValue('admin')) {
                $nav->addDropDownMenu(_('Služby'),
                    [
                    'wizard-service.php' => \Ease\TWB\Part::GlyphIcon('forward').' '._('Průvodce založením služby'),
                    'service.php' => \Ease\TWB\Part::GlyphIcon('plus').' '._('Nová služba'),
                    'services.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Přehled služeb'),
                    'servicegroup.php' => \Ease\TWB\Part::GlyphIcon('plus').' '._('Nová skupina služeb'),
                    'servicegroups.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Přehled skupin služeb'), /*
                      'servicedependency.php' => _('Závislosti služeb'),
                      'extserviceinfo.php' => _('Rozšířené informace služeb'),
                      'serviceescalation.php' => _('Eskalace služeb') */
                    '' => '',
                    'stemplate.php?action=new' => \Ease\TWB\Part::GlyphIcon('plus').' '._('Nová předloha sledované služby'),
                    'stemplates.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Přehled předloh sled. sl.')
                    ]
                );
            } else {
                $service  = new \Icinga\Editor\Engine\IEService();
                $services = $service->getListing(null, null,
                    ['icon_image', 'platform']);

                if (count($services)) {
                    $services_menu = ['services.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Přehled služeb')];
                    foreach ($services as $serviceID => $serviceInfo) {
                        $services_menu['servicetweak.php?service_id='.$serviceID]
                            = $serviceInfo[$service->nameColumn];
                    }
                    $nav->addDropDownMenu(_('Služby'), $services_menu);
                }
            }

            $contact  = new \Icinga\Editor\Engine\IEContact();
            $contacts = $contact->getListing(null, null, ['parent_id']);
            foreach ($contacts as $contactID => $contactInfo) { //Vyfiltrovat pouze primární kontakty
                if ($contactInfo['parent_id']) {
                    unset($contacts[$contactID]);
                }
            }

            if (count($contacts)) {
                $contacts_menu = ['contacts.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Přehled Kontaktů')];
                foreach ($contacts as $contactID => $contactInfo) {
                    $contacts_menu['contacttweak.php?contact_id='.$contactID] = $contactInfo[$contact->nameColumn];
                }
                $contacts_menu[] = '';
            } else {
                $contacts_menu = [];
            }

            $nav->addDropDownMenu(_('Kontakty'),
                array_merge($contacts_menu,
                    [
                'contacts.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Přehled kontaktů'),
                'newcontact.php' => \Ease\TWB\Part::GlyphIcon('plus').' '._('Nový kontakt'),
                'contactgroups.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Přehled skupin kontaktů'),
                'contactgroup.php' => \Ease\TWB\Part::GlyphIcon('edit').' '._('Nová skupina kontaktů')]
            ));

            if ($user->getSettingValue('admin')) {
                $nav->addDropDownMenu(_('Příkaz'),
                    [
                    'command.php' => \Ease\TWB\Part::GlyphIcon('edit').' '._('Nový příkaz'),
                    'commands.php' => \Ease\TWB\Part::GlyphIcon('list-alt').' '._('Přehled příkazů'),
                    'importcommand.php' => \Ease\TWB\Part::GlyphIcon('import').' '._('Importovat'),
                    '',
                    'script.php' => \Ease\TWB\Part::GlyphIcon('edit').' '._('Nový skript'),
                    'scripts.php' => \Ease\TWB\Part::GlyphIcon('list-alt').' '._('Přehled skriptů')]
                );
                $nav->addDropDownMenu(_('Rozšířené'),
                    [
                    'timeperiods.php' => \Ease\TWB\Part::GlyphIcon('list').' '._('Přehled časových period'),
                    'timeperiod.php' => \Ease\TWB\Part::GlyphIcon('plus').' '._('Nová časová perioda'),
                    'preferences.php' => \Ease\TWB\Part::GlyphIcon('wrench').' '._('Nastavení icingy'),
                    'regenall.php' => \Ease\TWB\Part::GlyphIcon('ok').' '._('Přegenerovat všechny konfiguráky'),
                    'reset.php' => \Ease\TWB\Part::GlyphIcon('cog').' '._('Reset Objektů'),
                    'dbrecreate.php' => \Ease\TWB\Part::GlyphIcon('wrench').' '._('Reinicializovat databázi'),
                    'fixer.php' => \Ease\TWB\Part::GlyphIcon('ok-circle').' '._('Opravit databázi'),
                    'import.php' => \Ease\TWB\Part::GlyphIcon('import').' '._('Importovat')
                    /* 'module.php' => _('definice modulů') */                    ]
                );
            }
            $results = [
                'nagstamon.php' => \Ease\TWB\Part::GlyphIcon('info').' '._('PC Lin/Win/Mac'),
                'anag.php' => \Ease\TWB\Part::GlyphIcon('info').' '._('Android'),
                'wpnag.php' => \Ease\TWB\Part::GlyphIcon('Info').' '._('Win Phone')];

            if (file_exists('/etc/apache2/conf-enabled/icinga-web.conf')) {
                $results['/icinga-web/'] = \Ease\TWB\Part::GlyphIcon('Info').' '._('Web');
            }

            $nav->addDropDownMenu(_('Výsledky testů'), $results);
        }
    }

    /**
     * Přidá do stránky javascript pro skrývání oblasti stavových zpráv
     */
    public function finalize()
    {
        \Ease\JQuery\Part::jQueryze($this);
        $this->includeJavaScript('js/slideupmessages.js');
    }
}