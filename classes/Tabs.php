<?php

class Tabs extends ObjectModel
{

	public $name;
    public $position;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'mproducttabs',
		'primary' => 'id',
		'fields' => array(
			'name' =>			array('type' => self::TYPE_STRING, 'required' => true, 'size' => 255),
            'position' =>       array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
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
        $this->sortPosition();
    }

    public static function all() {

        return Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'mproducttabs` ORDER BY `position` ASC');
    }

    public static function getProductTabs($id_product) {

        $sql = 'SELECT pt.id, pt.name, ptc.content FROM `' . _DB_PREFIX_ . 'mproducttabs` pt
        LEFT JOIN `' . _DB_PREFIX_ . 'mproducttabs_content` ptc ON (pt.id = ptc.id_tab)
        WHERE ptc.id_product =' . (int)$id_product . ' ORDER BY pt.position ASC';

        return Db::getInstance()->executeS($sql);

    }

    public static function getTabIdByName($name) {

        return Db::getInstance()->getValue('SELECT id FROM `' . _DB_PREFIX_ . 'mproducttabs` WHERE name = "'. $name .'"');
    }

    public static function updatePosition($id_tab, $position) {

        $tab = new Tabs((int)$id_tab);
        $tab->position = (int)$position;

        return $tab->save();

    }

    public function sortPosition() {

        $tabs = Tabs::all();

        foreach ($tabs as $position => $tab) {
            
            $mproducttab = new Tabs((int)$tab);
            $mproducttab->position = (int)$position;
            $mproducttab->save();
        }

        return true;

    }

    public static function count() {
        
            return Db::getInstance()->getValue('SELECT COUNT(id) FROM `' . _DB_PREFIX_ . 'mproducttabs`');
    }

}