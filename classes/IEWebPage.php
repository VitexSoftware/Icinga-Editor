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
     * @param VSUser $UserObject 
     */
    function __construct($PageTitle = null, &$UserObject = null)
    {
        if (is_null($UserObject)) {
            $UserObject = EaseShared::user();
        }
        $this->jQueryUISkin = $UserObject->getSettingValue('Skin');
        parent::__construct($PageTitle, $UserObject);
        $this->IncludeCss('css/bootstrap.css');
        $this->IncludeCss('css/default.css');
        $this->Head->addItem('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
        $this->addCss('body {
                padding-top: 60px;
                padding-bottom: 40px;
            }');
        $this->Head->addItem('<link rel="apple-touch-icon-precomposed" sizes="144x144" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-144-precomposed.png">');
        $this->Head->addItem('<link rel="apple-touch-icon-precomposed" sizes="114x114" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-114-precomposed.png">');
        $this->Head->addItem('<link rel="apple-touch-icon-precomposed" sizes="72x72" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-72-precomposed.png">');
        $this->Head->addItem('<link rel="apple-touch-icon-precomposed" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-57-precomposed.png">');


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
     * @param string $LoginPage
     */
    function onlyForAdmin($LoginPage = 'login.php')
    {
        if (!$this->User->getSettingValue('admin')) {
            EaseShared::user()->addStatusMessage(_('Nejprve se prosím přihlašte jako admin'), 'warning');
            $this->redirect($LoginPage);
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
    public $PageTitle = 'Page Heading';

    /**
     * Nastavuje titulek
     * 
     * @param string $PageTitle
     */
    function __construct($PageTitle = null)
    {
        parent::__construct('header');
        if (!is_null($PageTitle)) {
            EaseShared::webPage()->setPageTitle($PageTitle);
        }
    }

    /**
     * Vloží vršek stránky a hlavní menu
     */
    function finalize()
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
     * @param mixed $content
     * @param array $properties 
     */
    function __construct($name = null, $content = null, $properties = null)
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
<div class="dropdown-menu" style="padding: 15px; padding-bottom: 0px;">
<form method="post" class="navbar-form navbar-left" action="login.php" accept-charset="UTF-8">
<input style="margin-bottom: 15px;" type="text" placeholder="' . _('login') . '" id="username" name="login">
<input style="margin-bottom: 15px;" type="password" placeholder="' . _('Heslo') . '" id="password" name="password">
<!-- input style="float: left; margin-right: 10px;" type="checkbox" name="remember-me" id="remember-me" value="1">
<label class="string optional" for="remember-me"> ' . _('zapamatuj si mne') . '</label -->
<input class="btn btn-primary btn-block" type="submit" id="sign-in" value="' . _('přihlásit') . '">
<label style="text-align:center;margin-top:5px">' . _('nebo') . '</label>
<!-- input class="btn btn-primary btn-block" type="button" id="sign-in-google" value="Sign In with Google" -->
<a href="twauth.php?authenticate=1" class="btn btn-primary btn-block" type="button" id="sign-in-twitter">' . _('Autentifikace přez Twitter') . '</a>
</form>
</div>', 'left'
            );
        } else {
            $this->addMenuItem('
<li class="dropdown" style="width: 120px; text-align: right; background-image: url( ' . $user->getIcon() . ' ) ;  background-repeat: no-repeat; background-position: left center; background-size: 40px 40px;"><a href="#" class="dropdown-toggle" data-toggle="dropdown">' . $user->getUserLogin() . ' <b class="caret"></b></a>
<ul class="dropdown-menu" style="text-align: left;">
<li><a href="settings.php"><i class="icon-cog"></i> ' . _('Nastavení') . '</a></li>
<li><a href="overview.php"><i class="icon-list"></i> ' . _('Přehled konfigurací') . '</a></li>
<li><a href="http://v.s.cz/kontakt.php"><i class="icon-envelope"></i> ' . _('Uživatelská podpora') . '</a></li>
<li class="divider"></li>
<li><a href="logout.php"><i class="icon-off"></i> ' . _('Odhlášení') . '</a></li>
</ul>
</li>
', 'left');
        }
    }

    /**
     * Vypíše stavové zprávy 
     */
    function draw()
    {
        $StatusMessages = $this->webPage->getStatusMessagesAsHtml();
        if ($StatusMessages) {
            $this->addItem(new EaseHtmlDivTag('StatusMessages', $StatusMessages, array('class' => 'well', 'title' => _('kliknutím skryjete zprávy'))));
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
    function __construct()
    {
        parent::__construct('MainMenu');
    }

    /**
     * Vložení menu
     */
    function afterAdd()
    {
        $nav = $this->addItem(new IEBootstrapMenu());
        $User = EaseShared::user();
        $UserID = $User->getUserID();
        if ($UserID) {

            if ($User->getSettingValue('admin')) {
                $nav->addDropDownMenu(_('Uživatelé'), array(
                    'users.php' => '<i class="icon-list"></i>&nbsp;' . _('Přehled uživatelů'),
                    'createaccount.php' => EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nový uživatel'),
                        )
                );
            }


            $host = new IEHost();
            $hosts = $host->getListing(null,null,array('icon_image'));
            $hostMenuItem = array();
            if ($hosts) {
                foreach ($hosts as $cID => $cInfo) {
                    if ($cInfo['register'] != 1) {
                        continue;
                    }
                    $image = $cInfo['icon_image'];
                    if (!$image) {
                        $image = 'unknown.gif';
                    }

                    $hostMenuItem['host.php?host_id=' . $cInfo['host_id']] = '<img class="menuico" src="/icinga/images/logos/'.$image.'"> '.$cInfo['host_name'] . ' ' . EaseTWBPart::GlyphIcon('edit');
                }
                if (count($hostMenuItem)) {
                    $hostMenuItem[] = '';
                }
            }

            $nav->addDropDownMenu(_('Hosti'), array_merge($hostMenuItem, array(
                'wizard.php' => '<i class="icon-cog"></i>&nbsp;' . _('Průvodce rychlým založením'),
                'hosts.php' => '<i class="icon-list"></i>&nbsp;' . _('Přehled hostů'),
                'host.php' => EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nový Host'),
                'hostgroups.php' => '<i class="icon-list"></i>&nbsp;' . _('Přehled skupin hostů'),
                'hostgroup.php' => EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nová skupina hostů')/* , 
                              'exthostinfo.php' => _('Rozšířené informace hostů'),
                              'hostdependency.php' => _('Závislosti hostů'),
                              'hostescalation.php' => _('Eskalace hostů') */)
            ));
            $nav->addDropDownMenu(_('Služby'), array(
                'service.php' => '<i class="icon-edit"></i> ' . _('Nová služba'),
                'services.php' => '<i class="icon-list"></i>&nbsp;' . _('Přehled služeb'),
                'servicegroup.php' => EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nová skupina služeb'),
                'servicegroups.php' => '<i class="icon-list"></i>&nbsp;' . _('Přehled skupin služeb'), /*
                      'servicedependency.php' => _('Závislosti služeb'),
                      'extserviceinfo.php' => _('Rozšířené informace služeb'),
                      'serviceescalation.php' => _('Eskalace služeb') */)
            );
            $nav->addDropDownMenu(_('Kontakty'), array(
                'contacts.php' => '<i class="icon-list"></i>&nbsp;' . _('Přehled kontaktů'),
                'contact.php' => EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nový kontakt'),
                'contactgroups.php' => '<i class="icon-list"></i>&nbsp;' . _('Přehled skupin kontaktů'),
                'contactgroup.php' => EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nová skupina kontaktů'))
            );


            if ($User->getSettingValue('admin')) {
                $nav->addDropDownMenu(_('Příkaz'), array(
                    'commands.php' => '<i class="icon-list"></i>&nbsp;' . _('Přehled příkazů'),
                    'command.php' => EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nový příkaz'),
                    'importcommand.php' => '<i class="icon-download"></i> ' . _('Importovat'))
                );
                $nav->addDropDownMenu(_('Rozšířené'), array(
                    'timeperiods.php' => '<i class="icon-list"></i>&nbsp;' . _('Přehled časových period'),
                    'timeperiod.php' => EaseTWBPart::GlyphIcon('edit') . ' ' . _('Nová časová perioda'),
                    'regenall.php' => '<i class="icon-ok"></i> ' . _('Přegenerovat všechny konfiguráky'),
                    'dbrecreate.php' => '<i class="icon-wrench"></i> ' . _('Reinicializovat databázi'),
                    'import.php' => '<i class="icon-download"></i> ' . _('Importovat')
                        /* 'module.php' => _('definice modulů') */                        )
                );
            }
            $nav->addMenuItem(new EaseHtmlATag('apply.php', _('Uplatnit změny <i class="icon-ok"></i>'), array('class' => 'btn btn-warning')));
        }
    }

    /**
     * Přidá do stránky javascript pro skrývání oblasti stavových zpráv
     */
    function finalize()
    {
        EaseJQueryPart::jQueryze($this);
        $this->addJavaScript('$("#StatusMessages").click(function() { $("#StatusMessages").fadeTo("slow",0.25).slideUp("slow"); });', 3, true);
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
    function finalize()
    {
        if (!count($this->webPage->heroUnit->PageParts)) {
            unset($this->webPage->container->PageParts['EaseHtmlDivTag@heroUnit']);
        };
        $this->SetTagID('footer');
        $this->addItem('<hr>');
        $this->addJavaScript('!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");');
        $Foot = $this->addItem(new EaseHtmlDivTag('FootAbout'));
        $Foot->addItem('<a href="http://www.spoje.net"><img style="position: relative; top: -7px; left: -10px;" align="right" style="border:0" src="img/spojenet_small_white.gif" alt="SPOJE.NET" title="Housing zajišťují SPOJE.NET s.r.o." /></a>');
        $Foot->addItem('<span style="position: relative; top: -4px; left: -10px;">&nbsp;&nbsp; &copy; 2012 <a href="http://vitexsoftware.cz/">Vitex Software</a></span>');

        $Foot->addItem('<a href="https://twitter.com/VSMonitoring" class="twitter-follow-button" data-show-count="true" data-lang="cs">Sledovat @VSMonitoring</a>');
    }

}

?>
