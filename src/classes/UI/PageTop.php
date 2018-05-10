<?php

namespace Icinga\Editor\UI;

/**
 * Vršek stránky
 *
 * @package    VitexSoftware
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 */
class PageTop extends \Ease\Html\HeaderTag
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
        parent::__construct();
        if (!is_null($pageTitle)) {
            \Ease\Shared::webPage()->setPageTitle($pageTitle);
        }
        $this->setTagID('header');
    }

    /**
     * Vloží vršek stránky a hlavní menu
     */
    public function finalize()
    {
        $this->addItem(new MainMenu());
    }

}
