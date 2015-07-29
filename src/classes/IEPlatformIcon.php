<?php

/**
 * Obrázek platformy
 *
 * @package    IcingaEditor
 * @subpackage Engine
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class IEPlatformIcon extends EaseHtmlImgTag
{

    /**
     * Zobrazí obrázek platformy
     *
     * @param string $platform windows|linux
     */
    public function __construct($platform)
    {

        switch ($platform) {
            case 'windows':
                $image = 'base/win40.gif';
                break;
            case 'linux':
                $image = 'base/linux40.gif';
                break;
            default:
                $image = 'unknown.gif';
                break;
        }
        parent::__construct('logos/' . $image, $platform);
    }

}
