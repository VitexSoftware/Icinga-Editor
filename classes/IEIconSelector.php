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

    static public $webprefix = '/icinga/';
    static public $webdir = '/usr/share/icinga/htdocs/';
    static public $icodir = '/images/logos/';

    /**
     * Volba ikony pro host
     * 
     * @param IEHost $host
     */
    function __construct($host)
    {
        parent::__construct();
        EaseJQueryPart::jQueryze($this);

        $icoBox = $this->addItem(new EaseHtmlFieldSet(_('Vyber Ikonu')));
        $icoBox->setTagCss('width: 100%;');

        $d = dir(self::$webdir . self::$icodir.'custom/');
        while (false !== ($entry = $d->read())) {
            if (is_dir(self::$webdir . self::$icodir . 'custom/'.$entry)) {
            } else {
                list($userid,$imgname) = explode('-', $entry);
                if($userid != $host->owner->getId()){
                    continue;
                }
                
                $hostIcon = new EaseHtmlImgTag(self::$webprefix . self::$icodir . 'custom/'.$entry);
                $hostIcon->setTagClass('host_icon');
                if ($host->getDataValue('icon_image') == 'custom/' . $entry) {
                    $hostIcon->setTagCss('border: 3px red solid;');
                }
                $icoBox->addItem(new EaseHtmlATag('?action=newicon&host_id=' . $host->getId() . '&newicon=custom/' . $entry, $hostIcon));
            }
        }
        $d->close();
        
        $d = dir(self::$webdir . self::$icodir.'base/');
        while (false !== ($entry = $d->read())) {
            if (strstr($entry, 'gd2')) {
                continue;
            }
            if (is_dir(self::$webdir . self::$icodir . 'base/'.$entry)) {
            } else {
                $hostIcon = new EaseHtmlImgTag(self::$webprefix . self::$icodir . 'base/'.$entry);
                $hostIcon->setTagClass('host_icon');
                if ($host->getDataValue('icon_image') == 'base/' . $entry) {
                    $hostIcon->setTagCss('border: 3px red solid;');
                }
                $icoBox->addItem(new EaseHtmlATag('?action=newicon&host_id=' . $host->getId() . '&newicon=base/' . $entry, $hostIcon));
            }
        }
        $d->close();

        $uplBox = $this->addItem(new EaseHtmlFieldSet(_('Nahraj vlastní')));
        $uplBox->setTagCss('width: 100%;');
        $icoupform = $uplBox->addItem(new EaseTWBForm('icoUp', null, 'POST', null, array('enctype' => 'multipart/form-data')));
        $icoupform->addItem(new EaseHtmlEmTag(_('Obrázek typu GIF,PNG nebo JPG, nejlépe 40x40 px')));
        $icoupform->addItem(new EaseTWBFormGroup(_('Ikona ze souboru'), new EaseHtmlInputFileTag('icofile'), '', _('Soubor v počítači')));
        $icoupform->addItem(new EaseTWBFormGroup(_('Ikona z adresy'), new EaseHtmlInputTextTag('icourl'), '', _('Soubor na internetu')));
        $icoupform->addItem(new EaseTWBFormGroup(_('Titulek obrázku'), new EaseHtmlInputTextTag('icon_image_alt'), '', _('nepovinné')));
        $icoupform->addItem(new EaseTWSubmitButton(_('Odeslat')));
        $icoupform->addItem(new EaseHtmlInputHiddenTag('host_id', $host->getId()));
    }

    /**
     * Otestuje zdali je soubor PNG/GIF/JPF
     * 
     * @param type $tmpfilename
     */
    public static function imageTypeOK($tmpfilename)
    {
        $finfo = new finfo(FILEINFO_MIME);
        $info = $finfo->file($tmpfilename);
        if (!$info) {
            return false;
        }
        if (strstr($info, 'gif')) {
            return true;
        }
        if (strstr($info, 'png')) {
            return true;
        }
        if (strstr($info, 'jpeg')) {
            return true;
        }
        return false;
    }

    /**
     * Uloží ikonu do správné složky
     * 
     * @param string $tmpfilename
     * @param IEHost $host
     * @return boolean
     */
    public static function saveIcon($tmpfilename, $host)
    {
        $id = $host->owner->getUserID();
        $size = getimagesize($tmpfilename);

        switch ($size['mime']) {
            case "image/gif":
                $suffix = 'gif';
                break;
            case 'image/jpeg':
                $suffix = 'jpg';
                break;
            case 'image/png':
                $suffix = 'png';
                break;
        }

        $newname =  'custom/' . $id . '-' . $host->getName().'.'.$suffix;
        if (rename($tmpfilename, self::$webdir . self::$icodir.'/' .$newname)) {
            return $newname;
        }
        return false;
    }

}
