<?php

namespace SCH60\Viewoutput;

class ViewoutputHtml implements InterfaceViewoutput{
    
    public $filename;
    
    public $data = array();
    
    public $tplSubdir = "";
    
    public function __construct($filename = "", array $data = array(), $tplSubdir = D_CONTROLLER_NAME){
        $this->filename = $filename;
        $this->data = $data;
        $this->tplSubdir = $tplSubdir;
    }
    
    public function render(){
        $this->renderHtml($this->filename, $this->data, false, $this->tplSubdir);
    }
    
    public function renderHtml($__filename, $__data = array(), $____return = false, $____tplSubdir = D_CONTROLLER_NAME){
        if($____return){
            ob_start();
            ob_implicit_flush(false);
        }
        extract($__data, EXTR_SKIP);
        require  D_APP_DIR. '/tpl/'. strtolower($____tplSubdir. '/'. $__filename). '.php';
        if($____return){
            return ob_get_clean();
        }
    }
    
}