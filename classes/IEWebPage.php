<?php

define('IE_VERSION', '0.144');

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
require_once 'IEContact.php';
require_once 'IEMainMenu.php';

class IEWebPage extends EaseTWBWebPage
{

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
        $this->IncludeCss('css/default.css');
        $this->head->addItem('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
        $this->addCss('body {
                padding-top: 60px;
                padding-bottom: 40px;
            }');
//        $this->head->addItem('<link rel="apple-touch-icon-precomposed" sizes="144x144" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-144-precomposed.png">');
//        $this->head->addItem('<link rel="apple-touch-icon-precomposed" sizes="114x114" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-114-precomposed.png">');
//        $this->head->addItem('<link rel="apple-touch-icon-precomposed" sizes="72x72" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-72-precomposed.png">');
        $this->head->addItem('<link rel="apple-touch-icon-precomposed" href="img/vsmonitoring.png">');
        $this->head->addItem('<link rel="shortcut icon"  type="image/png" href="img/vsmonitoring.png">');
        $this->addItem('<br>');
        $this->container = $this->addItem(new EaseHtmlDivTag(null, null, array('class' => 'container')));
    }

    /**
     * Rozdělí stránku do třísloupcového layoutu
     */
    function addPageColumns()
    {
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
        if (!$this->user->getSettingValue('admin')) {
            EaseShared::user()->addStatusMessage(_('Nejprve se prosím přihlašte jako admin'), 'warning');
            $this->redirect($loginPage);
            exit;
        }
    }

    /**
     * Nepřihlášeného uživatele přesměruje na přihlašovací stránku
     *
     * @param string $loginPage adresa přihlašovací stránky
     */
    function onlyForLogged($loginPage = 'login.php')
    {
        return parent::onlyForLogged($loginPage . '?backurl=' . urlencode($_SERVER['REQUEST_URI']));
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
        parent::__construct("Menu", new EaseHtmlImgTag('img/vsmonitoring.png', 'VSMonitoring', 20, 20, array('class' => 'img-rounded')), array('class' => 'navbar-fixed-top'));

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
<input class="form-control" style="margin-bottom: 15px;" type="text" placeholder="' . _('login') . '" id="username" name="login">
<input class="form-control" style="margin-bottom: 15px;" type="password" placeholder="' . _('Heslo') . '" id="password" name="password">
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
            $this->addItem(new EaseHtmlDivTag('StatusMessages', $statusMessages, array('class' => 'well', 'title' => _('kliknutím skryjete zprávy'), 'data-state' => 'down')));
            $this->addItem(new EaseHtmlDiv(null, array('id' => 'smdrag')));
        }
        parent::draw();
    }

}

/**
 * Spodek stránky
 *
 * @package    VitexSoftware
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 */
class IEPageBottom extends EaseTWBContainer
{

    /**
     * Zobrazí přehled právě přihlášených a spodek stránky
     */
    public function finalize()
    {
        $this->SetTagID('footer');
        $this->addItem('<hr>');
        $this->addJavaScript('!function (d,s,id) {var js,fjs=d.getElementsByTagName(s)[0];if (!d.getElementById(id)) {js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");');
        $foot = $this->addItem(new EaseHtmlDivTag('FootAbout'));

        $foot->addItem('&nbsp;<a href="http://www.austro-bohemia.cz/"><img style="position: relative;top: -2px; left: -10px; height: 25px" align="right" style="border:0" src="images/austro-bohemia-logo.png" alt="ABSRO" title="Pasivní checky napsány pro společnost Austro Bohemia s.r.o." /></a>');

        $foot->addItem('&nbsp;<a href="http://www.spoje.net"><img style="position: relative; top: -7px; left: -10px;" align="right" style="border:0" src="img/spojenet_small_white.gif" alt="SPOJE.NET" title="Housing zajišťují SPOJE.NET s.r.o." /></a>');
        $foot->addItem('&nbsp;<span style="position: relative; top: -4px; left: -10px;">Icinga Editor v.: ' . constant('IE_VERSION') . '&nbsp;&nbsp; &copy; 2012-2015 <a href="http://vitexsoftware.cz/">Vitex Software</a></span>');

//        $Foot->addItem('<a href="https://twitter.com/VSMonitoring" class="twitter-follow-button" data-show-count="true" data-lang="cs">Sledovat @VSMonitoring</a>');
    }

}
