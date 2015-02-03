<?php

/**
 * Volba platformy
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IEPlatformSelector extends EaseHtmlSelect
{

    public $platforms = array(
      'generic' => array('image' => 'logos/unknown.gif'),
      'linux' => array('image' => 'logos/base/win40.gif'),
      'windows' => array('image' => 'logos/base/linux40.gif'),
    );

    function loadItems()
    {
        return array('generic' => 'generic', 'windows' => 'windows', 'linux' => 'linux');
    }

    public function finalize()
    {
        parent::finalize();
        reset($this->platforms);
        foreach ($this->pageParts as $optionName => $option) {
            $platform = current($this->platforms);
            if (isset($platform['image'])) {
                $this->pageParts[$optionName]->setTagProperties(array('data-image' => $platform['image']));
            }
            next($this->platforms);
        }
        EaseShared::webPage()->addJavaScript('$("#' . $this->getTagID() . '").msDropDown();', null, true);
        EaseShared::webPage()->includeJavaScript('js/msdropdown/jquery.dd.min.js');
        EaseShared::webPage()->includeCss('css/msdropdown/dd.css');
    }

}
