<?php

require_once 'IEHost.php';

/**
 * Volba rodičů patřičných k hostu
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IEParentSelector extends EaseContainer
{

    public $myKeyColumn = 'host_name';

    /**
     * Editor k přidávání členů skupiny
     *
     * @param IEHost $host
     */
    public function __construct($host)
    {
        parent::__construct();
        $fieldName = $host->getmyKeyColumn();
        $initialContent = new EaseHtmlFieldSet(_('rodiče hostu'));

        $addparentForm = $initialContent->addItem(new EaseTWBForm('addparent'));
        $addparentForm->addItem(new EaseTWBFormGroup(_('IP nebo Hostname'), new EaseHtmlInputTextTag('newparent')));
        $addparentForm->addItem(new EaseHtmlInputHiddenTag($fieldName, $host->getId()));
        $addparentForm->addItem(new EaseTWSubmitButton(_('Přidat rodiče'), 'success'));

        $initialContent->setTagCss(array('width' => '100%'));

        if (is_null($host->getMyKey())) {
            $initialContent->addItem(_('Nejprve je potřeba uložit záznam'));
        } else {

            $allParents = $host->getListing();
            unset($allParents[$host->getID()]); //Nenabízet sám sebe jako rodiče
            foreach ($allParents as $parentID => $parentInfo) {
                if ($parentInfo['register'] != 1) {
                    unset($allParents[$parentID]);
                }
            }

            $parentsAssigned = array();
            foreach ($host->getDataValue('parents') as $parentAssigned) {
                $parentID = EaseShared::myDbLink()->queryToValue('SELECT `' . $host->myKeyColumn . '` FROM ' . $host->myTable . ' WHERE `' . $host->nameColumn . '` = \'' . addSlashes($parentAssigned) . '\'');
                $parentsAssigned[$parentID][$host->nameColumn] = $parentAssigned;
                $parentsAssigned[$parentID][$host->myKeyColumn] = $parentID;
                unset($allParents[$parentID]);
            }

            if (count($allParents)) {
                foreach ($allParents as $parentID => $parentInfo) {
                    $initialContent->addItem($this->parentButton($parentInfo, 'plus', $host));
                }
            }

            if (count($parentsAssigned)) {
                $initialContent->addItem('</br>');
                foreach ($parentsAssigned as $parentID => $parentInfo) {
                    $initialContent->addItem($this->parentButton($parentInfo, 'remove', $host));
                }
            }
        }

        $this->addItem($initialContent);
    }

    function &parentButton($parentInfo, $op, $host)
    {
        $pName = $parentInfo['host_name'];
        $parentID = $parentInfo['host_id'];
        if ($op == 'plus') {
            $operation = 'add';
            $opCaption = _('Přiřadit rodiče');
            $type = 'default';
        } else {
            $operation = 'del';
            $opCaption = _('Odstranit rodiče');
            $type = 'success';
        }
        $parentMenu = new EaseTWBButtonDropdown(
            $pName, $type, 'xs', array(
          new EaseHtmlATag('?' . $operation . '=parents&amp;name=' . $parentInfo[$host->nameColumn] . '&amp;member=' . $parentID . '&amp;' . $host->myKeyColumn . '=' . $host->getId(), EaseTWBPart::GlyphIcon($op) . ' ' . $opCaption),
          new EaseHtmlATag('host.php?host_id=' . $parentID, EaseTWBPart::GlyphIcon('wrench') . ' ' . _('Editace'))
            )
        );

        return $parentMenu;
    }

}
