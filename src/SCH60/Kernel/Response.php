<?php
/**
 * 页面简易分发器核心
 * Simple Control Handler 60
 *
 */

namespace SCH60\Kernel;

use DG24\Loader;
use SCH60\Viewoutput\ViewoutputCli;
use SCH60\Viewoutput\ViewoutputJson;
use SCH60\Viewoutput\ViewoutputHtml;


class Response{
    
    public function sendResponse($code = 200){
        static $headerStatusCode = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Large',
            415 => 'Unsupported Media Type',
            416 => 'Requested range not satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out',
        );
        
        if("200" != $code){
            if(isset($headerStatusCode[$code])){
                $headerLine = 'HTTP/1.1 '. $code. ' '. $headerStatusCode[$code];
            }else{
                $headerLine = 'HTTP/1.1 501 '. $headerStatusCode[501];
            }
            header($headerLine);
            
            /*
            if(Loader::$app->request->isCli()){
                fwrite(STDERR, $headerLine. PHP_EOL. "Date: ". gmdate("r"). PHP_EOL. PHP_EOL);
            }
            */
        }
    }
    
    public function error($tip, $responseCode = 403, $redirectUrl = "", $exitOrReturn = "exit"){
        
        Loader::$app->setOption("httpcode", $responseCode);
        
        $viewoutputInstance = null;
        
        if(Loader::$app->request->isAjax()){
            
            $viewoutputInstance = new ViewoutputJson(array(
                'error' => $tip,
                'redirectUrl' => $redirectUrl
            ), 1, $tip);
            
        }elseif(Loader::$app->request->isCli()){
            $viewoutputInstance = new ViewoutputCli("", "", $tip);
            
        }else{
            
            $tplsubdir = "_default";
            
            $ctrlViewFile = D_APP_DIR. '/tpl/'. strtolower(D_CONTROLLER_NAME). '/common/error_tip.php';
            if(file_exists($ctrlViewFile)){
                $tplsubdir = D_CONTROLLER_NAME;
            }
            
            $viewoutputInstance = new ViewoutputHtml("common/error_tip", array(
                'tip' => $tip,
                'redirectUrl' => $redirectUrl,
                'responseCode' => $responseCode,
                "isHtml" => false,
            ), $tplsubdir);
            
        }
        
        if($exitOrReturn == "exit"){
            $this->sendResponse($responseCode);
            $viewoutputInstance->render();
            exit();
        }
        
        return $viewoutputInstance;
        
    }
    
    public function msg($tip, $redirectUrl = "", $exitOrReturn = "exit"){
        
        $viewoutputInstance = null;
        
        if(Loader::$app->request->isAjax()){
            
            $viewoutputInstance = new ViewoutputJson(array(
                'tip' => $tip,
                'redirectUrl' => $redirectUrl
            ));
            
        }elseif(Loader::$app->request->isCli()){
            $viewoutputInstance = new ViewoutputCli($tip);
            
        }else{
            
            $tplsubdir = "_default";
            
            $ctrlViewFile = D_APP_DIR. '/tpl/'. strtolower(D_CONTROLLER_NAME). '/common/msg.php';
            if(file_exists($ctrlViewFile)){
                $tplsubdir = D_CONTROLLER_NAME;
            }
            
            $viewoutputInstance = new ViewoutputHtml("common/msg", array(
                'tip' => $tip,
                'redirectUrl' => $redirectUrl,
                "isHtml" => false,
            ), $tplsubdir);
            
        }
        
        
        if($exitOrReturn == "exit"){
            $viewoutputInstance->render();
            exit();
        }
        
        return $viewoutputInstance;
        
    }
    
    /**
     * 设置一个cookies（注意和php不同，多了一个参数，有一个参数有更改）
     * @param string $name
     * @param string $value
     * @param int $expire 过期时间。（与PHP不同）
     *     当为正数时，表示设置该cookies并规定其在多久后过期。比如：3600表示3600秒后将过期。
     *     当为负数时，表示：设置该cookie过期并删除
     *     当为0时，表示：设置为session cookies
     * @param boolean $usePre 是否使用pre？（与PHP不同，为新增参数）默认true
     * @param string $path
     * @param string $domain
     * @param string $secure
     * @param string $httponly
     */
    public function setCookie($name, $value, $expire = 0, $usePre = true, $path = null, $domain = null, $secure = false, $httponly = false){
        if($usePre){
            $name = Loader::config("Base", 'cookiePre'). $name;
        }
        
        if($expire > 0){
            $expire = time() + $expire;
        }elseif($expire < 0){
            $value = "";
            $expire = 1;
        }
        
        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
    
    public function session_set($idx, $val){
        if(defined('D_DISABLE_SESSION')){
            return null;
        }
        Loader::$app->request->session_get('test');
        $idx = Loader::config("Base", 'cookiePre'). $idx;
        $_SESSION[$idx] = $val;
    }
    
    public function redirect($url){
        $this->sendResponse(302);
        header("Location: ". $url);
        exit();
    }
    
}
