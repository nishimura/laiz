<?php
/**
 * HTTP Request Management Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\action;

use \laiz\builder\Singleton;

/**
 * HTTP Request Management Class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Request_Web implements Request
{

    /**
     * @var array $param Store request value.
     */
    private $params = array();

    private $ACTION_KEY;
    private $PATH_INFO_ACTION;

    private $actionName;

    /**
     * Store GET, POST and REQUEST_METHOD value.
     * @access public
     */
    function __construct(){
        if (isset($_SERVER['argv'], $_SERVER['argc'])){
            // ==todo== separate class or function
            // command line
            $argIndex = 1;
            $setAction = false;
            for ($i = 1; $i < $_SERVER['argc']; $i++){
                if (!$setAction){
                    // action is first argument excluding /^-/
                    if (!preg_match('/^-/', $_SERVER['argv'][$i])){
                        $this->add('action', $_SERVER['argv'][$i]);
                        $setAction = true;
                        continue;
                    }
                }

                $this->add('arg' . $argIndex, $_SERVER['argv'][$i]);
                $argIndex++;
            }
        }

        if (is_array($_REQUEST)){
            // web application
            foreach ($_REQUEST as $key => $value){
                $this->add($key, $value);
            }
        }

        if (isset($_SERVER['REQUEST_METHOD']))
            $this->add("REQUEST_METHOD", $_SERVER["REQUEST_METHOD"]);
    }

    /**
     * Store action key.
     *
     * @param string $name
     */
    public function setActionKey($name)
    {
        $this->ACTION_KEY = $name;
    }

    /**
     * Add variable.
     *
     * @param string $name
     * @param mixed $data
     * @access public
     */
    function add($name, $data){
        $this->params[$name] = $data;
    }

    /**
     * Return variable.
     *
     * @param string $name
     * @return mixed
     * @access public
     */
    function get($name){
        if (isset($this->params[$name])){
            return $this->params[$name];
        }else{
            return null;
        }
    }

    /**
     * Return all variable.
     *
     * @return mixed
     * @access public
     */
    function getAll(){
        return $this->params;
    }

    /**
     * Variables of PATH_INFO how many is made for Action is specified.
     *
     * @param int $num
     */
    public function setPathInfoAction($num)
    {
        $this->PATH_INFO_ACTION = $num;
        return $this;
    }

    /**
     * Return number of Action for PATH_INFO.
     * 
     * @return int
     */
    public function getPathInfoAction()
    {
        return $this->PATH_INFO_ACTION;
    }

    /**
     * Initialize action name.
     *
     * @access public
     */
    public function initActionName(){
        if (isset($this->params[$this->ACTION_KEY])){
            $action = $this->params[$this->ACTION_KEY];

        }elseif (isset($_SERVER['PATH_INFO'])){
            // parse PATH_INFO as action
            $requests = explode('/', $_SERVER['PATH_INFO']);
            if (isset($requests[$this->PATH_INFO_ACTION])
                && strlen(trim($requests[$this->PATH_INFO_ACTION])) > 0){
                $action = $requests[$this->PATH_INFO_ACTION];
            }else{
                $action = '';
            }
        }else{
            $action = '';
        }

        $this->actionName = $action;

        return $this;
    }

    public function setActionName($actionName)
    {
        $this->actionName = $actionName;
    }

    /**
     * Return action name.
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    public function setRequestsByConfigs(Array $configs){
        // PATH_INFOからプロパティ設定
        if ($this->PATH_INFO_ACTION && isset($_SERVER['PATH_INFO']) && isset($configs['pathinfo'])){
            $pathInfo = explode('/', $_SERVER['PATH_INFO']);
            foreach ($configs['pathinfo'] as $key => $value){
                if (isset($pathInfo[$value + $this->PATH_INFO_ACTION]))
                    $this->add($key, $pathInfo[$value + $this->PATH_INFO_ACTION]);
            }
        }

    }


}
