<?php

namespace Icinga\Editor\UI;

/**
 * Vrací obrázek ikony hosta
 *
 * @package    IcingaEditor
 * @subpackage Engine
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class HostIcon extends \Ease\Html\ImgTag
{

    /**
     * Zobrazí obrázek hosta
     *
     * @param IEHost $host
     */
    public function __construct($host)
    {
        $image = 'unknown.gif';
        $title = '';
        if (is_array($host)) {
            if (isset($host['icon_image'])) {
                $image = $host['icon_image'];
                $title = $host['host_name'];
            }
        } else {
            $image = $host->getDataValue('icon_image');
            $title = $host->getName();
        }
        parent::__construct('logos/'.$image, $title, null, null,
            ['class' => 'host_icon']);
    }
}