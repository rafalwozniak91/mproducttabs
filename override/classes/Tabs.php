<?php

class TabsCore extends ObjectModel
{

	public $name;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'mproducttabs',
		'primary' => 'id',
		'fields' => array(
			'name' =>			array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 255),
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

}