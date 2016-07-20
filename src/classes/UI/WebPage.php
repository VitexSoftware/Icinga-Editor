<?php

namespace Icinga\Editor\UI;

/**
 * Třídy pro vykreslení stránky
 *
 * @package   VitexSoftware
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2011 Vitex@hippy.cz (G)
 */
class WebPage extends \Ease\TWB\WebPage
{
    /**
     * Hlavní blok stránky
     * @var \Ease\Html\Div
     */
    public $container = NULL;

    /**
     * První sloupec
     * @var \Ease\Html\Div
     */
    public $columnI = NULL;

    /**
     * Druhý sloupec
     * @var \Ease\Html\Div
     */
    public $columnII = NULL;

    /**
     * Třetí sloupec
     * @var \Ease\Html\Div
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
            $userObject = \Ease\Shared::user();
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
        $this->container    = $this->addItem(new \Ease\Html\Div(null,
            ['class' => 'container']));
    }

    /**
     * Rozdělí stránku do třísloupcového layoutu
     */
    function addPageColumns()
    {
        $row = $this->container->addItem(new \Ease\Html\Div(null,
            ['class' => 'row']));

        $this->columnI   = $row->addItem(new \Ease\Html\Div(null,
            ['class' => 'col-md-4']));
        $this->columnII  = $row->addItem(new \Ease\Html\Div(null,
            ['class' => 'col-md-4']));
        $this->columnIII = $row->addItem(new \Ease\Html\Div(null,
            ['class' => 'col-md-4']));
    }

    /**
     * Pouze pro admina
     *
     * @param string $loginPage
     */
    public function onlyForAdmin($loginPage = 'login.php')
    {
        if (!\Ease\Shared::user()->getSettingValue('admin')) {
            \Ease\Shared::user()->addStatusMessage(_('Nejprve se prosím přihlašte jako admin'),
                'warning');
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
        return parent::onlyForLogged($loginPage.'?backurl='.urlencode($_SERVER['REQUEST_URI']));
    }
}