<?php

include_once(_PS_MODULE_DIR_.'mproducttabs/classes/Tabs.php');
include_once(_PS_MODULE_DIR_.'mproducttabs/classes/TabsContent.php');

class MproducttabsTabsModuleFrontController extends ModuleFrontController
{   

    public $errors = false;

    public $token  = null;

    public function init()
    {
        $this->page_name = 'tabs';
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::init();
    }

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		
		$this->postProcess();
		
		parent::initContent();

		// $this->setTemplate('complaint.tpl');
	}

	/**
	 * @see FrontController::setMedia()
	 */
    public function setMedia()
    {
    	parent::setMedia();
    }

}
