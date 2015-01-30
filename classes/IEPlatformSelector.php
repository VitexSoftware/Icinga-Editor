<?php

/**
 * Volba platformy
 *
 * @author vitex
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

    function afterAdd()
    {
        EaseShared::webPage()->includeJavaScript('js/msdropdown/jquery.dd.min.js');
        EaseShared::webPage()->includeCss('css/msdropdown/dd.css');
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
        EaseShared::webPage()->addJavaScript('$("#' . $this->getTagID() . '").msDropDown()', null, true);
    }

    /**
     * Formátuje cenu
     *
     * @param float $price
     * @return string
     */
    function formatCurrency($price)
    {
        return round($price) . ' Kč';
    }

}
