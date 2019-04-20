<?php

namespace SCH60\Viewoutput;

class ViewoutputCli implements InterfaceViewoutput{
    
    public $echoMsg;
    
    public $stdOutMsg;
    
    public $stdErrMsg;
    
    public function __construct($echoMsg = "", $stdOutMsg = "", $stdErrMsg = ""){
        $this->echoMsg = $echoMsg;
        $this->stdOutMsg = $stdOutMsg;
        $this->stdErrMsg = $stdErrMsg;
    }
    
    public function render(){
        if(!empty($this->echoMsg)){
            echo $this->echoMsg;
        }
        
        if(!empty($this->stdOutMsg)){
            fwrite(STDOUT, $this->stdOutMsg);
        }
        
        if(!empty($this->stdErrMsg)){
            fwrite(STDERR, $this->stdErrMsg);
        }
    }
    
    
}