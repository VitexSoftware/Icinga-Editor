<?php

namespace Icinga\Editor\UI;

/**
 * Page Bottom
 *
 * @package    VitexSoftware
 * @author     Vitex <vitex@hippy.cz>
 */
class PageBottom extends \Ease\Html\FooterTag {

    /**
     * Zobrazí přehled právě přihlášených a spodek stránky
     */
    public function finalize() {
        $composer = 'composer.json';
        if (!file_exists($composer)) {
            $composer = '../' . $composer;
        }

        $appInfo = json_decode(file_get_contents($composer));

        $container = $this->setTagID('footer');
        $this->addItem('<hr>');
        $footrow = new \Ease\TWB\Row();

        $author = '<a href="https://github.com/VitexSoftware/Icinga-Editor">Icinga Editor</a> v.: ' . $appInfo->version . '&nbsp;&nbsp; &copy; 2012-2017 <a href="http://vitexsoftware.cz/">Vitex Software</a>';
        $trans = new \Ease\Html\ATag('https://hosted.weblate.org/projects/icinga-editor/translations/',
                '<img src="img/weblate-128.png" width="20">' . _('Translated with Weblate'));

        $footrow->addColumn(4, [$author . '<br>' . $trans]);
        $footrow->addColumn(4,
                '<a href="http://www.austro-bohemia.cz/"><img style="position: relative;top: -2px; left: -10px; height: 25px" align="right" style="border:0" src="images/austro-bohemia-logo.png" alt="ABSRO" title="Pasivní checky napsány pro společnost Austro Bohemia s.r.o." /></a>');
        $footrow->addColumn(4,
                '<a href="http://www.spoje.net"><img style="position: relative; top: -7px; left: -10px;" align="right" style="border:0" src="img/spojenet_small_white.gif" alt="SPOJE.NET" title="Housing zajišťují SPOJE.NET s.r.o." /></a>');
        $this->addItem(new \Ease\TWB\Container($footrow));
//        $Foot->addItem('<a href="https://twitter.com/VSMonitoring" class="twitter-follow-button" data-show-count="true" data-lang="cs">Sledovat @VSMonitoring</a>');
    }

}
