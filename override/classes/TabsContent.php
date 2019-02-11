<?php

class TabsContentCore extends ObjectModel
{
	public $id_tab;
    public $id_product;
	public $content;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'complaintform_images',
		'primary' => 'id',
		'fields' => array(
			'id_tab' =>  	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_product' =>    array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'content' =>			array('type' => self::TYPE_STRING, 'required' => true, 'size' => 255),
		),

	);

    /**
     * Build an complaint
     *
     * @param int $id_complaint Existing complaint id in complaints to load object (optional)
     */
    public function __construct($id_complaint_image = null, $id_lang = null)
    {
        parent::__construct($id_complaint_image);

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