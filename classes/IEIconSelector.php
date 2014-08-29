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
class IEIconSelector extends EaseContainer {

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
    public function __construct($host) {
        parent::__construct();
        EaseJQueryPart::jQueryze($this);

        $icodirs = array('' => '');

        $icoBox = $this->addItem(new EaseHtmlFieldSet(_('Vyber Ikonu')));
        $icoBox->setTagCss('width: 100%;');

        $icodir = self::$webdir . self::$icodir;
        $d = dir($icodir);
        if (is_object($d)) {
            while (false !== ($entry = $d->read())) {
                if (is_dir(self::$webdir . self::$icodir . '/' . $entry)) {
                    if ($entry[0] == '.') {
                        continue;
                    }
                    $icodirs[$entry] = $entry;
                }
            }
            $d->close();
        }

        $iconTabs = $icoBox->addItem(new EaseTWBTabs('IconTabs'));



        foreach ($icodirs as $subicodir) {
            $default = false;
            $icons = new EaseContainer;
            $customIcos = self::$webdir . self::$icodir . $subicodir;
            $d = dir($customIcos);
            if (is_object($d)) {
                while (false !== ($entry = $d->read())) {
                    if (!is_dir($customIcos . '/' . $entry)) {
                        if (strstr($entry, '.gd2') || ($entry[0] == '.')) {
                            continue;
                        }
                        if ($entry == 'custom') {
                            list($userid, $imgname) = explode('-', $entry);
                            if ($userid != $host->owner->getId()) {
                                continue;
                            }
                            $default = true;
                        }
                        $hostIcon = new EaseHtmlImgTag(self::$webprefix . self::$icodir . '/' . $subicodir . '/' . $entry);
                        $hostIcon->setTagClass('host_icon');
                        if ($host->getDataValue('icon_image') == 'custom/' . $entry) {
                            $hostIcon->setTagCss('border: 3px red solid;');
                        }
                        $icons->addItem(new EaseHtmlATag('?action=newicon&host_id=' . $host->getId() . '&newicon=' . $subicodir . '/' . $entry, $hostIcon));
                    }
                }
                $d->close();
            }
            if(!$subicodir){
                $subicodir = 'ico';
            }
            $iconTabs->addTab($subicodir, $icons,$default);
        }

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
     * Otestuje zdali je soubor PNG/GIF/JPG
     *

     *      * @param type $tmpfilename
     */
    public static function imageTypeOK($tmpfilename) {
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
        if (strstr($info, 'ico')) {
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
    public static function saveIcon($tmpfilename, $host) {
        $id = $host->owner->getUserID();
        $thumbnailImagePath = $tmpfilename . '40';

        list($sourceImageWidth, $sourceImageHeight, $sourceImageType) = getimagesize($tmpfilename);
        switch ($sourceImageType) {
            case IMAGETYPE_ICO:
                $tmpfilenameIco = $tmpfilename . '.ico';
                $tmpfilenameGif = $tmpfilename . '.gif';
                rename($tmpfilename, $tmpfilenameIco);
                system("convert '" . $tmpfilenameIco . "[0]' " . $tmpfilenameGif);
                $tmpfilename = $tmpfilenameGif;
                unlink($tmpfilenameIco);
            case IMAGETYPE_GIF:
                $sourceGdImage = imagecreatefromgif($tmpfilename);
                $suffix = 'gif';
                break;
            case IMAGETYPE_JPEG:
                $sourceGdImage = imagecreatefromjpeg($tmpfilename);
                $suffix = 'jpg';
                break;
            case IMAGETYPE_PNG:
                $sourceGdImage = imagecreatefrompng($tmpfilename);
                $suffix = 'png';
                break;
        }

        if (!$sourceImageHeight || !$sourceImageWidth) {
            return NULL;
        }

        if ($sourceImageWidth > $sourceImageHeight) {
            $thumbnailImageWidth = self::$imageSize;
        } else {
            $thumbnailImageHeight = self::$imageSize;
        }

        if (isset($thumbnailImageWidth) AND ! isset($thumbnailImageHeight)) {
            // autocompute height if only width is set
            if ($thumbnailImageWidth) {
                $thumbnailImageHeight = (100 / ($sourceImageWidth / $thumbnailImageWidth)) * .01;
            }
            $thumbnailImageHeight = @round($sourceImageHeight * $thumbnailImageHeight);
        } elseif (isset($thumbnailImageHeight) AND ! isset($thumbnailImageWidth)) {
            // autocompute width if only height is set
            if ($thumbnailImageHeight) {
                $thumbnailImageWidth = (100 / ($sourceImageHeight / $thumbnailImageHeight)) * .01;
            }
            $thumbnailImageWidth = @round($sourceImageWidth * $thumbnailImageWidth);
        } elseif (isset($thumbnailImageHeight) AND isset($thumbnailImageWidth) AND isset($constrain)) {
            // get the smaller resulting image dimension if both height
            // and width are set and $constrain is also set
            $hx = (100 / ($sourceImageWidth / $thumbnailImageWidth)) * .01;
            $hx = @round($sourceImageHeight * $hx);

            $wx = (100 / ($sourceImageHeight / $thumbnailImageHeight)) * .01;
            $wx = @round($sourceImageWidth * $wx);

            if ($hx < $thumbnailImageHeight) {
                $thumbnailImageHeight = (100 / ($sourceImageWidth / $thumbnailImageWidth)) * .01;
                $thumbnailImageHeight = @round($sourceImageHeight * $thumbnailImageHeight);
            } else {
                $thumbnailImageWidth = (100 / ($sourceImageHeight / $thumbnailImageHeight)) * .01;
                $thumbnailImageWidth = @round($sourceImageWidth * $thumbnailImageWidth);
            }
        }

        $thumbnailGdImage = imagecreatetruecolor($thumbnailImageWidth, $thumbnailImageHeight);
        imagecopyresampled($thumbnailGdImage, $sourceGdImage, 0, 0, 0, 0, $thumbnailImageWidth, $thumbnailImageHeight, $sourceImageWidth, $sourceImageHeight);

        switch ($sourceImageType) {
            case IMAGETYPE_ICO:
            case IMAGETYPE_GIF:
                imagegif($thumbnailGdImage, $thumbnailImagePath);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnailGdImage, $thumbnailImagePath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnailGdImage, $thumbnailImagePath, 9);
                break;
        }

        imagedestroy($sourceGdImage);
        imagedestroy($thumbnailGdImage);

        $newname = 'custom/' . $id . '-' . $host->getName() . '.' . $suffix;
        if (rename($thumbnailImagePath, self::$webdir . self::$icodir . '/' . $newname)) {
            unlink($tmpfilename);

            return $newname;
        }

        return false;
    }

}
