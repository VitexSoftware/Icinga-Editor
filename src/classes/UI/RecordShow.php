<?php

namespace Icinga\Editor\UI;

/**
 * Přehled záznamu konfigurace
 *
 * @todo dodělat
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class RecordShow extends \Ease\TWB\Well
{

    /**
     * Zobrazí přehled záznamu.
     *
     * @param ABBase $recordObject
     */
    public function __construct($recordObject)
    {
        parent::__construct();

        $row = new \Ease\TWB\Row();

        $this->addItem(new \Ease\Html\H3Tag($recordObject->getName()));

        $recordObject->setData($recordObject->htmlizeRow($recordObject->getData()));

        foreach ($recordObject->keywordsInfo as $keyword => $kinfo) {
            if ($keyword == $recordObject->nameColumn) {
                continue;
            }
            if (isset($kinfo['title'])) {
                $def = new \Ease\Html\DlTag();
                $def->addDef($kinfo['title'],
                    $recordObject->getDataValue($keyword));
                $row->addItem(new \Ease\TWB\Col(4, $def));
            }
        }

        $this->addItem($row);
    }

}
