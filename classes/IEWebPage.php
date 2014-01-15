<?php

/**
 * Třídy pro vykreslení stránky
 *
 * @package   VitexSoftware
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2011 Vitex@hippy.cz (G)
 */
require_once 'Ease/EaseWebPage.php';
require_once 'Ease/EaseHtmlForm.php';
require_once 'Ease/EaseJQueryWidgets.php';
require_once 'Ease/EaseTWBootstrap.php';
require_once 'IEHost.php';
require_once 'IEHostOverview.php';

class IEWebPage extends EaseTWBWebPage
{

    /**
     * Skin JQuery UI stránky
     * @var string
     */
    public $jQueryUISkin = 'eggplant';

    /**
     * Hlavní blok stránky
     * @var EaseHtmlDivTag
     */
    public $container = NULL;

    /**
     * První sloupec
     * @var EaseHtmlDivTag
     */
    public $columnI = NULL;

    /**
     * Druhý sloupec
     * @var EaseHtmlDivTag
     */
    public $columnII = NULL;

    /**
     * Třetí sloupec
     * @var EaseHtmlDivTag
     */
    public $columnIII = NULL;

    /**
     * Základní objekt stránky
     *
     * @param VSUser $userObject
     */
    public function __construct($pageTitle = null, &$userObject = null)
    {
        if (is_null($userObject)) {
            $userObject = EaseShared::user();
        }
        $this->jQueryUISkin = $userObject->getSettingValue('Skin');
        parent::__construct($pageTitle, $userObject);
        $this->IncludeCss('css/bootstrap.css');
        $this->IncludeCss('css/default.css');
        $this->head->addItem('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
        $this->addCss('body {
                padding-top: 60px;
                padding-bottom: 40px;
            }');
        $this->head->addItem('<link rel="apple-touch-icon-precomposed" sizes="144x144" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-144-precomposed.png">');
        $this->head->addItem('<link rel="apple-touch-icon-precomposed" sizes="114x114" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-114-precomposed.png">');
        $this->head->addItem('<link rel="apple-touch-icon-precomposed" sizes="72x72" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-72-precomposed.png">');
        $this->head->addItem('<link rel="apple-touch-icon-precomposed" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-57-precomposed.png">');

        $this->container = $this->addItem(new EaseHtmlDivTag(null, null, array('class' => 'container')));

        $this->heroUnit = $this->container->addItem(new EaseHtmlDivTag('heroUnit', null, array('class' => 'jumbotron')));

        $row = $this->container->addItem(new EaseHtmlDivTag(null, null, array('class' => 'row')));

        $this->columnI = $row->addItem(new EaseHtmlDivTag(null, null, array('class' => 'col-md-4')));
        $this->columnII = $row->addItem(new EaseHtmlDivTag(null, null, array('class' => 'col-md-4')));
        $this->columnIII = $row->addItem(new EaseHtmlDivTag(null, null, array('class' => 'col-md-4')));
    }

    /**
     * Pouze pro admina
     *
     * @param string $loginPage
     */
    public function onlyForAdmin($loginPage = 'login.php')
    {
        if (!$this->User->getSettingValue('admin')) {
            EaseShared::user()->addStatusMessage(_('Nejprve se prosím přihlašte jako admin'), 'warning');
            $this->redirect($loginPage);
            exit;
        }
    }

}

/**
 * Vršek stránky
 *
 * @package    VitexSoftware
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 */
class IEPageTop extends EaseHtmlDivTag
{

    /**
     * Titulek stránky
     * @var type
     */
    public $pageTitle = 'Page Heading';

    /**
     * Nastavuje titulek
     *
     * @param string $pageTitle
     */
    public function __construct($pageTitle = null)
    {
        parent::__construct('header');
        if (!is_null($pageTitle)) {
            EaseShared::webPage()->setPageTitle($pageTitle);
        }
    }

    /**
     * Vloží vršek stránky a hlavní menu
     */
    public function finalize()
    {
        $this->SetupWebPage();
        $this->addItem(new IEMainMenu());
    }

}

class IEBootstrapMenu extends EaseTWBNavbar
{

    /**
     * Navigace
     * @var EaseHtmlUlTag
     */
    public $nav = NULL;

