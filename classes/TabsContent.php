<?php

class TabsContent extends ObjectModel
{
	public $id_tab;
    public $id_product;
    public $content;
    public $is_open;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'mproducttabs_content',
		'primary' => 'id',
		'fields' => array(
			'id_tab' =>  	   array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_product' =>    array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'content' =>	   array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'required' => true),
            'is_open' =>       array('type' => self::TYPE_INT, 'validate' => 'isBool', 'required' => true),
        ),
        'associations' => array(
            'tab' =>    array('type' => self::HAS_ONE, 'field' => 'id_tab', 'object' => 'Tabs'),
        ),
    );

    /**
     * Build an complaint
     *
     * @param int $id_complaint Existing complaint id in complaints to load object (optional)
     */
    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id);

    }

    /**
    * @see ObjectModel::add()
    */
    public function add($autodate = true, $null_values = false)
    {
        if (!parent::add($autodate, $null_values)) {
            return false;
        }

        return true;
    }

    public static function getTabContent($id_tab, $id_product) 
    {

        return Db::getInstance()->getRow('SELECT content, id as id_tab_content, is_open FROM `' . _DB_PREFIX_ . 'mproducttabs_content` WHERE id_tab ='.(int)$id_tab.' AND id_product = '.(int)$id_product);

    }

    public static function deleteTabContentByTabId($id_tab) 
    {
        return Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'mproducttabs_content` WHERE id_tab ='.(int)$id_tab);
    }

}