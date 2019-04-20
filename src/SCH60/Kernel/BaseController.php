<?php
/**
 * 页面简易分发器核心
 * Simple Control Handler 60
 *
 */

namespace SCH60\Kernel;

use DG24\Loader;
use SCH60\Viewoutput\ViewoutputHtml;
use SCH60\Viewoutput\ViewoutputJson;

class BaseController{
    
    /**
     *
     * @var Request
     */
    protected $request;
    
    /**
     *
     * @var Response
     */
    protected $response;
    
    protected $layout = "";
    
    public function __construct(){
        $this->request = Loader::$app->request;
        $this->response = Loader::$app->response;
    }
    
    public function beforeRunAction(){
        
    }
    
    public function __call($name, $args){
        if(0 === strpos($name, 'action')){
            return $this->response->error($name. '不存在', 404, "", "return");
        }
        
        throw new \BadMethodCallException('Controller Method Not Found:'. $name);
        
    }
    
    public function json($rst, $code = 0, $err = "", $errdetail = null){
        $viewoutput = new ViewoutputJson($rst, $code, $err, $errdetail);
        return $viewoutput;
    }
    
    public function httpCode($responseCode){
        Loader::$app->setOption("httpcode", $responseCode);
    }
    
    
    public function render($filepath = null, $data = array()){
        if(empty($filepath)){
            $router = Loader::$app->getRouter();
            $filepath = $router['router'];
        }
        
        $viewoutput = new ViewoutputHtml();
        
        if(!empty($this->layout)){
            $data['content'] = $viewoutput->renderHtml($filepath, $data, true);
            $viewoutput->data = $data;
            $viewoutput->filename = $this->layout;
        }else{
            $viewoutput->data = $data;
            $viewoutput->filename = $filepath;
        }
        
        return $viewoutput;
        
    }
    
    
}