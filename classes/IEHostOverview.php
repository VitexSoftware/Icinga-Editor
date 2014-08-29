<?php

require_once 'Ease/EaseHtml.php';

/**
 * Description of IEHostOverview
 *
 * @author vitex
 */
class IEHostOverview extends EaseHtmlDivTag
{

    public function __construct($host)
    {
        parent::__construct();
        $this->addItem(new EaseHtmlH1Tag(array(self::icon($host), $host->getDataValue('alias'))));
        $this->addItem(new EaseHtmlH2Tag($host->getDataValue('host_name')));
        $this->addItem(new EaseHtmlH3Tag(array(self::platformIcon($host->getDataValue('platform')),$host->getDataValue('display_name'))));
        //$this->addItem(  );
        $parents = $host->getDataValue('parents');
        if ($parents) {
            $this->addItem(_('Rodiče') . ': ' . implode(',', $parents));
        }
        $this->addItem(new EaseHtmlDivTag(null, _('Uloženo') . ': ' . $host->getDataValue('DatSave')));
        $this->addItem(new EaseHtmlDivTag(null, _('Založeno') . ': ' . $host->getDataValue('DatCreate')));
    }

    public static function icon($host)
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

        return new EaseHtmlImgTag('logos/' . $image,$title,null,null,array('class'=>'host_icon'));
    }

    public static function platformIcon($platform)
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

        return new EaseHtmlImgTag('logos/' . $image, $platform);
    }

}
