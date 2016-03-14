<?php
namespace Icinga\Editor\UI;

/**
 * Spodek stránky
 *
 * @package    VitexSoftware
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 */
class PageBottom extends \Ease\TWB\Container
{

    /**
     * Zobrazí přehled právě přihlášených a spodek stránky
     */
    public function finalize()
    {
        if (!defined('HIDEFOOTER')) {
            $this->SetTagID('footer');
            $this->addItem('<hr>');
            $star = '<iframe src="https://ghbtns.com/github-btn.html?user=Vitexus&repo=icinga_configurator&type=star&count=true" frameborder="0" scrolling="0" width="170px" height="20px"></iframe>';
            $footrow = new \Ease\TWB\Row();
            $footrow->addColumn(4, '<a href="https://www.vitexsoftware.cz/monitoring.php">Icinga Editor</a> v.: ' . constant('IE_VERSION') . '&nbsp;&nbsp; &copy; 2012-2015 <a href="http://vitexsoftware.cz/">Vitex Software</a>');
            $footrow->addColumn(4, '<a href="http://www.austro-bohemia.cz/"><img style="position: relative;top: -2px; left: -10px; height: 25px" align="right" style="border:0" src="images/austro-bohemia-logo.png" alt="ABSRO" title="Pasivní checky napsány pro společnost Austro Bohemia s.r.o." /></a>');
            $footrow->addColumn(4, '<a href="http://www.spoje.net"><img style="position: relative; top: -7px; left: -10px;" align="right" style="border:0" src="img/spojenet_small_white.gif" alt="SPOJE.NET" title="Housing zajišťují SPOJE.NET s.r.o." /></a>');
            $this->addItem($footrow);
//        $Foot->addItem('<a href="https://twitter.com/VSMonitoring" class="twitter-follow-button" data-show-count="true" data-lang="cs">Sledovat @VSMonitoring</a>');
        } else {
            $this->addItem('<hr>Icinga Editor v.: ' . constant('IE_VERSION'));
        }
    }

}
