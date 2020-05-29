<?php

namespace Icinga\Editor\UI;

/**
 * Vršek stránky
 *
 * @author     Vitex <vitex@hippy.cz>
 */
class PageTop extends \Ease\Html\HeaderTag {

    /**
     * Titulek stránky
     * @var string
     */
    public $pageTitle = 'Page Heading';

    /**
     * Nastavuje titulek
     *
     * @param string $pageTitle
     */
    public function __construct($pageTitle = null) {
        parent::__construct();
        if (!is_null($pageTitle)) {
            WebPage::singleton()->setPageTitle($pageTitle);
        }
        $this->setTagID('header');
    }

    /**
     * Vloží vršek stránky a hlavní menu
     */
    public function finalize() {
        $this->addItem(new MainMenu());
    }

}
