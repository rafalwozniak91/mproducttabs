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
        $this->version = '1.2.1';
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
        Configuration::updateValue('MPRODUCTS_PTS_TABS', false);
        Configuration::updateValue('MPRODUCTS_PTS_LIMIT', 500);
        Configuration::updateValue('MPRODUCTS_PTS_OFFSET_LIMIT', 0);

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


        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

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
    if(Tools::getIsset('updatePositions')) {
        echo $this->ajaxProcessUpdatePositions();
    }

    if((bool)Tools::isSubmit('rewriteMproducttabsModule')) {
        $this->submitRewriteTabs();
    }

    if(Tools::getIsset('updatemproducttabs')){
        $output .= $this->renderEditForm();
    } else {
        $output .= $this->renderForm();
    }

    return $output.$this->tabsList().$this->confimrUpdatedTabs().$this->renderPtsForm();
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
                $tab->position = Tabs::count();
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
        $this->context->controller->addJS($this->_path.'views/js/back/mproducttabs.js');
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
            $tab['is_open'] = $tabContent['is_open'];
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
        $id_tabs_content = Tools::getValue('id_tab_content');
        $is_open = Tools::getValue('is_open');

        $id_product = $params['id_product'];

        foreach ($id_tabs as $tab_id) {

            if(!empty($contents[$tab_id]) && isset($tabs[$tab_id]))
            {

                if(TabsContent::getTabContent($tab_id, $id_product)) {

                    $tabContent = new TabsContent((int)$id_tabs_content[$tab_id]);
                    $tabContent->content = $contents[$tab_id];
                    $tabContent->is_open = $is_open ? (int)array_key_exists($tab_id, $is_open) : 0;
                    $tabContent->save();

                } else {

                    $tabContent = new TabsContent();
                    $tabContent->id_tab = (int)$tab_id;
                    $tabContent->id_product = $id_product;
                    $tabContent->content = $contents[$tab_id];
                    $tabContent->is_open = $is_open ? (int)array_key_exists($tab_id, $is_open) : 0;
                    $tabContent->add(); 
                }

            } else {

                $tabContent = new TabsContent((int)$id_tabs_content[$tab_id]);
                $tabContent->delete();

            }

        }
    }

    public function hookDisplayFooterProduct()
    {
        /* Place your code here. */
    }

    public function hookProductTabs($params)
    {

        $id_product = (int)$params['id_product'];
        $tabs = (object)Tabs::getProductTabs((int)$id_product);

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
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'filter_key' => 'a!position',
                'position' => 'position',
                'align' => 'center',
                'class' => 'fixed-width-md'
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'id';
        $helper->position_identifier = 'position';
        $helper->orderBy = 'position';
        $helper->orderWay = 'ASC';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = false;
        $helper->table_id = 'module-'.$this->name;
        $helper->title = $this->displayName;
        $helper->table = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        return $helper->generateList($tabs, $fields_list);

    }

    public function ajaxProcessUpdatePositions()
    {
        $positions = Tools::getValue('module-mproducttabs');

        if (!is_array($positions))
            return false;
        
        foreach ($positions as $position => $value) {
            
            $pos = explode('_', $value);

            Tabs::updatePosition($pos[2], $position);

        }

        return Tools::jsonEncode([
            'status' => true
        ]);
        
    }


    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderPtsForm()
    {
        $helper = new HelperForm();

        $fields_value = [
            'MPRODUCTTABS_PTS' => ''
        ];


        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'rewriteMproducttabsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' =>$fields_value, /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getPtsConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getPtsConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Rewrite pts tabs'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('PTS tabs'),
                        'name' => 'MPRODUCTTABS_PTS',
                        'is_bool' => true,
                        'desc' => $this->l('Do you want to rewrite pts tabs content?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }


    public function confimrUpdatedTabs() {

        $id_lang = (int)$this->context->language->id;
        $id_shop = (int)$this->context->shop->id;
        $offset = Configuration::get('MPRODUCTS_PTS_OFFSET_LIMIT');
        $ptsTabsCount = $this->getCountOfPtsTabs($id_lang, $id_shop);

        if($offset) {
            if($offset < $ptsTabsCount)
                return $this->displayWarning(sprintf($this->l('Updated tabs: %1$d / %2$d'), $offset, $ptsTabsCount)); 

            if($offset == $ptsTabsCount)
                return $this->displayConfirmation(sprintf($this->l('Updated tabs: %1$d / %2$d'), $offset, $ptsTabsCount)); 
        }

    }

    public function submitRewriteTabs() {

        if(Tools::getValue('MPRODUCTTABS_PTS')) {

            set_time_limit(0);

            $id_lang = (int)$this->context->language->id;
            $id_shop = (int)$this->context->shop->id;

            $pts_tabs = $this->getPtsTabsNames($id_lang);

            $pts_tabs_active = Configuration::get('MPRODUCTS_PTS_TABS');

            if(!$pts_tabs_active) {

                foreach ($pts_tabs as $pts_tab) {

                    if(!empty($pts_tab['name'])) {
                        $tab = new Tabs();
                        $tab->name = $pts_tab['name'];
                        $tab->position = Tabs::count();
                        $tab->add();  
                    }
                }
            }

            Configuration::updateValue('MPRODUCTS_PTS_TABS', true);

            $limit = Configuration::get('MPRODUCTS_PTS_LIMIT');
            $offset = Configuration::get('MPRODUCTS_PTS_OFFSET_LIMIT');
            $totalTabs = $this->getCountOfPtsTabs($id_lang, $id_shop);

            $pts_tabs_contents = $this->getPtsTabContent($id_lang, $id_shop, $limit, $offset);

            foreach ($pts_tabs_contents as $pts_tab_content) {

                if($offset <= $totalTabs ) {

                    if(!empty($pts_tab_content['content'])) {
                        $tabsContent = new TabsContent();
                        $tabsContent->id_tab = (int)Tabs::getTabIdByName($pts_tab_content['name']);
                        $tabsContent->id_product = $pts_tab_content['id_product'];
                        $tabsContent->content = $pts_tab_content['content'];
                        $tabContent->is_open = 0;

                        if($tabsContent->add()) {
                            Configuration::updateValue('MPRODUCTS_PTS_OFFSET_LIMIT', Configuration::get('MPRODUCTS_PTS_OFFSET_LIMIT')+1);
                        } 
                    }
                }
            }  

            
        }

    }

    public function getPtsTabsNames($id_lang) {

        return Db::getInstance()->executeS('SELECT id_tab, name FROM `'._DB_PREFIX_.'pet_tab_lang` WHERE id_lang ='.(int)$id_lang);
    }

    public function getPtsTabContent($id_lang, $id_shop, $limit, $offset) {

        return Db::getInstance()->executeS('SELECT ptl.name ,ptcs.id_product, ptcl.content FROM `'._DB_PREFIX_.'pet_tab_content_lang` ptcl
            LEFT JOIN `'._DB_PREFIX_.'pet_tab_content_shop` ptcs ON (ptcl.id_tab_content = ptcs.id_tab_content)
            LEFT JOIN `'._DB_PREFIX_.'pet_tab_content` ptc ON (ptcs.id_tab_content = ptc.id_tab_content)
            LEFT JOIN `'._DB_PREFIX_.'pet_tab_lang` ptl ON (ptc.id_tab = ptl.id_tab)
            WHERE ptcl.id_lang = '.(int)$id_lang.' AND ptl.id_lang = ptcl.id_lang AND ptcl.id_shop = '.(int)$id_shop.' LIMIT '.(int)$limit.($offset ? ' OFFSET '.(int)$offset : ''));
    }

    public function getCountOfPtsTabs($id_lang, $id_shop) {

        return Db::getInstance()->getValue('SELECT COUNT(ptcl.content) as total FROM `'._DB_PREFIX_.'pet_tab_content_lang` ptcl
            WHERE ptcl.id_lang = '.(int)$id_lang.' AND ptcl.id_shop = '.(int)$id_shop);
    }


}
