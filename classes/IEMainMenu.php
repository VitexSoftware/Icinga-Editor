<?php

require_once 'IENavBarSearchBox.php';

/**
 * Hlavní menu
 *
 * @package    VitexSoftware
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 */
class IEMainMenu extends EaseHtmlDivTag
{

    /**
     * Vytvoří hlavní menu
     */
    public function __construct()
    {
        parent::__construct('MainMenu');
    }

    /**
     * Add "Apply Changes" Button into menu
     *
     * @param IEBootstrapMenu $nav
     */
    private function changesButton($nav)
    {
        if (EaseShared::user()->getSettingValue('unsaved') == true) {
            $nav->addMenuItem(
                new EaseHtmlATag(
                'apply.php', _('Uplatnit změny'), array('class' => 'btn btn-success')
                ), 'right'
            );
        } else {
            $nav->addMenuItem(new EaseHtmlATag('apply.php', _('Uplatnit změny'), array('class' => 'btn btn-inverse')), 'right');
        }
    }

    /**
     * Add Groups/Hosts into menu
     *
     * @param IEBootstrapMenu $nav
     */
    private function groupsHostsMenu($nav)
    {

        EaseShared::webPage()->addCss('.dropdown-menu { overflow-y: auto } ');
        EaseShared::webPage()->addJavaScript("$('.dropdown-menu').css('max-height',$(window).height()-100);", null, true);

        $user = EaseShared::user();
        $host = new IEHost();
        $hosts = $host->getListing(null, null, array('icon_image', 'platform'));
        $hostsNotInGroup = array();
        $hnames = array();
        foreach ($hosts as $hID => $hInfo) {
            $hnames[$hInfo['host_name']] = & $hosts[$hID];
            $hostsNotInGroup[$hInfo['host_name']] = $hInfo;
        }
        $topItems = array(
          'wizard.php' => EaseTWBPart::GlyphIcon('forward') . ' ' . _('Průvodce založením hostu'),
        );
        $topItems['host.php'] = EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nový Host');

        $hostgroup = new IEHostgroup();
        $topItems['hostgroup.php'] = EaseTWBPart::GlyphIcon('plus') . ' ' . _('Nová skupina hostů'); /* ,
          'exthostinfo.php' => _('Rozšířené informace hostů'),
          'hostdependency.php' => _('Závislosti hostů'),
          'hostescalation.php' => _('Eskalace hostů') */

        $pocHostgroup = $hostgroup->getMyRecordsCount();
        $hostGroupMenuItem = array();

        if ($pocHostgroup) {
            //$hostgroups = $hostgroup->myDbLink->queryToArray('SELECT ' . $hostgroup->getmyKeyColumn() . ', hostgroup_name, DatSave FROM ' . $hostgroup->myTable . ' WHERE user_id=' . $user->getUserID(), 'hostgroup_id');
            $hostgroups = $hostgroup->getListing(null, null, array('members'));

            foreach ($hostgroups as $cID => $hgInfo) {
                $hostGroupMenuItem['hostgroup.php?hostgroup_id=' . $hgInfo['hostgroup_id']] = EaseTWBPart::GlyphIcon('cloud') . ' ' . $hgInfo['hostgroup_name'];
                if (strlen($hgInfo['members'])) {
                    foreach (unserialize($hgInfo['members']) as $hgMember) {
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

                        if (!is_null($hInfo)) {
                            $hostGroupMenuItem['host.php?host_id=' . $hInfo['host_id']] = '&nbsp;' . IEHostOverview::icon($hInfo) . ' ' .
                                $hInfo['host_name'] . ' ' .
                                IEHostOverview::platformIcon($hInfo['platform']);
                        }
                    }
                }
            }
            $topItems['hostgroups.php'] = EaseTWBPart::GlyphIcon('list-alt') . ' ' . _('Přehled skupin hostů');
        } else {
            if (count($hostGroupMenuItem)) {
                $hostGroupMenuItem[] = '';
            }
        }

        if (count($hostsNotInGroup)) {

            foreach ($hostsNotInGroup as $menuHost) {
                $hostGroupMenuItem['host.php?host_id=' . $menuHost['host_id']] = '&nbsp;' . IEHostOverview::icon($menuHost) . ' ' .
                    $menuHost['host_name'] . ' ' .
                    IEHostOverview::platformIcon($menuHost['platform']);
            }
        }




        $topItems['hosts.php'] = EaseTWBPart::GlyphIcon('list') . ' ' . _('Detailní přehled hostů');

        $nav->addDropDownMenu(_('Hosti'), array_merge($topItems, array('' => ''), $hostGroupMenuItem));
    }

