<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'Ease/EaseDataGrid.php';

/**
 * Description of DBFDataGrid
 *
 * @author vitex
 */
class IEDataGrid extends EaseDataGrid
{

    public $defaultColProp = array('sortable' => true);
    public $options = array(
      'method' => 'GET',
      'dataType' => 'json',
      'height' => 'auto',
      'width' => 'auto',
      'sortname' => 'id',
      'sortorder' => 'asc',
      'usepager' => true,
      'useRp' => true,
      'rp' => 20,
      'dblClickResize' => true,
      'showTableToggleBtn' => true,
      'add' => array(),
      'edit' => array(),
      'buttons' => array(
//        array('name' => 'CSV Export', 'bclass' => 'csvexport')
//        , array('name' => 'PDF Export', 'bclass' => 'pdfexport')
      )
    );
    public $addFormItems = array(array('name' => 'action', 'value' => 'add', 'type' => 'hidden'));
    public $editFormItems = array(array('name' => 'action', 'value' => 'edit', 'type' => 'hidden'));

    /**
     * Objekt jehož data jsou zobrazována
     * @var IECfg
     */
    public $dataSource = null;

    /**
     * Zdroj dat pro flexigrid
     *
     * @param string $name ID elementu
     * @param string $datasource URL
     * @param array $properties vlastnosti elementu
     */
    function __construct($name, $datasource, $properties = null)
    {
        $this->dataSource = $datasource;
        $this->options['title'] = $name;
        $this->setTagID();

        $this->options['url'] = 'datasource.php?class=' . get_class($datasource);
        $this->options['sortname'] = $datasource->getMyKeyColumn();
        $dataurl = null;

        parent::__construct($dataurl, $properties);
        EaseJQueryPart::jQueryze($this);
        EaseShared::webPage()->includeJavaScript('js/flexigrid.js');
        EaseShared::webPage()->includeCSS('css/flexigrid.css');
        $this->setUpButtons();
        $this->setUpColumns();
    }

    function setUpButtons()
    {
        $this->addAddButton(_('Přidat'));
        $this->addEditButton(_('Upravit'));
        $this->addDeleteButton(_('Smazat'));
    }

    function setUpColumns()
    {
        foreach ($this->dataSource->useKeywords as $keyword => $type) {
            if (isset($this->dataSource->keywordsInfo[$keyword])) {
                if (!isset($this->dataSource->keywordsInfo[$keyword]['title']) || !strlen(trim($this->dataSource->keywordsInfo[$keyword]['title']))) {
                    $this->addStatusMessage(_('Chybi titulek') . ' ' . $this->dataSource->keyword . ': ' . $keyword, 'warning');
                    $this->dataSource->keywordsInfo[$keyword]['title'] = $keyword;
                }
                if (strstr($type, 'VARCHAR') || strstr($type, 'TEXT') || strstr($type, 'SELECT') || strstr($type, 'PLATFORM') || strstr($type, 'IDLIST')) {
                    $this->setColumn($keyword, $this->dataSource->keywordsInfo[$keyword]['title'], true);
                } else {
                    $this->setColumn($keyword, $this->dataSource->keywordsInfo[$keyword]['title'], false);
                }
            }
        }
    }

    /**
     * Přidá tlačítko
     *
     * @param string $title Popisek tlačítka
     * @param string $class CSS třída tlačítka
     */
    function addButton($title, $class, $onpress = null)
    {
        if ($onpress) {
            $this->options['buttons'][] = array('name' => $title, 'bclass' => $class, 'onpress: ' . $onpress);
        } else {
            $this->options['buttons'][] = array('name' => $title, 'bclass' => $class);
        }
    }

