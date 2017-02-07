<?php

namespace Icinga\Editor\UI;

/**
 * Volba ikony patřičné k hostu
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2017 Vitex@hippy.cz (G)
 */
class IconSelector extends \Ease\Container
{
    public static $webprefix = '/icinga/';
    public static $webdir    = '/usr/share/icinga/htdocs/';
    public static $icodir    = '/images/logos/';

    /**
     * Výchozí velikost ikony hosta
     * @var int
     */
    public static $imageSize = 40;

    /**
     * Host icon chooser
     *
     * @param \Icinga\Editor\Engine\Host $host
     */
    public function __construct($host)
    {
        parent::__construct();
        \Ease\JQuery\Part::jQueryze($this);

        $icodirs = ['' => ''];

        $icoBox = $this->addItem(new \Ease\TWB\Panel(_('Choose icon')));
        $icoBox->setTagCss(['width', '100%;']);

        $icodir = self::$webdir.self::$icodir;
        $d      = dir($icodir);
        if (is_object($d)) {
            while (false !== ($entry = $d->read())) {
                if (is_dir(self::$webdir.self::$icodir.'/'.$entry)) {
                    if ($entry[0] == '.') {
                        continue;
                    }
                    $icodirs[$entry] = $entry;
                }
            }
            $d->close();
        }

        $iconTabs = $icoBox->addItem(new \Ease\TWB\Tabs('IconTabs'));

        foreach ($icodirs as $subicodir) {
            $default    = false;
            $icons      = new \Ease\Container;
            $customIcos = self::$webdir.self::$icodir.$subicodir;
            $d          = dir($customIcos);
            if (is_object($d)) {
                while (false !== ($entry = $d->read())) {
                    if (!is_dir($customIcos.'/'.$entry)) {
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
                        $hostIcon = new \Ease\Html\ImgTag(self::$webprefix.self::$icodir.'/'.$subicodir.'/'.$entry);
                        $hostIcon->setTagClass('host_icon');
                        if ($host->getDataValue('icon_image') == 'custom/'.$entry) {
                            $hostIcon->setTagCss('border: 3px red solid;');
                        }
                        $icons->addItem(new \Ease\Html\ATag('?action=newicon&host_id='.$host->getId().'&newicon='.$subicodir.'/'.$entry,
                            $hostIcon));
                    }
                }
                $d->close();
            }
            if (!$subicodir) {
                $subicodir = 'ico';
            }
            $iconTabs->addTab($subicodir, $icons, $default);
        }

        $uplBox    = $this->addItem(new \Ease\TWB\Panel(_('Uload your own'),
            'info'));
        $uplBox->setTagCss(['width'=>'100%;']);
        $icoupform = $uplBox->addItem(new \Ease\TWB\Form('icoUp', null, 'POST',
            null, ['enctype' => 'multipart/form-data']));
        $icoupform->addItem(new \Ease\Html\EmTag(_('GIF,PNG or JPG image, best 40x40 px')));
        $icoupform->addItem(new \Ease\TWB\FormGroup(_('Icon from file'),
            new \Ease\Html\InputFileTag('icofile'), '', _('File on your disk')));
        $icoupform->addItem(new \Ease\TWB\FormGroup(_('Icon from URL'),
            new \Ease\Html\InputTextTag('icourl'), '', _('File on internet')));
        $icoupform->addItem(new \Ease\TWB\FormGroup(_('Image title'),
            new \Ease\Html\InputTextTag('icon_image_alt'), '', _('Optional')));
        $icoupform->addItem(new \Ease\TWB\SubmitButton(_('Save')));
        $icoupform->addItem(new \Ease\Html\InputHiddenTag('host_id',
            $host->getId()));
    }

    /**
     * Check if image is PNG/GIF/JPG
     *
     * @param string $tmpfilename
     */
    public static function imageTypeOK($tmpfilename)
    {
        if (class_exists('\finfo')) {
            $finfo = new \finfo(constant('FILEINFO_MIME'));
            $info  = $finfo->file($tmpfilename);
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
        } else {
            \Ease\Shared::webPage()->addStatusMessage('php-fileinfo ?!?!?',
                'error');
        }
        return false;
    }