    /**
     * Vložení menu
     */
    public function afterAdd()
    {
        $nav = $this->addItem(new IEBootstrapMenu());
        $user = EaseShared::user();
        $userID = $user->getUserID();
        if ($userID) { //Authenticated user
            $nav->addMenuItem(new IENavBarSearchBox('search', 'search.php'));


            if ($user->getSettingValue('admin')) {

                $users = $user->getColumnsFromMySQL(array('id', 'login'), array('id' => '!0'), 'login', $user->getmyKeyColumn());

                $userList = array();
                if ($users) {
                    foreach ($users as $uID => $uInfo) {
                        $userList['userinfo.php?user_id=' . $uInfo['id']] = EaseTWBPart::GlyphIcon('user') . '&nbsp;' . $uInfo['login'];
                    }
                    if (count($userList)) {
                        $userList[] = '';
                    }
                }

                $nav->addDropDownMenu(_('Uživatelé'), array_merge($userList, array(
                  'users.php' => EaseTWBPart::GlyphIcon('list') . '&nbsp;' . _('Přehled uživatelů'),
                  'createaccount.php' => EaseTWBPart::GlyphIcon('plus') . ' ' . _('Nový uživatel'),
                    ))
                );
            }

            $this->changesButton($nav);




            $this->groupsHostsMenu($nav);

//            $nav->addDropDownMenu(_('Hosti'), $hostGroupHostsMenuItem);
            if (EaseShared::user()->getSettingValue('admin')) {
                $nav->addDropDownMenu(_('Služby'), array(
                  'service.php' => EaseTWBPart::GlyphIcon('plus') . ' ' . _('Nová služba'),
                  'services.php' => EaseTWBPart::GlyphIcon('list') . ' ' . _('Přehled služeb'),
                  'servicegroup.php' => EaseTWBPart::GlyphIcon('plus') . ' ' . _('Nová skupina služeb'),
                  'servicegroups.php' => EaseTWBPart::GlyphIcon('list') . ' ' . _('Přehled skupin služeb'), /*
                      'servicedependency.php' => _('Závislosti služeb'),
                      'extserviceinfo.php' => _('Rozšířené informace služeb'),
                      'serviceescalation.php' => _('Eskalace služeb') */)
                );
            } else {
                $service = new IEService();
                $services = $service->getListing(null, null, array('icon_image', 'platform'));

                if (count($services)) {
                    $services_menu = array('services.php' => EaseTWBPart::GlyphIcon('list') . ' ' . _('Přehled služeb'));
                    foreach ($services as $serviceID => $serviceInfo) {
                        $services_menu['servicetweak.php?service_id=' . $serviceID] = $serviceInfo[$service->nameColumn];
                    }
                    $nav->addDropDownMenu(_('Služby'), $services_menu);
                }
            }

            $contact = new IEContact();
            $contacts = $contact->getListing(null, null, array('parent_id'));
            foreach ($contacts as $contactID => $contactInfo) { //Vyfiltrovat pouze primární kontakty
                if ($contactInfo['parent_id']) {
                    unset($contacts[$contactID]);
                }
            }

            if (count($contacts)) {
                $contacts_menu = array('contacts.php' => EaseTWBPart::GlyphIcon('list') . ' ' . _('Přehled Kontaktů'));
                foreach ($contacts as $contactID => $contactInfo) {
                    $contacts_menu['contacttweak.php?contact_id=' . $contactID] = $contactInfo[$contact->nameColumn];
                }
                $contacts_menu[] = '';
            } else {
                $contacts_menu = array();
            }

            $nav->addDropDownMenu(_('Kontakty'), array_merge($contacts_menu, array(
              'contacts.php' => EaseTWBPart::GlyphIcon('list') . ' ' . _('Přehled kontaktů'),
              'newcontact.php' => EaseTWBPart::GlyphIcon('plus') . ' ' . _('Nový kontakt'),
              'contactgroups.php' => EaseTWBPart::GlyphIcon('list') . ' ' . _('Přehled skupin kontaktů'),
              'contactgroup.php' => EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nová skupina kontaktů'))
            ));

            if ($user->getSettingValue('admin')) {
                $nav->addDropDownMenu(_('Příkaz'), array(
                  'commands.php' => EaseTWBPart::GlyphIcon('list-alt') . ' ' . _('Přehled příkazů'),
                  'command.php' => EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nový příkaz'),
                  'importcommand.php' => EaseTWBPart::GlyphIcon('download') . ' ' . _('Importovat'))
                );
                $nav->addDropDownMenu(_('Rozšířené'), array(
                  'timeperiods.php' => EaseTWBPart::GlyphIcon('list') . ' ' . _('Přehled časových period'),
                  'timeperiod.php' => EaseTWBPart::GlyphIcon('plus') . ' ' . _('Nová časová perioda'),
                  'preferences.php' => EaseTWBPart::GlyphIcon('wrench') . ' ' . _('Nastavení icingy'),
                  'regenall.php' => EaseTWBPart::GlyphIcon('ok') . ' ' . _('Přegenerovat všechny konfiguráky'),
                  'dbrecreate.php' => EaseTWBPart::GlyphIcon('wrench') . ' ' . _('Reinicializovat databázi'),
                  'import.php' => EaseTWBPart::GlyphIcon('download') . ' ' . _('Importovat')
                    /* 'module.php' => _('definice modulů') */                    )
                );
            }
            $results = array(
              'nagstamon.php' => EaseTWBPart::GlyphIcon('info') . ' ' . _('PC Lin/Win/Mac'),
              'anag.php' => EaseTWBPart::GlyphIcon('info') . ' ' . _('Android'),
              'wpnag.php' => EaseTWBPart::GlyphIcon('Info') . ' ' . _('Win Phone'));

            if (file_exists('/etc/apache2/conf-enabled/icinga-web.conf')) {
                $results['/icinga-web/'] = EaseTWBPart::GlyphIcon('Info') . ' ' . _('Web');
            }

            $nav->addDropDownMenu(_('Výsledky testů'), $results);
        }
    }

    /**
     * Přidá do stránky javascript pro skrývání oblasti stavových zpráv
     */
    public function finalize()
    {
        EaseJQueryPart::jQueryze($this);
        $this->addJavaScript('$("#StatusMessages").click(function () { $("#StatusMessages").fadeTo("slow",0.25).slideUp("slow"); });', 3, true);
    }

}
