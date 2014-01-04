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

    public static $webprefix = '/icinga/';
    public static $webdir = '/usr/share/icinga/htdocs/';
    public static $icodir = '/images/logos/';

    /**
     * Výchozí velikost ikony hosta
     * @var int
     */
    public static $imageSize = 40;

    /**
     * Volba ikony pro host
     *
     * @param IEHost $host
     */
    public function __construct($host)
    {
        parent::__construct();
        EaseJQueryPart::jQueryze($this);

        $icoBox = $this->addItem(new EaseHtmlFieldSet(_('Vyber Ikonu')));
        $icoBox->setTagCss('width: 100%;');

        $d = dir(self::$webdir . self::$icodir . 'custom/');
        while (false !== ($entry = $d->read())) {
            if (is_dir(self::$webdir . self::$icodir . 'custom/' . $entry)) {

            } else {
                list($userid, $imgname) = explode('-', $entry);
                if ($userid != $host->owner->getId()) {
                    continue;
                }

                $hostIcon = new EaseHtmlImgTag(self::$webprefix . self::$icodir . 'custom/' . $entry);
                $hostIcon->setTagClass('host_icon');
                if ($host->getDataValue('icon_image') == 'custom/' . $entry) {
                    $hostIcon->setTagCss('border: 3px red solid;');
                }
                $icoBox->addItem(new EaseHtmlATag('?action=newicon&host_id=' . $host->getId() . '&newicon=custom/' . $entry, $hostIcon));
            }
        }
        $d->close();

        $d = dir(self::$webdir . self::$icodir . 'base/');
        while (false !== ($entry = $d->read())) {
            if (strstr($entry, 'gd2')) {
                continue;
            }
            if (is_dir(self::$webdir . self::$icodir . 'base/' . $entry)) {

            } else {
                $hostIcon = new EaseHtmlImgTag(self::$webprefix . self::$icodir . 'base/' . $entry);
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
     * @param  string  $tmpfilename
     * @param  IEHost  $host
     * @return boolean
     */
    public static function saveIcon($tmpfilename, $host)
    {
        $id = $host->owner->getUserID();
        $thumbnail_image_path = $tmpfilename . '40';

        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($tmpfilename);
        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                $source_gd_image = imagecreatefromgif($tmpfilename);
                $suffix = 'gif';
                break;
            case IMAGETYPE_JPEG:
                $source_gd_image = imagecreatefromjpeg($tmpfilename);
                $suffix = 'jpg';
                break;
            case IMAGETYPE_PNG:
                $source_gd_image = imagecreatefrompng($tmpfilename);
                $suffix = 'png';
                break;
        }

        if (!$source_image_height || !$source_image_width) {
            return NULL;
        }

        if ($source_image_width > $source_image_height) {
            $thumbnail_image_width = self::$imageSize;
        } else {
            $thumbnail_image_height = self::$imageSize;
        }

        if (isset($thumbnail_image_width) AND !isset($thumbnail_image_height)) {
            // autocompute height if only width is set
            if ($thumbnail_image_width) {
                $thumbnail_image_height = (100 / ($source_image_width / $thumbnail_image_width)) * .01;
            }
            $thumbnail_image_height = @round($source_image_height * $thumbnail_image_height);
        } elseif (isset($thumbnail_image_height) AND !isset($thumbnail_image_width)) {
            // autocompute width if only height is set
            if ($thumbnail_image_height) {
                $thumbnail_image_width = (100 / ($source_image_height / $thumbnail_image_height)) * .01;
            }
            $thumbnail_image_width = @round($source_image_width * $thumbnail_image_width);
        } elseif (isset($thumbnail_image_height) AND isset($thumbnail_image_width) AND isset($constrain)) {
            // get the smaller resulting image dimension if both height
            // and width are set and $constrain is also set
            $hx = (100 / ($source_image_width / $thumbnail_image_width)) * .01;
            $hx = @round($source_image_height * $hx);

            $wx = (100 / ($source_image_height / $thumbnail_image_height)) * .01;
            $wx = @round($source_image_width * $wx);

            if ($hx < $thumbnail_image_height) {
                $thumbnail_image_height = (100 / ($source_image_width / $thumbnail_image_width)) * .01;
                $thumbnail_image_height = @round($source_image_height * $thumbnail_image_height);
            } else {
                $thumbnail_image_width = (100 / ($source_image_height / $thumbnail_image_height)) * .01;
                $thumbnail_image_width = @round($source_image_width * $thumbnail_image_width);
            }
        }

        $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
        imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);

        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                imagegif($thumbnail_gd_image, $thumbnail_image_path);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnail_gd_image, $thumbnail_image_path, 9);
                break;
        }

        imagedestroy($source_gd_image);
        imagedestroy($thumbnail_gd_image);

        $newname = 'custom/' . $id . '-' . $host->getName() . '.' . $suffix;
        if (rename($thumbnail_image_path, self::$webdir . self::$icodir . '/' . $newname)) {
            unlink($tmpfilename);
            return $newname;
        }

        return false;
    }

}
