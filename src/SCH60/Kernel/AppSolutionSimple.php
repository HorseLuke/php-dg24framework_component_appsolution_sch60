<?php
namespace SCH60\Kernel;

use DG24\SolutionBare\App;
use DG24\Loader;
use DG24\StdTrait\PropertyOptionStdTrait;
use SCH60\Viewoutput\InterfaceViewoutput;

class AppSolutionSimple extends App{
    
    use PropertyOptionStdTrait;
    
    /**
     * route入口。必有：subapp，controller，action
     * @var array
     */
    protected $routeEntry = array();
    
    protected $opt2cli_env_simulate_as_web = true;
    
    protected $opt2httpcode = 200;
    
    protected $opt2exit_after_view_output = "exit";
    
    /**
     * 
     * @var Request
     */
    public $request;
    
    /**
     * 
     * @var Response
     */
    public $response;
    
    public function __construct(array $cfg = null){
        parent::__construct($cfg);
        $this->initRequestResponse();
    }
    
    protected function initRequestResponse(){
        $this->request = new Request();
        $this->response = new Response();
    }
    
    public function run(){
        if($this->opt2cli_env_simulate_as_web == true && $this->request->isCli() == true){
            $this->cliEnvSimulateAsWeb();
        }
        $this->createDispatchEnvironment();
        $this->runhook();
        $this->dispatchController();
    }
    
    
    protected function cliEnvSimulateAsWeb(){
        global $_GET;
        $_GET = $this->request->parseCliArgv();
        $_SERVER['HTTP_HOST'] = 'www.cli.com';
        $_SERVER['PHP_SELF'] = '/test/index.php';
        $_SERVER['HTTP_USER_AGENT'] = "";
        $_SERVER['REQUEST_METHOD'] = "GET";
    }
    
    protected function runhook(){
        $hookCfg = Loader::config('hooks');
        if(!is_array($hookCfg)){
            return ;
        }
        
        foreach($hookCfg as $hook){
            if($hook instanceof \Closure){
                $hook();
            }else{
                $this->getInstance($hook[0])->{$hook[1]}();
            }
        }
    }
    
    public function createDispatchEnvironment(){
        $r = isset($_POST['r']) ? $_POST['r'] : (isset($_GET['r']) ? $_GET['r'] : '');
        if(empty($r)){
            $r = Loader::config("Base", 'defaultRoute');
        }
        $this->routeEntry = $this->createRouterEntry($r);
        
        
    }
    
    
    public function dispatchController(){
        
        $controlerName = $this->routeEntry['subapp']. '\\'. D_CONTROLLER_NAME. '\\'. $this->routeEntry['controller'];
        if(!class_exists($controlerName)){
            return $this->dispatchViewoutput(
                $this->response->error($controlerName. '未定义', 404, "", "return")
            );
        }
        
        $controller = new $controlerName();
        $controller->beforeRunAction();
        
        $actionName = 'action'. $this->routeEntry['action'];
        $return = $controller->$actionName();
        
        $this->dispatchViewoutput($return);
    }
    
    
    public function dispatchViewoutput($return){
        
        do{
            
            if($this->opt2httpcode != 200){
                $this->response->sendResponse($this->opt2httpcode);
            }
            
            if(is_scalar($return)){
                echo $return;
                break;
            }
            
            if($return instanceof InterfaceViewoutput){
                $return->render();
                break;
            }
            
            throw new \InvalidArgumentException("Unsupported Controller Return Result");
            
        }while(false);

        if($this->opt2exit_after_view_output == "exit"){
            exit();
        }
        
    }
    
    /**
     * 检查路由是否符合格式，并创建最终路由入口。格式如下：
     * subapp：仅允许第一个为字母，其余为“字母+数字+下划线”的形式。如：aa，aa0
     *     最终格式化为首字母大写的app存放文件夹路径，如：Aa，Aa0
     * controller：仅允许第一个为字母，其余为“字母+数字+下划线”的形式。如：aa，ab_c0
     *     最终格式化为首字母大写大写的controller名称，如：Aa，Ab_C0
     * action：仅允许“字母+数字+下划线”的形式。如：aa，0bc
     *     最终格式化为首字母大写的action名称。如：Aa，0bc
     * @param string $route
     * @return array 解析的RouterEntry。必有
     */
    public function createRouterEntry($route){
        
        $res = null;
        
        if(!preg_match('/^(?P<subapp>[a-z][a-z0-9_]*)\/(?P<controller>[a-z][a-z0-9_]*)\/(?P<action>[a-z0-9_]+)$/iU', $route, $res)){
            return $this->dispatchViewoutput(
                $this->response->error('路由错误', 406, "", "return")
            );
        }
        
        $return = array();
        $return['subapp'] = ucfirst($res['subapp']);
        $return['controller'] = ucfirst($res['controller']);
        $return['action'] = ucfirst($res['action']);
        $return['router'] = $res['subapp']. '/'. $res['controller']. '/'.$res['action'];
        $return['runRouter'] = $return['subapp']. '/'. $return['controller']. '/'.$return['action'];
        
        return $return;
        
    }
    
    public function getRouter(){
        return $this->routeEntry;
    }
    
    
}