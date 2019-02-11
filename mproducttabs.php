<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(_PS_MODULE_DIR_.'mproducttabs/classes/Tabs.php');
include_once(_PS_MODULE_DIR_.'mproducttabs/classes/TabsContent.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

class Mproducttabs extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'mproducttabs';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Rafał Woźniak';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product tabs');
        $this->description = $this->l('Display extra content tabs in product page');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall my module?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
        $this->registerHook('header') &&
        $this->registerHook('backOfficeHeader') &&
        $this->registerHook('actionProductUpdate') &&
        $this->registerHook('productTabs') &&
        $this->registerHook('displayAdminProductsExtra');
    }

    public function uninstall()
    {

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */

        $output = '';

        if (((bool)Tools::isSubmit('submitMproducttabsModule'))) {
            $output .= $this->postProcess();
        }

        if((bool)Tools::isSubmit('editMproducttabsModule')) {

            $id = Tools::getValue('MPRODUCTTABS_TAB_ID');
            $name = Tools::getValue('MPRODUCTTABS_TAB_NAME');

            $tab = new Tabs((int)$id);
            $tab->name = $name;
            if($tab->save()) 
               $output .= $this->displayConfirmation($this->l('Tab has been updated')); 

        }

        if(Tools::getIsset('deletemproducttabs')) {
            
            $id = Tools::getValue('id');
            $tab = new Tabs((int)$id);
            $tab->delete();

        }

        if(Tools::getIsset('updatemproducttabs')){
            $output .= $this->renderEditForm();
        } else {
            $output .= $this->renderForm();
        }

        return $output.$this->tabsList();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitMproducttabsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }


    protected function renderEditForm()
    {
        
        $id = Tools::getValue('id');
        $tab = new Tabs((int)$id);

        $fields = [
        ];

        $fields_values = [
            'MPRODUCTTABS_TAB_ID' => $tab->id,
            'MPRODUCTTABS_TAB_NAME' => $tab->name,
        ];

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'editMproducttabsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $fields_values, /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getEditForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Add new tab'),
                    'icon' => 'icon-plus',
                ),
                'input' => array(
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'desc' => $this->l('Enter a valid tab name'),
                        'name' => 'MPRODUCTTABS_TAB_NAME',
                        'label' => $this->l('Tab name'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Create the structure of your form.
     */
    protected function getEditForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Edit tab'),
                    'icon' => 'icon-pencil',
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'MPRODUCTTABS_TAB_ID',
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'desc' => $this->l('Enter a new valid tab name'),
                        'name' => 'MPRODUCTTABS_TAB_NAME',
                        'label' => $this->l('Tab name'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'MPRODUCTTABS_TAB_NAME' => ''
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach ($this->getConfigFormValues() as $field => $value) {

            if(!empty(Tools::getValue($field))) {
                $tab = new Tabs();
                $tab->name = Tools::getValue($field);
                $tab->add();     
            }

        }

        return $this->displayConfirmation($this->l('The tab has been added.'));
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        // $this->context->controller->addJS($this->_path.'/views/js/front.js');
        // $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }


    public function hookDisplayAdminProductsExtra() 
    {

        $id_product = Tools::getValue('id_product');

        $productTabs = Tabs::all();

        foreach ($productTabs as &$tab) {

            $tabContent = TabsContent::getTabContent($tab['id'], (int)$id_product);

            $tab['id_tab_content'] =  $tabContent['id_tab_content'];
            $tab['content'] =  $tabContent['content'];
        }

        $this->context->smarty->assign([
            'id_product' => (int)$id_product,
            'productTabs' => $productTabs

        ]);

        return $this->context->smarty->fetch($this->local_path.'views/templates/admin/tab.tpl');
    }

    public function hookActionProductUpdate($params)
    {

        $tabs = Tools::getValue('tab');
        $id_tabs = Tools::getValue('id_tabs');

        $contents = Tools::getValue('content');
        $methods = Tools::getValue('method');
        $id_tabs_content = Tools::getValue('id_tab_content');

        $id_product = $params['id_product'];

        foreach ($id_tabs as $tab_id) {

            if(!empty($contents[$tab_id]) && isset($tabs[$tab_id]))
            {

                switch ($methods[$tab_id]) {

                    case 'add':
                    $tabContent = new TabsContent();
                    $tabContent->id_tab = (int)$tab_id;
                    $tabContent->id_product = $id_product;
                    $tabContent->content = $contents[$tab_id];
                    $tabContent->add();

                    break;

                    case 'update':

                    $tabContent = new TabsContent((int)$id_tabs_content[$tab_id]);
                    $tabContent->content = $contents[$tab_id];
                    $tabContent->save();

                    break;
                }

            } else {

                $tabContent = new TabsContent((int)$id_tabs_content[$tab_id]);
                $tabContent->delete();

            }

        }

    }

    public function getProductTabsGroups($id_product) {

     $tabs = (object)Tabs::getProductTabs((int)$id_product);

     foreach ($tabs as &$tab) {

        $tab['content'] = TabsContent::getProductTabContent($tab['id'], (int)$id_product);

    }

    return $tabs;
}

public function hookDisplayFooterProduct()
{
    /* Place your code here. */
}

public function hookProductTabs($params)
{

    $id_product = (int)$params['id_product'];

    $tabs = $this->getProductTabsGroups($id_product);

    $this->context->smarty->assign('product_tabs', $tabs);

    return $this->context->smarty->fetch($this->local_path.'views/templates/hooks/mproducttabs.tpl');
}

public function tabsList()
{

    $tabs = Tabs::all();

    $fields_list = array(
        'id' => array(
            'title' => 'ID',
            'width' => 'auto',
            'type' => 'text'
        ),
        'name' => array(
            'title' => $this->l('Name'),
            'width' => 'auto',
            'type' => 'text'
        )
    );

    $helper = new HelperList();
    $helper->shopLinkType = '';
    $helper->simple_header = true;
    $helper->identifier = 'id';
    $helper->actions = array('edit', 'delete');
    $helper->show_toolbar = false;
    $helper->title = $this->displayName;
    $helper->table = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

    return $helper->generateList($tabs, $fields_list);

}

}