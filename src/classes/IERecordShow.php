<?php

/**
 * Přehled záznamu konfigurace
 *
 * @todo dodělat
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class IERecordShow extends EaseTWBWell
{

    /**
     * Zobrazí přehled záznamu.
     *
     * @param ABBase $recordObject
     */
    public function __construct($recordObject)
    {
        parent::__construct();

        $row = new EaseTWBRow();

        $this->addItem(new EaseHtmlH3Tag($recordObject->getName()));

        $recordObject->setData($recordObject->htmlizeRow($recordObject->getData()));

        foreach ($recordObject->keywordsInfo as $keyword => $kinfo) {
            if ($keyword == $recordObject->nameColumn) {
                continue;
            }
            if (isset($kinfo['title'])) {
                $def = new EaseHtmlDlTag();
                $def->addDef($kinfo['title'], $recordObject->getDataValue($keyword));
                $row->addItem(new EaseTWBCol(4, $def));
            }
        }

        $this->addItem($row);
    }

}
