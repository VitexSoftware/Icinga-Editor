<?php

require_once 'Ease/EaseHtml.php';
/**
 * Description of IEHostOverview
 *
 * @author vitex
 */
class IEHostOverview extends EaseHtmlDivTag
{
    function __construct($host)
    {
        parent::__construct();
        $this->addItem(new EaseHtmlH1Tag( array(self::icon($host), $host->getDataValue('alias') )));
        $this->addItem(new EaseHtmlH2Tag( $host->getDataValue('host_name') ));
        $this->addItem(new EaseHtmlH3Tag( $host->getDataValue('display_name') ));
        $this->addItem(_('Rodiče').': '. implode(',', $host->getDataValue('parents')));
        $this->addItem( new EaseHtmlDivTag(null, _('Uloženo').': '. $host->getDataValue('DatSave')));
        $this->addItem( new EaseHtmlDivTag(null, _('Založeno').': '. $host->getDataValue('DatCreate')));
    }

    public static function icon($host)
    {
        $image = $host->getDataValue('icon_image');
        if(!$image){
            $image = 'unknown.gif';
        } 
        return new EaseHtmlImgTag('/icinga/images/logos/'.$image);
    }

}
