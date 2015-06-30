<?php

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
