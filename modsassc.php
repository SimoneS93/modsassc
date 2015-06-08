<?php

require_once 'Forma.php';
require_once 'scss.inc.php';

class ModSASSC extends Module {
    /**
     *
     * @var Forma
     */
    private $form;
    
    public function __construct($name = null, \Context $context = null) {
        $this->name = 'modsassc';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Simone Salerno';
        $this->need_instance = 0;
        $this->bootstrap = 1;

        parent::__construct($name, $context);

        $this->displayName = $this->l('SASSC');
        $this->description = $this->l('An online SASS editor and compiler.');
        
        $this->form = new ModSASSCForma($this, 'SASS compiler');
        $this->form->addCodeEditor('editor', 'SASS editor', 'css');
        $this->form->addCodeViewer('viewer', 'CSS viewer', 'css');
    }
    
    /**
     * Installer
     * @return bool
     */
    public function install() {
        return parent::install() && $this->registerHook('displayHeader');
    }
    
    /**
     * Uninstaller
     * @return bool
     */
    public function uninstall() {
        return $this->unregisterHook('displayHeader') && parent::uninstall();
    }
    
    /**
     * Inject CSS in page head
     */
    public function hookDisplayHeader() {
        Tools::addCSS($this->sassfile());
    }

    /**
     * Show configuration form
     * @return string
     */
    public function getContent() {
        $this->form->updateFromPOST();
        $this->compileSASS();
        
        return $this->form->display($this->identifier, $this->context);
    }
    
    /**
     * Compile SASS source to CSS
     */
    private function compileSASS() {
        if ($this->form->isSubmit()) {
            $source = $this->form->getValue('editor');
            //we have some issues with newlines
            //unescape input
            $source = str_replace('\n', "\n", $source);
            $compiler = new scssc();
            $compiler->addImportPath(implode(DS, array(dirname(__FILE__), 'scss', 'source')));
            $compiled = $compiler->compile($source);
            $compiled = preg_replace('/(@charset "UTF-8";\n*)+/', '', $compiled);
            
            file_put_contents($this->sassfile(), $compiled);
            
            //escape output
            $this->form->setValue('editor', str_replace("\n", '\n', $source));
            $this->form->setValue('viewer', str_replace("\n", '\n', $compiled));
        }
    }
    
    /**
     * Get SASS output file
     * @return string
     */
    private function sassfile() {
        return implode(DS, array(dirname(__FILE__), 'scss', 'compiled.css'));
    }
}