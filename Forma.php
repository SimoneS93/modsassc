<?php

class ModSASSCForma {
    /**
     * @var ModuleCore
     */
    private $module;
    /**
     * @var string
     */
    private $title;
    /**
     * @var array
     */
    private $input_fields;
    /**
     * @var array
     */
    private $input_values;


    /**
     * 
     * @param Module $module
     * @param string $title
     */
    public function __construct(Module $module, $title) {
        $this->title = $title;
        $this->module = $module;
        $this->input_fields = array();
        //get form fields values from DB if they exists
        $this->input_values = Configuration::hasKey($this->key()) ? json_decode(Configuration::get($this->key()), TRUE) : array();
    }

    /**
     * Add a generic input to form
     * @param string $name
     * @param string $label
     * @param array $options
     * @return Forma
     */
    public function addInput($name, $label, array $options=array()) {
        $options['name'] = $this->nameify($name);
        $options['label'] = $this->module->l($label);
        array_push($this->input_fields, $options);
        return $this;
    }
    
    /**
     * Add a code editor input
     * @param string $name
     * @param string $label
     * @param array $options
     * @return Forma
     */
    public function addCodeEditor($name, $label, $lang='txt', array $options=array()) {
        $options['type'] = 'text';
        $options['class'] = sprintf('%s code-editor lang-%s', $options['class'], $lang);
        
        //include codemirror
        Tools::addCSS(dirname(__FILE__).'/css/codemirror.css');
        Tools::addJS(dirname(__FILE__).'/js/codemirror.js');
        Tools::addJS(dirname(__FILE__).sprintf('/js/mode/%s/%s.js', $lang, $lang));
        Tools::addJS(dirname(__FILE__).'/js/editor.js');
        
        return $this->addInput($name, $label, $options);
    }
    
    /**
     * Add a code viewer input
     * @param string $name
     * @param string $label
     * @param array $options
     * @return Forma
     */
    public function addCodeViewer($name, $label, $lang='txt', array $options=array()) {
        $options['class'] = sprintf('%s readOnly', $options['class']);
        return $this->addCodeEditor($name, $label, $lang, $options);
    }

    /**
     * Add switch input
     * @param string $name
     * @param string $label
     * @param array $options
     * @return Forma
     */
    public function addSwitch($name, $label, array $options=array()) {
        $defaults = array(
            'type' => 'switch',
            'is_bool' => TRUE,
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            )
        );
        
        return $this->addInput($name, $label, array_merge($options, $defaults));
    }

        /**
     * Add a text input to form
     * @param string $name
     * @param string $label
     * @param array $options
     * @return Forma
     */
    public function addText($name, $label, array $options=array()) {
        $options['type'] = 'text';
        return $this->addInput($name, $label, $options);
    }
    
    /**
     * Add a textarea input to form
     * @param string $name
     * @param string $label
     * @param array $options
     * @return Forma
     */
    public function addTextarea($name, $label, array $options=array()) {
        $options['type'] = 'textarea';
        return $this->addInput($name, $label, $options);
    }
    
    /**
     * Get input value
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getValue($name, $default=NULL) {
        $key = $this->nameify($name);
        if (isset($this->input_values[$key])) {
            return $this->input_values[$key];
        }
        return $default;
    }

    /**
     * Manual set input value
     * @param string $name
     * @param mixed $value
     * @return Forma
     */
    public function setValue($name, $value) {
        $key = $this->nameify($name);
        $this->input_values[$key] = $value;
        return $this;
    }
    
    /**
     * Display the form
     * @param string $identifier
     * @param Context $context
     * @return string
     */
    public function display($identifier, Context $context) {
        $module = $this->module;
        $helper = new HelperFormCore();
        $helper->show_toolbar = false;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $module;
        $helper->identifier = $identifier; #private
        $helper->submit_action = $this->key();
        $helper->currentIndex = $context->link->getAdminLink('AdminModules', false).'&configure='.$module->name.'&tab_module='.$module->tab.'&module_name='.$module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
                'base_url' => $context->shop->getBaseURL(),
                'fields_value' => $this->input_values,
                'languages' => $context->controller->getLanguages(),
                'id_language' => $context->language->id
        );
        
        $form = array(
            'legend' => array(
                    'title' => $module->l($this->title),
                    'icon' => 'icon-cogs'
            ),
            'input' => $this->input_fields,
            'submit' => array(
                    'title' => $module->l('Save'),
            )
        );
        
        return $helper->generateForm(array(array('form' => $form)));
    }
    
    /**
     * Update form values from POST request
     * @return void
     */
    public function updateFromPOST() {
        if ($this->isSubmit()) {
            foreach (Tools::getValue($this->namebase(), array()) as $name => $value) {
                $key = $this->nameify($name);
                $this->input_values[$key] = $value;
            }
            Configuration::updateValue($this->key(), json_encode($this->input_values));
        }
    }
    
    /**
     * Check if is form submission
     * @return true
     */
    public function isSubmit() {
        return Tools::isSubmit($this->key());
    }

    /**
     * Get form fields namespace
     * @return string
     */
    private function namebase() {
        return $this->module->name;
    }

    /**
     * Generate unique and identifiable name for inputs
     * @param string $name
     * @return string
     */
    private function nameify($name) {
        return sprintf('%s[%s]', $this->namebase(), $name);
    }
    
    /**
     * Get unique key for form
     */
    private function key() {
        return sprintf('form_%s_%d', $this->module->name, $this->module->id);
    }
}