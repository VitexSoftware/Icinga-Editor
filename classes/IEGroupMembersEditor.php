<?php

/**
 * Konfigurátor členů skupiny
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IEGroupMembersEditor extends EaseContainer
{

    /**
     * Editor k přidávání členů skupiny
     *
     * @param string $fieldName    název políčka formuláře
     * @param string $fieldCaption popisek políčka
     * @param IEcfg  $dataSource   editovaný objekt
     */
    public function __construct($fieldName, $fieldCaption, $dataSource, $members)
    {
        $iDColumn = $dataSource->keywordsInfo[$fieldName]['refdata']['idcolumn'];
        $nameColumn = $dataSource->keywordsInfo[$fieldName]['refdata']['captioncolumn'];
        $sTable = $dataSource->keywordsInfo[$fieldName]['refdata']['table'];

        if (isset($dataSource->keywordsInfo[$fieldName]['refdata']['condition'])) {
            $conditions = $dataSource->keywordsInfo[$fieldName]['refdata']['condition'];
        } else {
            $conditions = array();
        }

        if (isset($dataSource->keywordsInfo[$fieldName]['refdata']['public']) && intval($dataSource->keywordsInfo[$fieldName]['refdata']['public'])) {
            $sqlConds = " ( " . $dataSource->myDbLink->prepSelect(array_merge($conditions, array($dataSource->userColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $dataSource->myDbLink->prepSelect(array_merge($conditions, array('public' => 1))) . ")  ";
        } else {
            $sqlConds = $dataSource->myDbLink->prepSelect(array_merge($conditions, array($dataSource->userColumn => EaseShared::user()->getUserID())));
        }

        $initialContent = new EaseTWBPanel($fieldCaption);
        $initialContent->setTagCss(array('width' => '100%'));

//        $AddNewItem = new EaseHtmlInputSearchTag($FieldName, '', array('class' => 'search-input', 'title' => _('přidání člena')));
//        $AddNewItem->setDataSource('jsondata.php?source[' . key($DataSource) . ']=' . current($DataSource));

        if (is_null($dataSource->getMyKey())) {
            $initialContent->addItem(_('Nejprve je potřeba uložit záznam'));
        } else {

            if ($sTable == $dataSource->myTable) {
                $tmpKey = $dataSource->getMyKey();
                if ($tmpKey) {
                    $members[$tmpKey] = true;
                }
            }

            if ($members && count($members)) {
                $aviavbleCond = 'AND ' . $iDColumn . ' NOT IN (' . join(',', array_keys($members)) . ') ';
            } else {
                $aviavbleCond = '';
            }

            $membersAviableArray = EaseShared::myDbLink()->queryToArray(
                'SELECT ' . $nameColumn . ', ' . $iDColumn . ' ' .
                'FROM `' . $sTable . '` ' .
                'WHERE (' . $sqlConds . ') ' .
                $aviavbleCond .
                'ORDER BY ' . $nameColumn, $iDColumn);

            if ($sTable == $dataSource->myTable) {
                unset($members[$dataSource->getMyKey()]);
            }
            $addText = _('Přiřadit');
            $delText = _('Odebrat');

            if (count($membersAviableArray)) {
                foreach ($membersAviableArray as $memberID => $memberName) {
                    $initialContent->addItem(
                        new EaseTWBButtonDropdown(
                        $memberName[$nameColumn], 'inverse', 'xs', array(
                      new EaseHtmlATag($dataSource->keywordsInfo[$fieldName]['refdata']['table'] . '.php?host_id=' . $memberID, EaseTWBPart::GlyphIcon('wrench') . ' ' . _('Editace')),
                      new EaseHtmlATag(null, EaseTWBPart::GlyphIcon('plus-sign') . ' ' . $addText, array(
                        'onClick' => "addGroupMember('" . get_class($dataSource) . "','" . $dataSource->getId() . "','" . $fieldName . "','" . $memberName[$nameColumn] . "','" . $memberID . "')"
                        , 'class' => 'handle', 'data-addtext' => $addText, 'data-deltext' => $delText))
                        ), array('id' => get_class($dataSource) . '_' . $fieldName . '_' . $memberID, 'style' => 'margin: 1px;')));
                }
            }

            if ($members && count($members)) {
                $initialContent->addItem('</br>');
                foreach ($members as $memberID => $memberName) {
                    $initialContent->addItem(
                        new EaseTWBButtonDropdown(
                        $memberName, 'success', 'xs', array(
                      new EaseHtmlATag($dataSource->keywordsInfo[$fieldName]['refdata']['table'] . '.php?host_id=' . $memberID, EaseTWBPart::GlyphIcon('wrench') . ' ' . _('Editace')),
                      new EaseHtmlATag(null, EaseTWBPart::GlyphIcon('remove') . ' ' . _('Odebrat'), array(
                        'onClick' => "delGroupMember('" . get_class($dataSource) . "','" . $dataSource->getId() . "','" . $fieldName . "','" . $memberName . "','" . $memberID . "')"
                        , 'class' => 'handle', 'data-addtext' => $addText, 'data-deltext' => $delText))
                        ), array('id' => get_class($dataSource) . '_' . $fieldName . '_' . $memberID, 'style' => 'margin: 1px;'))
                    );
                }
            }
        }
        parent::__construct($initialContent);
    }

    function finalize()
    {
        EaseShared::webPage()->includeJavaScript('js/groupmembers.js');
    }

}
