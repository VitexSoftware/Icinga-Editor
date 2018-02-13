<?php

namespace Icinga\Editor\UI;

/**
 * Assign Parents to host
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class ParentSelector extends \Ease\Container
{
    public $keyColumn = 'host_name';

    /**
     * Editor k přidávání členů skupiny
     *
     * @param IEHost $host
     */
    public function __construct($host)
    {
        parent::__construct();
        $fieldName      = $host->getKeyColumn();
        $initialContent = new \Ease\TWB\Panel(_('Host Parents'));

        $addparentForm = $initialContent->addItem(new \Ease\TWB\Form('addparent'));
        $addparentForm->addItem(new \Ease\TWB\FormGroup(_('IP or Hostname'),
            new \Ease\Html\InputTextTag('newparent')));
        $addparentForm->addItem(new \Ease\Html\InputHiddenTag($fieldName,
            $host->getId()));
        $addparentForm->addItem(new \Ease\TWB\SubmitButton(_('Add parent'),
            'success'));

        $initialContent->setTagCss(['width' => '100%']);

        if (is_null($host->getMyKey())) {
            $initialContent->addItem(_('Save record first'));
        } else {

            $allParents = $host->getListing();
            unset($allParents[$host->getID()]); //AntiLoop
            foreach ($allParents as $parentID => $parentInfo) {
                if ($parentInfo['register'] != 1) {
                    unset($allParents[$parentID]);
                }
            }

            $parentsAssigned = [];
            foreach ($host->getDataValue('parents') as $parentAssigned) {
                $parentID                                       = \Ease\Shared::db()->queryToValue('SELECT `'.$host->keyColumn.'` FROM '.$host->myTable.' WHERE `'.$host->nameColumn.'` = \''.addSlashes($parentAssigned).'\'');
                $parentsAssigned[$parentID][$host->nameColumn]  = $parentAssigned;
                $parentsAssigned[$parentID][$host->keyColumn] = $parentID;
                unset($allParents[$parentID]);
            }

            if (count($allParents)) {
                foreach ($allParents as $parentID => $parentInfo) {
                    $initialContent->addItem($this->parentButton($parentInfo,
                            'plus', $host));
                }
            }

            if (count($parentsAssigned)) {
                $initialContent->addItem('</br>');
                foreach ($parentsAssigned as $parentID => $parentInfo) {
                    $initialContent->addItem($this->parentButton($parentInfo,
                            'remove', $host));
                }
            }
        }

        $this->addItem($initialContent);
    }

    function &parentButton($parentInfo, $op, $host)
    {
        $pName    = $parentInfo['host_name'];
        $parentID = $parentInfo['host_id'];
        if ($op == 'plus') {
            $operation = 'add';
            $opCaption = _('Aassign parents');
            $type      = 'default';
        } else {
            $operation = 'del';
            $opCaption = _('Remove parents');
            $type      = 'success';
        }
        $parentMenu = new \Ease\TWB\ButtonDropdown(
            $pName, $type, 'xs',
            [
            new \Ease\Html\ATag('?'.$operation.'=parents&amp;name='.$parentInfo[$host->nameColumn].'&amp;member='.$parentID.'&amp;'.$host->keyColumn.'='.$host->getId(),
                \Ease\TWB\Part::GlyphIcon($op).' '.$opCaption),
            new \Ease\Html\ATag('host.php?host_id='.$parentID,
                \Ease\TWB\Part::GlyphIcon('wrench').' '._('Editor'))
            ]
        );

        return $parentMenu;
    }

}
