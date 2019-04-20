<?php
/**
 * 页面简易分发器核心
 * Simple Control Handler 60 
 *
 */

namespace SCH60\Kernel;

use DG24\Loader;

class StrHelper{
    
    static public function O($s){
        return htmlspecialchars($s);
    }
    
    static public function url($route = "", $param = null, $absolute = true){
        $url = '';
        
        if(!empty($param)){
            if(!is_array($param)){
                parse_str($param, $param_new);
                $param = $param_new;
            }
        }else{
            $param = array();
        }
        
        if(!empty($route)){
            $param = array_merge(array('r' => $route), $param);
        }
        
        if(!empty($param)){
            $url .=  '?'. http_build_query($param);
        }
        
        $url = Loader::$app->request->getEntryFilename(). $url;
        
        if($absolute){
            $url = Loader::$app->request->getBaseUrl(). '/'. $url;
        }
        
        return $url;
        
    }
    
    static public function urlStatic($str){
        return Loader::$app->request->getBaseUrl(). '/'. $str;
    }
    
    
    
}
