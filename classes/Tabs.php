<?php

class Tabs extends ObjectModel
{

	public $name;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'mproducttabs',
		'primary' => 'id',
		'fields' => array(
			'name' =>			array('type' => self::TYPE_STRING, 'required' => true, 'size' => 255),
		),
		'associations' => array(
            'contents' =>             array('type' => self::HAS_MANY, 'field' => 'id_tab', 'object' => 'TabContents'),
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

    public function delete() {

        parent::delete();
        TabsContent::deleteTabContentByTabId($this->id);
    }

    public static function all() {

        return Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'mproducttabs`');
    }

    public static function getProductTabs($id_product) {

        $sql = 'SELECT pt.id, pt.name FROM `' . _DB_PREFIX_ . 'mproducttabs` pt
        LEFT JOIN `' . _DB_PREFIX_ . 'mproducttabs_content` ptc ON (pt.id = ptc.id_tab)
        WHERE ptc.id_product =' . (int)$id_product;

        return Db::getInstance()->executeS($sql);

    }

    public static function getTabIdByName($name) {

        return Db::getInstance()->getValue('SELECT id FROM `' . _DB_PREFIX_ . 'mproducttabs` WHERE name = "'. $name .'"');
    }

}
