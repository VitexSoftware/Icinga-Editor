<?php
require_once 'IEHost.php';
/**
 * Volba služeb patřičných k hostu
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IEParentSelector extends EaseContainer
{

    public $MyKeyColumn = 'host_name';

    /**
     * Editor k přidávání členů skupiny
     *
     * @param IEHost $host
     */
    public function __construct($host)
    {
        parent::__construct();
        $fieldName = $host->getMyKeyColumn();
        $initialContent = new EaseHtmlFieldSet(_('rodiče hostu'));
        
        $addparentForm = $initialContent->addItem( new EaseTWBForm('addparent') );
        $addparentForm->addItem( new EaseTWBFormGroup(_('IP nebo Hostname'), new EaseHtmlInputTextTag('newparent')) );
        $addparentForm->addItem( new EaseHtmlInputHiddenTag($fieldName, $host->getId()));
        $addparentForm->addItem( new EaseTWSubmitButton(_('Přidat rodiče'), 'success') );
        
        $initialContent->setTagCss(array('width' => '100%'));

        if (is_null($host->getMyKey())) {
            $initialContent->addItem(_('Nejprve je potřeba uložit záznam'));
        } else {

            $allParents = $host->getListing();
            foreach ($allParents as $parentID => $parentInfo) {
                if ($parentInfo['register']!=1) {
                    unset($allParents[$parentID]);
                }
            }

            foreach ($host->getDataValue('parents') as $parentAssigned) {
                $parentID = EaseShared::myDbLink()->queryToValue('SELECT `'.$host->MyKeyColumn.'` FROM '. $host->myTable .' WHERE `'. $host->NameColumn .'` = \''. addSlashes($parentAssigned) .'\'' );
                $parentsAssigned[$parentID] = $parentAssigned;
                unset($allParents[$parentID]);
            }

            if (count($allParents)) {

                foreach ($allParents as $parentID => $parentInfo) {
                    //localhost/IcingaEditor/host.php?add=parents&member=60&name=192.168.222.42&host_id=9#parents
                    $jellybean = new EaseHtmlSpanTag($parentInfo[$host->NameColumn], null, array('class' => 'jellybean gray'));
                    $jellybean->addItem(new EaseHtmlATag('?add=parents&amp;name=' . $parentInfo[$host->NameColumn] . '&amp;member=' . $parentID .  '&amp;' . $host->MyKeyColumn . '=' . $host->getId(), $parentInfo[$host->NameColumn]));
                    $initialContent->addItem($jellybean);
                }
            }

            if (count($parentsAssigned)) {
                $initialContent->addItem('</br>');
                foreach ($parentsAssigned as $parentID => $parentInfo) {
                    $jellybean = new EaseHtmlSpanTag($parentInfo, null, array('class' => 'jellybean'));
                    $jellybean->addItem($parentInfo);
                    //localhost/IcingaEditor/host.php?del=parents&member=0&name=natwor&host_id=9#parents                    
                    $jellybean->addItem(new EaseHtmlATag('?del=parents&amp;name=' . $parentInfo . '&amp;member=' . $parentID . '&amp;' . $host->getMyKeyColumn() . '=' . $host->getMyKey() . '&amp;' . $host->MyKeyColumn . '=' . $host->getId(), EaseTWBPart::GlyphIcon('remove')));
                    $initialContent->addItem($jellybean);
                }
            }

        }

        $this->addItem($initialContent);
    }

}