    /**
     * Hlavní menu aplikace
     *
     * @param string $name
     * @param mixed  $content
     * @param array  $properties
     */
    public function __construct($name = null, $content = null, $properties = null)
    {
        parent::__construct("Menu", 'VSMonitoring', array('class' => 'navbar-fixed-top'));

        $user = EaseShared::user();
        EaseTWBPart::twBootstrapize();
        if (!$user->getUserID()) {
            $this->addMenuItem('<a href="createaccount.php">' . EaseTWBPart::GlyphIcon('leaf') . ' ' . _('Registrace') . '</a>', 'right');
            $this->addMenuItem(
                    '
<li class="divider-vertical"></li>
<li class="dropdown">
<a class="dropdown-toggle" href="login.php" data-toggle="dropdown"><i class="icon-circle-arrow-left"></i> ' . _('Přihlášení') . '<strong class="caret"></strong></a>
<div class="dropdown-menu" style="padding: 15px; padding-bottom: 0px; left: -120px;">
<form method="post" class="navbar-form navbar-left" action="login.php" accept-charset="UTF-8">
<input style="margin-bottom: 15px;" type="text" placeholder="' . _('login') . '" id="username" name="login">
<input style="margin-bottom: 15px;" type="password" placeholder="' . _('Heslo') . '" id="password" name="password">
<!-- input style="float: left; margin-right: 10px;" type="checkbox" name="remember-me" id="remember-me" value="1">
<label class="string optional" for="remember-me"> ' . _('zapamatuj si mne') . '</label -->
<input class="btn btn-primary btn-block" type="submit" id="sign-in" value="' . _('přihlásit') . '">
</form>
</div>', 'right'
            );
        } else {

            $userMenu = '<li class="dropdown" style="width: 120px; text-align: right; background-image: url( ' . $user->getIcon() . ' ) ;  background-repeat: no-repeat; background-position: left center; background-size: 40px 40px;"><a href="#" class="dropdown-toggle" data-toggle="dropdown">' . $user->getUserLogin() . ' <b class="caret"></b></a>
<ul class="dropdown-menu" style="text-align: left; left: -60px;">
<li><a href="settings.php">' . EaseTWBPart::GlyphIcon('wrench') . '<i class="icon-cog"></i> ' . _('Nastavení') . '</a></li>
';

            if ($user->getSettingValue('admin')) {
                $userMenu .= '<li><a href="overview.php">' . EaseTWBPart::GlyphIcon('list') . ' ' . _('Přehled konfigurací') . '</a></li>';
            }

            $this->addMenuItem($userMenu . '
<li><a href="http://v.s.cz/kontakt.php">' . EaseTWBPart::GlyphIcon('envelope') . ' ' . _('Uživatelská podpora') . '</a></li>
<li class="divider"></li>
<li><a href="logout.php">' . EaseTWBPart::GlyphIcon('off') . ' ' . _('Odhlášení') . '</a></li>
</ul>
</li>
', 'right');
        }
    }

    /**
     * Vypíše stavové zprávy
     */
    public function draw()
    {
        $statusMessages = $this->webPage->getStatusMessagesAsHtml();
        if ($statusMessages) {
            $this->addItem(new EaseHtmlDivTag('StatusMessages', $statusMessages, array('class' => 'well', 'title' => _('kliknutím skryjete zprávy'))));
        }
        parent::draw();
    }

}

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
     * Vložení menu
     */
    public function afterAdd()
    {
        $nav = $this->addItem(new IEBootstrapMenu());
        $user = EaseShared::user();
        $userID = $user->getUserID();
        if ($userID) {

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
            $host = new IEHost();
            $hosts = $host->getListing(null, null, array('icon_image', 'platform'));
            $hostMenuItem = array(
                'wizard.php' => EaseTWBPart::GlyphIcon('forward') . ' ' . _('Průvodce založením hostu'),
            );
            if ($user->getSettingValue('admin')) {
                $hostMenuItem['host.php'] = EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nový Host');
            }
            if ($hosts) {
                foreach ($hosts as $cID => $cInfo) {
                    if ($cInfo['register'] != 1) {
                        continue;
                    }
                    $image = $cInfo['icon_image'];
                    if (!$image) {
                        $image = 'unknown.gif';
                    }

                    $hostMenuItem['host.php?host_id=' . $cInfo['host_id']] = IEHostOverview::icon($cInfo) . ' ' .
                            $cInfo['host_name'] . ' ' .
                            IEHostOverview::platformIcon($cInfo['platform']);
                }
                $hostMenuItem['hosts.php'] = EaseTWBPart::GlyphIcon('list') . ' ' . _('Detailní přehled hostů');
                $hostMenuItem[] = '';
            }
            $hostgroup = new IEHostgroup();
            $hostGroupMenuItem = array(
                'hostgroup.php' => EaseTWBPart::GlyphIcon('plus') . ' ' . _('Nová skupina hostů')/* ,
                      'exthostinfo.php' => _('Rozšířené informace hostů'),
                      'hostdependency.php' => _('Závislosti hostů'),
                      'hostescalation.php' => _('Eskalace hostů') */
            );
            $pocHostgroup = $hostgroup->getMyRecordsCount();

            if ($pocHostgroup) {
                $hostgroups = $hostgroup->myDbLink->queryToArray('SELECT ' . $hostgroup->getmyKeyColumn() . ', hostgroup_name, DatSave FROM ' . $hostgroup->myTable . ' WHERE user_id=' . $user->getUserID(), 'hostgroup_id');

                foreach ($hostgroups as $cID => $cInfo) {
                    $hostGroupMenuItem['hostgroup.php?hostgroup_id=' . $cInfo['hostgroup_id']] = EaseTWBPart::GlyphIcon('cloud') . ' ' . $cInfo['hostgroup_name'] . ' ' . EaseTWBPart::GlyphIcon('edit');
                }
                if (count($hostGroupMenuItem)) {
                    $hostGroupMenuItem['hostgroups.php'] = EaseTWBPart::GlyphIcon('list-alt') . ' ' . _('Přehled skupin hostů');
                    $hostGroupMenuItem[''] = '';
                }
            } else {
                if (!count($hosts)) {
                    $hostGroupMenuItem = array();
                }
            }


            if (EaseShared::user()->getSettingValue('unsaved') == true) {
                $nav->addMenuItem(
                        new EaseHtmlATag(
                        'apply.php', _('Uplatnit změny'), array('class' => 'btn btn-success')), 'right'
                );
            } else {
                $nav->addMenuItem(new EaseHtmlATag('apply.php', _('Uplatnit změny'), array('class' => 'btn btn-inverse')), 'right');
            }
            $nav->addDropDownMenu(_('Hosti'), array_merge($hostGroupMenuItem, $hostMenuItem));
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
                
                if(count($services)){
                    $services_menu = array( 'services.php' => EaseTWBPart::GlyphIcon('list') . ' ' . _('Přehled služeb'));
                    foreach ($services as $serviceID => $serviceInfo){
                        $services_menu['servicetweak.php?service_id='.$serviceID] = $serviceInfo[$service->nameColumn];
                    }
                    $nav->addDropDownMenu(_('Služby'),$services_menu);
                }
                
            }
            $nav->addDropDownMenu(_('Kontakty'), array(
                'contacts.php' => EaseTWBPart::GlyphIcon('list') . ' ' . _('Přehled kontaktů'),
                'contact.php' => EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nový kontakt'),
                'contactgroups.php' => EaseTWBPart::GlyphIcon('list') . ' ' . _('Přehled skupin kontaktů'),
                'contactgroup.php' => EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nová skupina kontaktů'))
            );

            if ($user->getSettingValue('admin')) {
                $nav->addDropDownMenu(_('Příkaz'), array(
                    'commands.php' => EaseTWBPart::GlyphIcon('list-alt') . ' ' . _('Přehled příkazů'),
                    'command.php' => EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nový příkaz'),
                    'importcommand.php' => EaseTWBPart::GlyphIcon('download') . ' ' . _('Importovat'))
                );
                $nav->addDropDownMenu(_('Rozšířené'), array(
                    'timeperiods.php' => EaseTWBPart::GlyphIcon('list') . ' ' . _('Přehled časových period'),
                    'timeperiod.php' => EaseTWBPart::GlyphIcon('plus') . ' ' . _('Nová časová perioda'),
                    'regenall.php' => EaseTWBPart::GlyphIcon('ok') . ' ' . _('Přegenerovat všechny konfiguráky'),
                    'dbrecreate.php' => EaseTWBPart::GlyphIcon('wrench') . ' ' . _('Reinicializovat databázi'),
                    'import.php' => EaseTWBPart::GlyphIcon('download') . ' ' . _('Importovat')
                        /* 'module.php' => _('definice modulů') */                        )
                );
            }
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

/**
 * Spodek stránky
 *
 * @package    VitexSoftware
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 */
class IEPageBottom extends EaseHtmlDivTag
{

    /**
     * Zobrazí přehled právě přihlášených a spodek stránky
     */
    public function finalize()
    {
        if (!count($this->webPage->heroUnit->pageParts)) {
            unset($this->webPage->container->pageParts['EaseHtmlDivTag@heroUnit']);
        };
        $this->SetTagID('footer');
        $this->addItem('<hr>');
        $this->addJavaScript('!function (d,s,id) {var js,fjs=d.getElementsByTagName(s)[0];if (!d.getElementById(id)) {js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");');
        $Foot = $this->addItem(new EaseHtmlDivTag('FootAbout'));
        $Foot->addItem('<a href="http://www.spoje.net"><img style="position: relative; top: -7px; left: -10px;" align="right" style="border:0" src="img/spojenet_small_white.gif" alt="SPOJE.NET" title="Housing zajišťují SPOJE.NET s.r.o." /></a>');
        $Foot->addItem('<span style="position: relative; top: -4px; left: -10px;">&nbsp;&nbsp; &copy; 2012 <a href="http://vitexsoftware.cz/">Vitex Software</a></span>');

//        $Foot->addItem('<a href="https://twitter.com/VSMonitoring" class="twitter-follow-button" data-show-count="true" data-lang="cs">Sledovat @VSMonitoring</a>');
    }

}
