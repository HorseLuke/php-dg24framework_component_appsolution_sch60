<?php

namespace SCH60\Viewoutput;

class ViewoutputJson implements InterfaceViewoutput{
    
    public $data = null;
    
    public function __construct($rst, $code = 0, $err = "", $errdetail = null){
        $this->data = array('rst' => $rst, 'code' => $code,);
        if($code != 0){
            $this->data['err'] = $err;
            $this->data['errdetail'] = $errdetail;
        }
    }
    
    public function render(){
        header('Content-Type: application/json;charset=UTF-8');
        echo json_encode($this->data);
    }
    
}