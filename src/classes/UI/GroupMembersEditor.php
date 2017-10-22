<?php

namespace Icinga\Editor\UI;

/**
 * Group members Editor
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class GroupMembersEditor extends \Ease\Container
{

    /**
     * Group Members Editor
     *
     * @param string $fieldName    název políčka formuláře
     * @param string $fieldCaption popisek políčka
     * @param \Icinga\Editor\Engine\Configurator  $dataSource   editovaný objekt
     */
    public function __construct($fieldName, $fieldCaption, $dataSource, $members)
    {
        $iDColumn   = $dataSource->keywordsInfo[$fieldName]['refdata']['idcolumn'];
        $nameColumn = $dataSource->keywordsInfo[$fieldName]['refdata']['captioncolumn'];
        $sTable     = $dataSource->keywordsInfo[$fieldName]['refdata']['table'];

        if (isset($dataSource->keywordsInfo[$fieldName]['refdata']['condition'])) {
            $conditions = $dataSource->keywordsInfo[$fieldName]['refdata']['condition'];
        } else {
            $conditions = [];
        }

        if (isset($dataSource->keywordsInfo[$fieldName]['refdata']['public']) && intval($dataSource->keywordsInfo[$fieldName]['refdata']['public'])) {
            $sqlConds = " ( ".$dataSource->dblink->prepSelect(array_merge($conditions,
                        [$dataSource->userColumn => \Ease\Shared::user()->getUserID()]))." ) OR ( ".$dataSource->dblink->prepSelect(array_merge($conditions,
                        ['public' => 1])).")  ";
        } else {
            $sqlConds = $dataSource->dblink->prepSelect(array_merge($conditions,
                    [$dataSource->userColumn => \Ease\Shared::user()->getUserID()]));
        }

        $initialContent = new \Ease\TWB\Panel($fieldCaption);
        $initialContent->setTagCss(['width' => '100%']);




        if (is_null($dataSource->getMyKey())) {
            $initialContent->addItem(_('Save record first'));
        } else {

            if ($sTable == $dataSource->myTable) {
                $tmpKey = $dataSource->getMyKey();
                if ($tmpKey) {
                    $members[$tmpKey] = true;
                }
            }

            if ($members && count($members)) {
                $aviavbleCond = 'AND '.$iDColumn.' NOT IN ('.join(',',
                        array_keys($members)).') ';
            } else {
                $aviavbleCond = '';
            }

            $membersAviableArray = \Ease\Shared::db()->queryToArray(
                'SELECT '.$nameColumn.', '.$iDColumn.' '.
                'FROM `'.$sTable.'` '.
                'WHERE ('.$sqlConds.') '.
                $aviavbleCond.
                'ORDER BY '.$nameColumn, $iDColumn);

            if ($sTable == $dataSource->myTable) {
                unset($members[$dataSource->getMyKey()]);
            }
            $addText = _('Assign');
            $delText = _('Remove');

            $saverCode = htmlentities(str_replace('\\', '-',
                    get_class($dataSource)));

            if (count($membersAviableArray)) {
                foreach ($membersAviableArray as $memberID => $memberName) {
                    $reftable = $dataSource->keywordsInfo[$fieldName]['refdata']['table'];
                    $initialContent->addItem(
                        new \Ease\TWB\ButtonDropdown(
                        $memberName[$nameColumn], 'inverse', 'xs',
                        [
                        new \Ease\Html\ATag($reftable.'.php?'.$reftable.'_id='.$memberID,
                            \Ease\TWB\Part::GlyphIcon('wrench').' '._('Editor')),
                        new \Ease\Html\ATag(null,
                            \Ease\TWB\Part::GlyphIcon('plus-sign').' '.$addText,
                            [
                            'onClick' => "addGroupMember('".$saverCode."','".$dataSource->getId()."','".$fieldName."','".$memberName[$nameColumn]."','".$memberID."')"
                            , 'class' => 'handle', 'data-addtext' => $addText, 'data-deltext' => $delText])
                        ],
                        ['id' => $saverCode.'_'.$fieldName.'_'.$memberID,
                        'style' => 'margin: 1px;']));
                }
            }

            if ($members && count($members)) {
                $initialContent->addItem('</br>');
                foreach ($members as $memberID => $memberName) {
                    $reftable = $dataSource->keywordsInfo[$fieldName]['refdata']['table'];
                    $initialContent->addItem(
                        new \Ease\TWB\ButtonDropdown(
                        $memberName, 'success', 'xs',
                        [
                        new \Ease\Html\ATag($reftable.'.php?'.$reftable.'_id='.$memberID,
                            \Ease\TWB\Part::GlyphIcon('wrench').' '._('Edit')),
                        new \Ease\Html\ATag(null,
                            \Ease\TWB\Part::GlyphIcon('remove').' '._('Remove'),
                            [
                            'onClick' => "delGroupMember('".$saverCode."','".$dataSource->getId()."','".$fieldName."','".$memberName."','".$memberID."')"
                            , 'class' => 'handle', 'data-addtext' => $addText, 'data-deltext' => $delText])
                        ],
                        ['id' => $saverCode.'_'.$fieldName.'_'.$memberID,
                        'style' => 'margin: 1px;'])
                    );
                }
            }
        }
        parent::__construct($initialContent);
    }

    function finalize()
    {
        \Ease\Shared::webPage()->includeJavaScript('js/groupmembers.js');
    }
}