    /**
     * Save icon to propper folder
     *
     * @param  string  $tmpfilename
     * @param  IEHost  $host
     * @return boolean
     */
    public static function saveIcon($tmpfilename, $host)
    {
        $id                 = $host->owner->getUserID();
        $thumbnailImagePath = $tmpfilename.'40';

        list($sourceImageWidth, $sourceImageHeight, $sourceImageType) = getimagesize($tmpfilename);
        switch ($sourceImageType) {
            case IMAGETYPE_ICO:
                $tmpfilenameIco = $tmpfilename.'.ico';
                $tmpfilenameGif = $tmpfilename.'.gif';
                rename($tmpfilename, $tmpfilenameIco);
                system("convert '".$tmpfilenameIco."[0]' ".$tmpfilenameGif);
                $tmpfilename    = $tmpfilenameGif;
                unlink($tmpfilenameIco);
            case IMAGETYPE_GIF:
                $sourceGdImage  = imagecreatefromgif($tmpfilename);
                $suffix         = 'gif';
                break;
            case IMAGETYPE_JPEG:
                $sourceGdImage  = imagecreatefromjpeg($tmpfilename);
                $suffix         = 'jpg';
                break;
            case IMAGETYPE_PNG:
                $sourceGdImage  = imagecreatefrompng($tmpfilename);
                $suffix         = 'png';
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
                $thumbnailImageHeight = (100 / ($sourceImageWidth / $thumbnailImageWidth))
                    * .01;
            }
            $thumbnailImageHeight = @round($sourceImageHeight * $thumbnailImageHeight);
        } elseif (isset($thumbnailImageHeight) AND ! isset($thumbnailImageWidth)) {
            // autocompute width if only height is set
            if ($thumbnailImageHeight) {
                $thumbnailImageWidth = (100 / ($sourceImageHeight / $thumbnailImageHeight))
                    * .01;
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
                $thumbnailImageHeight = (100 / ($sourceImageWidth / $thumbnailImageWidth))
                    * .01;
                $thumbnailImageHeight = @round($sourceImageHeight * $thumbnailImageHeight);
            } else {
                $thumbnailImageWidth = (100 / ($sourceImageHeight / $thumbnailImageHeight))
                    * .01;
                $thumbnailImageWidth = @round($sourceImageWidth * $thumbnailImageWidth);
            }
        }

        $thumbnailGdImage = imagecreatetruecolor($thumbnailImageWidth,
            $thumbnailImageHeight);

        switch ($sourceImageType) {
            case IMAGETYPE_PNG:
                // integer representation of the color black (rgb: 0,0,0)
                $background = imagecolorallocate($sourceGdImage, 0, 0, 0);
                // removing the black from the placeholder
                imagecolortransparent($thumbnailGdImage, $background);

                // turning off alpha blending (to ensure alpha channel information
                // is preserved, rather than removed (blending with the rest of the
                // image in the form of black))
                imagealphablending($thumbnailGdImage, false);

                // turning on alpha channel information saving (to ensure the full range
                // of transparency is preserved)
                imagesavealpha($sourceGdImage, true);

                break;
            case IMAGETYPE_GIF:
                // integer representation of the color black (rgb: 0,0,0)
                $background = imagecolorallocate($sourceGdImage, 0, 0, 0);
                // removing the black from the placeholder
                imagecolortransparent($thumbnailGdImage, $background);

                break;
        }

        imagecopyresampled($thumbnailGdImage, $sourceGdImage, 0, 0, 0, 0,
            $thumbnailImageWidth, $thumbnailImageHeight, $sourceImageWidth,
            $sourceImageHeight);

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

        $newname = 'custom/'.$id.'-'.$host->getName().'.'.$suffix;
        if (rename($thumbnailImagePath, self::$webdir.self::$icodir.'/'.$newname)) {
            unlink($tmpfilename);

            return $newname;
        }

        return false;
    }
}
