<?php

namespace Icinga\Editor\UI;

use Ease\Html\DivTag;
use Ease\Shared;

/**
 * Třídy pro vykreslení stránky
 *
 * @package   VitexSoftware
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2019 Vitex@hippy.cz (G)
 */
class WebPage extends \Ease\TWB\WebPage {

    /**
     * Hlavní blok stránky
     * @var DivTag
     */
    public $container = NULL;

    /**
     * První sloupec
     * @var DivTag
     */
    public $columnI = NULL;

    /**
     * Druhý sloupec
     * @var DivTag
     */
    public $columnII = NULL;

    /**
     * Třetí sloupec
     * @var DivTag
     */
    public $columnIII = NULL;

    /**
     * Základní objekt stránky
     *
     * @param VSUser $userObject
     */
    public function __construct($pageTitle = null) {
        parent::__construct($pageTitle);
        $this->includeCss('css/default.css');
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
        $this->container = $this->addItem(new DivTag(null,
                        ['class' => 'container']));
    }

    /**
     * Rozdělí stránku do třísloupcového layoutu
     */
    function addPageColumns() {
        $row = $this->container->addItem(new DivTag(null,
                        ['class' => 'row']));

        $this->columnI = $row->addItem(new DivTag(null,
                        ['class' => 'col-md-4']));
        $this->columnII = $row->addItem(new DivTag(null,
                        ['class' => 'col-md-4']));
        $this->columnIII = $row->addItem(new DivTag(null,
                        ['class' => 'col-md-4']));
    }

    /**
     * Pouze pro admina
     *
     * @param string $loginPage
     */
    public function onlyForAdmin($loginPage = 'login.php') {
        if (!Shared::user()->getSettingValue('admin')) {
            Shared::user()->addStatusMessage(_('Nejprve se prosím přihlašte jako admin'),
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
    function onlyForLogged($loginPage = 'login.php', $message = null) {
        return parent::onlyForLogged($loginPage . '?backurl=' . urlencode($_SERVER['REQUEST_URI']),
                        $message);
    }

}
