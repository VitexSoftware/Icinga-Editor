<?php

require_once 'IEHost.php';

/**
 * Volba ikony patřičné k hostu
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IEIconSelector extends EaseContainer
{
    public $webprefix = '/icinga/';
    public $webdir = '/usr/share/icinga/htdocs/';
    public $icodir = '/images/logos/base/';

    /**
     * Volba ikony pro host
     * 
     * @param IEHost $host
     */
    function __construct($host)
    {
        parent::__construct();
        EaseJQueryPart::jQueryze($this);

        $d = dir($this->webdir .$this->icodir);
        while (false !== ($entry = $d->read())) {
                if(strstr($entry,'gd2')){
                    continue;
                }
                if(is_dir($this->webdir.$this->icodir.$entry)){
                    echo $entry;
                } else {
                    $hostIcon = new EaseHtmlImgTag($this->webprefix.$this->icodir . $entry);
                    if($host->getDataValue('icon_image')=='base/'.$entry){
                        $hostIcon->setTagCss('border: 3px red solid;');
                    }
                    $this->addItem( new EaseHtmlATag('?action=newicon&host_id='.$host->getId().'&newicon=base/'.$entry,$hostIcon) );
                }
        }
        $d->close();
    }

}