    /**
     * Vloží přidávací tlačítko
     *
     * @param string $title  Nadpis gridu
     * @param string $target Url
     */
    function addAddButton($title, $target = null)
    {
        $show = false;
        if (is_null($target)) {
            $target = $this->options['url'];
        }
        $this->addButton($title, 'add', 'addRecord');
        $this->addJavaScript('function addRecord(com, grid) {
              $(location).attr(\'href\',\'' . $this->dataSource->keyword . '.php\');
            }
        ', null, true);
    }

    /**
     * Vloží editační tlačítko
     *
     * @param type $title
     * @param type $target
     */
    function addEditButton($title, $target = null)
    {
        $this->addButton($title, 'edit', 'editRecord');
        $this->addJavaScript('function editRecord(com, grid) {

        var numItems = $(\'.trSelected\').length
        if(numItems){
            if(numItems == 1) {
                $(\'.trSelected\', grid).each(function() {
                    var id = $(this).attr(\'id\');
                    id = id.substring(id.lastIndexOf(\'row\')+3);
                    $(location).attr(\'href\',\'' . $this->dataSource->keyword . '.php?' . $this->dataSource->getMyKeyColumn() . '=\' +id);
                });

            } else {
                $(\'.trSelected\', grid).each(function() {
                    var id = $(this).attr(\'id\');
                    id = id.substring(id.lastIndexOf(\'row\')+3);
                    var url =\'' . $this->dataSource->keyword . '.php?' . $this->dataSource->getMyKeyColumn() . '=\' +id;
                    var win = window.open(url, \'_blank\');
                    win.focus();
                });
            }
        } else {
            alert("' . _('Je třeba označit nějaké řádky') . '");
        }

            }
        ', null, true);
    }

    /**
     * Přidá tlačítko pro smazání záznamu
     *
     * @param string $title  popisek tlačítka
     * @param string $target výkonný skript
     */
    function addDeleteButton($title, $target = null)
    {
        if (is_null($target)) {
            $target = $this->options['url'];
        }
        $this->addButton($title, 'delete', 'deleteRecord');
        $this->addJavaScript('function deleteRecord(com, grid) {
              $(location).attr(\'href\',\'http://yourPage.com/\');
            }
        ', null, true);
    }

    /**
     * Nastaví parametry sloupečky
     *
     * @param string  $name             jméno z databáze
     * @param string  $title            popisek sloupce
     * @param boolean $search           nabídnout pro sloupec vyhledávání
     * @param array   $columnProperties další vlastnosti v poli
     */
    function setColumn($name, $title, $search = false, $columnProperties = null)
    {
        if (!isset($this->options['colModel'])) {
            $this->options['colModel'] = array();
        }
        if (!isset($columnProperties['editable'])) {
            $columnProperties['editable'] = false;
        }
        $properties = $this->defaultColProp;
        $properties['name'] = $name;
        $properties['display'] = $title;
        if (is_array($columnProperties)) {
            $this->options['colModel'][] = array_merge($columnProperties, $properties);
        } else {
            $this->options['colModel'][] = $properties;
        }
        if ($search) {
            if (is_array($search)) {
                foreach ($search as $sid => $srch) {
                    $search[$sid] .= ' LIKE "%"';
                }
                $search = implode(' OR ', $search);
            }
            $this->options['searchitems'][] = array('display' => $title, 'name' => $name, 'where' => addslashes($search));
        }

        if ($columnProperties['editable']) {
            if (!isset($columnProperties['label'])) {
                $columnProperties['label'] = $title;
            }
            if (!isset($columnProperties['value'])) {
                $columnProperties['value'] = $this->webPage->getRequestValue($name);
            }
            $columnProperties['name'] = $name;
            $this->editFormItems[$name] = $columnProperties;
            $this->addFormItems[$name] = $columnProperties;
        }
    }

    /**
     * Vložení skriptu
     */
    function finalize()
    {
        EaseShared::webPage()->addJavaScript("\n"
            . '$(\'#' . $this->getTagID() . '\').flexigrid({ ' . EaseJQueryPart::partPropertiesToString($this->options) . ' });', null, true);
    }

}
