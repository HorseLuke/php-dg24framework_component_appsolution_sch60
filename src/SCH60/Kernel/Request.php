<?php
/**
 * 页面简易分发器核心
 * Simple Control Handler 60
 *
 */

namespace SCH60\Kernel;

use DG24\Loader;

class Request{
    
    protected $ip = null;
    
    protected $entryFilename = null;
    
    protected $baseUrl = null;
    
    protected $isAjax = null;
    
    protected $isCli = null;
    
    protected $isRobot = null;
    
    /**
     * 获取一个cookies
     * @param string $name
     * @param string $usePre 是否使用pre？默认true
     * @return mixed
     */
    public function getCookie($name, $usePre = true){
        if($usePre){
            $name = Loader::config("Base", 'cookiePre'). $name;
        }
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }
    
    public function input($arr, $idx, $default = null){
        if(!is_array($idx)){
            return isset($arr[$idx]) ? $arr[$idx] : $default;
        }
        
        $return = array();
        foreach ($idx as $i){
            $return[$i] = isset($arr[$i]) ? $arr[$i] : $default;
        }
        
        return $return;
    }
    
    public function isCli(){
        if(null !== $this->isCli){
            return $this->isCli;
        }
        
        if (php_sapi_name() == "cli") {
            $this->isCli = true;
        } else {
            $this->isCli = false;
        }
        
        return $this->isCli;
        
    }
    
    public function isRobot(){
        
        if($this->isRobot !== null){
            return $this->isRobot;
        }
        
        if(empty($_SERVER['HTTP_USER_AGENT']) || preg_match("/bot|spider|crawl|nutch|lycos|robozilla|slurp|search|seek|archive|curl/i", $_SERVER['HTTP_USER_AGENT'])){
            $this->isRobot = true;
        }else{
            $this->isRobot = false;
        }
        
        return $this->isRobot;
        
    }
    
    /**
     * 获取URL入口文件名
     * @return string
     */
    public function getEntryFilename(){
        if(null === $this->entryFilename){
            $this->entryFilename = basename(D_ENTRY_FILE);
        }
        return $this->entryFilename;
    }
    
    /**
     * 获取基础URL路径
     * @return string
     */
    public function getBaseUrl(){
        if(null === $this->baseUrl){
            $this->baseUrl = ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') ? 'http' : 'https'). '://'. $_SERVER['HTTP_HOST']. substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
        }
        
        return $this->baseUrl;
    }
    
    /**
     * 获取用户ipv4
     * @return string
     */
    public function getIp(){
        if(null !== $this->ip){
            return $this->ip;
        }
        
        $ip = $_SERVER['REMOTE_ADDR'];
        
        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
            $this->ip = $ip;
        }else{
            $this->ip = '0.0.0.0';
        }
        
        return $this->ip;
    }
    
    
    protected $is_session_started = false;
    public function session_get($idx = null, $def = null){
        if(defined('D_DISABLE_SESSION')){
            return null;
        }
        $prefix = Loader::config("Base", 'cookiePre');
        
        if(!$this->is_session_started){
            session_start();
            if(empty($_SESSION[$prefix. '_authses']) || $this->getCookie('sessionauth') != $_SESSION[$prefix. '_authses']){
                if(!empty($_SESSION)){
                    session_regenerate_id(true);
                }
                $_SESSION = array();
                $_SESSION[$prefix. '_authses'] = md5('sess_asdfasdf_'. uniqid(). "_". Loader::config("Base", 'hashSalt'));
                Loader::$app->response->setCookie('sessionauth', $_SESSION[$prefix. '_authses'], 0,  true, null, null,  false, true);
            }
            $this->is_session_started = true;
        }
        
        if(null == $idx){
            return $_SESSION;
        }
        $idx = $prefix. $idx;
        return isset($_SESSION[$idx]) ? $_SESSION[$idx] : $def;
    }
    
    /**
     * 是否处于ajax中？
     * @return boolean
     */
    public function isAjax(){
        if(null !== $this->isAjax){
            return $this->isAjax;
        }
        
        $this->isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        return $this->isAjax;
    }
    
    /**
     * 命令行解析
     * @return array
     */
    public function parseCliArgv(){
        global $argv;
        
        $argvFinal = $argv;
        
        foreach($argv as $a){
            preg_match('/^[-]+([a-z0-9_-]+)=(.*)$/i', $a, $match);
            if(isset($match[1]) && !is_numeric($match[1])){
                $argvFinal[$match[1]] = $match[2];
            }
        }
        
        return $argvFinal;
        
    }
    
    /**
     * 获取用户的GET
     * @param array|string $idx
     * @param string $useExtraGet
     * @param string $def
     * @return mixed
     */
    public function get($idx = null, $def = null){
        if(is_array($idx)){
            $return = array();
            foreach($idx as $i){
                $return[$i] = isset($_GET[$i]) ? $_GET[$i] : $def;
            }
            return $return;
        }elseif(!empty($idx)){
            return isset($_GET[$idx]) ? $_GET[$idx] : $def;
        }else{
            return $_GET;
        }
    }
    
    /**
     * 获取用户的post
     * @param string $idx
     * @param string $def
     * @return mixed
     */
    public function post($idx = null, $def = null){
        if(is_array($idx)){
            $return = array();
            foreach($idx as $i){
                $return[$i] = isset($_POST[$i]) ? $_POST[$i] : $def;
            }
            return $return;
        }elseif(!empty($idx)){
            return isset($_POST[$idx]) ? $_POST[$idx] : $def;
        }else{
            return $_POST;
        }
    }
    
    
    
}

