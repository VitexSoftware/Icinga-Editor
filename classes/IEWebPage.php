<?php

define('IE_VERSION', '0.162');

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
