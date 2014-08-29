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
     * @param string $FieldName    název políčka formuláře
     * @param string $FieldCaption popisek políčka
     * @param array  $DataSource   pole(tabulka=>sloupec)
     */
    public function __construct($FieldName, $FieldCaption, $DataSource, $members)
    {
        $IDColumn = $DataSource->keywordsInfo[$FieldName]['refdata']['idcolumn'];
        $nameColumn = $DataSource->keywordsInfo[$FieldName]['refdata']['captioncolumn'];
        $STable = $DataSource->keywordsInfo[$FieldName]['refdata']['table'];

        if (isset($DataSource->keywordsInfo[$FieldName]['refdata']['condition'])) {
            $Conditions = $DataSource->keywordsInfo[$FieldName]['refdata']['condition'];
        } else {
            $Conditions = array();
        }

        if (isset($DataSource->keywordsInfo[$FieldName]['refdata']['public']) && intval($DataSource->keywordsInfo[$FieldName]['refdata']['public'])) {
            $SqlConds = " ( " . $DataSource->myDbLink->prepSelect(array_merge($Conditions, array($DataSource->userColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $DataSource->myDbLink->prepSelect(array_merge($Conditions, array('public' => 1))) . ")  ";
        } else {
            $SqlConds = $DataSource->myDbLink->prepSelect(array_merge($Conditions, array($DataSource->userColumn => EaseShared::user()->getUserID())));
        }

        $InitialContent = new EaseHtmlFieldSet($FieldCaption);
        $InitialContent->setTagCss(array('width' => '100%'));

//        $AddNewItem = new EaseHtmlInputSearchTag($FieldName, '', array('class' => 'search-input', 'title' => _('přidání člena')));
//        $AddNewItem->setDataSource('jsondata.php?source[' . key($DataSource) . ']=' . current($DataSource));

        if (is_null($DataSource->getMyKey())) {
            $InitialContent->addItem(_('Nejprve je potřeba uložit záznam'));
        } else {

            if (DB_PREFIX . $STable == $DataSource->myTable) {
                $TmpKey = $DataSource->getMyKey();
                if ($TmpKey) {
                    $members[$TmpKey] = true;
                }
            }

            if ($members && count($members)) {
                $AviavbleCond = 'AND ' . $IDColumn . ' NOT IN (' . join(',', array_keys($members)) . ') ';
            } else {
                $AviavbleCond = '';
            }

            $membersAviableArray = EaseShared::myDbLink()->queryToArray(
                    'SELECT ' . $nameColumn . ', ' . $IDColumn . ' ' .
                    'FROM `' . DB_PREFIX . $STable . '` ' .
                    'WHERE (' . $SqlConds . ') ' .
                    $AviavbleCond .
                    'ORDER BY ' . $nameColumn, $IDColumn);

            if (DB_PREFIX . $STable == $DataSource->myTable) {
                unset($members[$DataSource->getMyKey()]);
            }

            if (count($membersAviableArray)) {
                foreach ($membersAviableArray as $MemberID => $MemberName) {
                    $Jellybean = new EaseHtmlSpanTag($MemberName[$nameColumn], null, array('class' => 'jellybean gray'));
                    $Jellybean->addItem(new EaseHtmlATag('?add=' . $FieldName . '&amp;member=' . $MemberID . '&amp;name=' . $MemberName[$nameColumn] . '&amp;' . $DataSource->getmyKeyColumn() . '=' . $DataSource->getMyKey() . '#' . $FieldName, EaseTWBPart::GlyphIcon('plus-sign').' '. $MemberName[$nameColumn]));
                    $InitialContent->addItem($Jellybean);
                }
            }

            if ($members && count($members)) {
                $InitialContent->addItem('</br>');
                foreach ($members as $MemberID => $MemberName) {
                    $Jellybean = new EaseHtmlSpanTag($MemberName, null, array('class' => 'jellybean'));
                    $Jellybean->addItem($MemberName);
                    $Jellybean->addItem(new EaseHtmlATag('?del=' . $FieldName . '&amp;member=' . $MemberID . '&amp;name=' . $MemberName . '&amp;' . $DataSource->getmyKeyColumn() . '=' . $DataSource->getMyKey() . '#' . $FieldName, EaseTWBPart::GlyphIcon('remove')));
                    $InitialContent->addItem($Jellybean);
                }
            }
        }
        parent::__construct($InitialContent);
    }

}
