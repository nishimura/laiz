<?php
/**
 * Container Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2009 Satoshi Nishimura
 */

/**
 * コンテナクラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Container
{
    /** @var Laiz_Container */
    static private $instance;

    /** @var string project default components list file */
    private $COMPONENTS_INI;

    /** @var string project directory */
    private $COMPONENTS_DIR;

    /**
     * @var Object Instances of component.
     * @access private
     */
    private $components = array();

    /** @var array $interfaces[interfaceName][priority] = component */
    private $interfaces = array();

    /** @var array $priorities[componentName] = priority */
    private $priorities = array();

    /** @var int $sortCounter[interfaceName] = counter */
    private $sortCounter = array();

    /** @var array */
    private $ignoreInterfaces = array();
    /**
     * 初期化処理
     *
     * @access public
     */
    private function __construct($args){
        $configs = Laiz_Configure::get('Laiz_Container');
        $this->COMPONENTS_INI = $configs['COMPONENTS_INI'];
        $this->COMPONENTS_DIR = $configs['COMPONENTS_DIR'];
        $this->ignoreInterfaces = get_declared_interfaces();
    }

    /**
     * cloneの禁止
     *
     * @access public
     */
    public function __clone(){
        trigger_error('Clone is not allowed', E_USER_ERROR);
    }

    /**
     * コンテナインスタンスを返却
     *
     * @return Laiz_Container
     * @access public
     */
    static public function getInstance($args = array()){
        if (self::$instance === null){
            $class = __CLASS__;
            self::$instance = new $class($args);

            self::$instance->init();
        }

        return self::$instance;
    }

    public function init()
    {
        $frameworkIniFile = 'Laiz/components.ini';
        $this->parseComponentsIni($frameworkIniFile);

        $projectIniFile = $this->COMPONENTS_INI;
        if (file_exists($projectIniFile))
            $this->parseComponentsIni($projectIniFile);
        return $this;
    }

    /**
     * 初期化処理
     *
     * @access public
     */
    public function clean(){
        $this->components = array();
        $this->interfaces  = array();
        $this->priorities  = array();
        $this->sortCounter = array();
        return $this;
    }

    /**
     * インターフェースを削除する
     *
     * @param string $interface
     * @return Laiz_Container
     */
    public function deleteInterface($interface)
    {
        unset($this->interfaces[$interface]);
        unset($this->sortCounter[$interface]);

        return $this;
    }

    /**
     * インターフェースを登録する
     *
     * @param Object $component
     */
    public function registerInterface($component, $priority)
    {
        $interfaces = class_implements($component);

        if (!is_array($interfaces))
            return true;

        foreach ($interfaces as $interface){
            // PHPコアのインターフェースはパスする
            if (in_array($interface, $this->ignoreInterfaces))
                continue;

            $p = $this->getUnusedPriority($component, $interface, $priority);
            // 既に登録済みの場合はパスする
            if ($p === false)
                continue;

            $this->interfaces[$interface][$p] = $component;
            if (isset($this->sortCounter[$interface]))
                $this->sortCounter[$interface]++;
            else
                $this->sortCounter[$interface] = 1;
        }
    }

    /**
     * コンポーネントの作成
     *
     * @param string $componentName
     * @param int $priority
     * @return Laiz_Container
     * @access public
     */
    function create($componentName, $priority = 100){
        // コンポーネントが既に存在する場合はここでリターン
        if (is_object($this->get($componentName))){
            return $this->get($componentName);
        }

        $obj = $this->newInstance($componentName, $priority);
        $this->register($obj, $componentName, $priority);
        return $obj;
    }

    /**
     * コンポーネントを作成する
     *
     * @param string $componentName
     * @param int $priority
     * @return Object
     */
    public function newInstance($componentName, $priority = 100)
    {
        $iniFile = str_replace('_', '/', $componentName);
        $parser = new Laiz_Parser_Ini_Simple();
        $configs = $parser->parseIniFile("$iniFile.ini", true);
        if (isset($configs['main']['class']))
            $className = $configs['main']['class'];
        else
            $className = $componentName;

        // コンポーネントの登録
        // インスタンスを作成し変数を設定する前に
        // コンテナに格納しないとループする
        if (!class_exists($className)){
            // クラス定義エラー
            trigger_error("class $className is not defined.", E_USER_ERROR);
            return null;
        }

        // コンストラクタ引数の取得
        try{
            $refClass = new ReflectionClass($className);
            $method = $refClass->getConstructor();
            if ($method !== null)
                $params = $this->getMethodParamObjectsByReflection($className, $method);
            else
                $params = array();
        }catch (ReflectionException $e){
            $params = array();
        }
        
        if (isset($configs['main']['constructor'])){
            $params = array_merge($params, $this->parseArguments($configs['main']['constructor'], $configs['main']));
        }

        // 優先度は生成するコンストラクタからも利用したいので
        // コンストラクタを呼び出す前に登録する
        $this->priorities[$className] = $priority;

        // オブジェクトの生成と登録
        $component = $this->createObject($className, $params);

        $this->initClass($component, $configs);

        if (isset($configs['main']['name']))
            $this->alias($componentName, $configs['main']['name'], $component);

        return $component;
    }

    /**
     * クラス名と関数名から引数のオブジェクトを生成して返却
     *
     * @param string $className
     * @param string $method
     * @return Object[]
     * @access public
     */
    public function getMethodParamObjects($className, $method){
        try{
            $method = new ReflectionMethod($className, $method);
        }catch (ReflectionException $e){
            return array();
        }

        return $this->getMethodParamObjectsByReflection($className, $method);
    }

    /**
     * クラス名と関数名から引数のオブジェクトを生成して返却
     *
     * @param string $className
     * @param ReflectionMethod
     * @return Object[]
     * @access private
     */
    public function getMethodParamObjectsByReflection($className, ReflectionMethod $method){
        $params = array();
        $paramRefs = $method->getParameters();
        foreach ($paramRefs as $paramRef){
            try{
                $obj = $paramRef->getClass();
                if (!is_object($obj)){
                    if (!$paramRef->isArray())
                        continue;

                    $name = $paramRef->getName();
                    $inflector = $this->create('Laiz_Util_Inflector');
                    $objs = $this->getComponents($inflector->classify($name));
                    // if (count($objs) > 0)
                        $params[] = $objs;
                    continue;
                }

                $name = $obj->getName();
            }catch (ReflectionException $e){
                $tmp = explode(' ', $e->getMessage(), 3);
                // "Class $name does not exist"からクラス名を取得
                $name = $tmp[1];
            }

            if (is_object($component = $this->get($name))){
                $params[] = $component;
                    
            }elseif ($component = $this->create($name)){
                $params[] = $component;
            }else{
                trigger_error("Failed to create object [$name] by type hinting.", E_USER_WARNING);
            }

        }

        return $params;
    }

    /**
     * オブジェクトの生成
     *
     * @param string $className
     * @param array $args
     * @return Object
     * @access public
     */
    public function createObject($className, $args = array()){
        $c = count($args);

        $obj = null;
        switch ($c){
        case 0:
            $obj = new $className();
            break;
        case 1:
            $obj = new $className($args[0]);
            break;
        case 2:
            $obj = new $className($args[0], $args[1]);
            break;
        case 3:
            $obj = new $className($args[0], $args[1], $args[2]);
            break;

        default:
            try{
                $r = new ReflectionClass($className);
                $obj = call_user_func_array(array($r, 'newInstance'), $args);
            }catch (ReflectionException $e){
                trigger_error("Failed to create object [$className] by more than 4 arguments."
                          , E_USER_WARNING);
            }
        }

        return $obj;
    }

    /**
     * メソッドを実行して結果を返却する
     *
     * @param mixed $obj オブジェクトまたはクラス名
     * @param string $method
     * @param array $args
     * @return mixed
     * @access public
     */
    public function execMethod($obj, $method, $fromArgs = array()){
        if (!method_exists($obj, $method)){
            trigger_error("Undefined $method method in ".get_class($obj)." class.", E_USER_ERROR);
            return null;
        }

        $objects = $this->getMethodParamObjects($obj, $method);
        
        $args = array();
        foreach ($objects as $object)
            $args[] = $object;
        foreach ($fromArgs as $fromArg)
            $args[] = $fromArg;
        return call_user_func_array(array($obj, $method), $args);
    }

    /**
     * 利用されていない優先度を返却する
     *
     * @param Object $component 
     * @param string $interface
     * @param int $priority
     * @return int|false 既に登録されているコンポーネントならfalse
     */
    private function getUnusedPriority($component, $interface, $priority)
    {
        if (isset($this->interfaces[$interface][$priority])){
            if ($this->interfaces[$interface][$priority] === $component){
                return false;
            }else
                return $this->getUnusedPriority($component, $interface, $priority + 1);
        }else{
            return $priority;
        }
    }

    /**
     * コンテナにコンポーネントをセット
     *
     * @param Object $component
     * @param string $name
     * @param int $priority
     * @access public
     * @return bool
     */
    public function register($component, $name = '', $priority = 100){
        if (!is_object($component)){
            return false;
        }

        if ($name == ''){
            $name = get_class($component);
        }
        $this->components[$name] = $component;

        return true;
    }

    /**
     * Setting alias of component name.
     *
     * @param string $sorce
     * @param string $alias
     * @return bool
     */
    public function alias($source, $dest, $component = null)
    {
        if ($component === null){
            $component = $this->get($source);
            if (!$component)
                return false;
        }

        $this->components[$dest] = $component;
        return true;
    }

    /**
     * コンポーネントインスタンスの返却
     *
     * @param string $name
     * @return Object
     * @access public
     */
    public function get($name){
        if (isset($this->components[$name])){
            $component = $this->components[$name];
        }else if ($name === __CLASS__){
            // 自分自身の返却
            $component = $this;
        }else{
            $component = null;
        }

        return $component;
    }

    /**
     * Laiz_Container::getへのエイリアス
     *
     * @param string $name
     * @return Object
     */
    public function getComponent($name)
    {
        return $this->get($name);
    }

    /**
     * クラスインスタンスの初期設定
     *
     * @param Object $class
     * @param string[] $configs
     * @return Object
     * @access public
     */
    public function initClass($class, $configs){
        // 初期値設定
        // 設定ファイルから変数のセット
        if (isset($configs['property'])){
            $this->setPropertiesByConfigs($class, $configs['property']);
        }

        // メソッドの実行
        if (isset($configs['method'])){
            $this->execMethodsByIniFile($class, $configs['method']);
        }
    }

    /**
     * iniファイルからクラスプロパティを設定
     *
     * @param Object $class 解析するオブジェクト
     * @param string[] $configs プロパティの設定配列
     * @access private
     */
    private function setPropertiesByConfigs($class, $configs){
        if (!is_array($configs))
            return;
        
        $vars = get_object_vars($class);

        foreach ($configs as $name => $var){
            if (array_key_exists($name, $vars)){
                $class->$name = $var;
            }
        }
    }

    /**
     * iniファイルからのメソッド実行
     *
     * @param Object $class
     * @param string[] $configs
     * @return bool
     * @access private
     */
    private function execMethodsByIniFile($class, $configs){
        if (!is_object($class) || !is_array($configs)){
            return false;
        }

        foreach ($configs as $method => $args){
            if (substr(trim($method), 0, 1) === '$') // 変数
                continue;

            if (!method_exists($class, $method)){
                trigger_error("クラス ".get_class($class)." にメソッド $method がありません", E_USER_ERROR);
                return false;
            }

            $objs = $this->getMethodParamObjects(get_class($class), $method);
            $args = array_merge($objs, $this->parseArguments($args, $configs));
            
            call_user_func_array(array($class, $method), $args);

        }

        return true;
    }

    /**
     * コンストラクタ、メソッドインジェクションの引数を解析して返却する
     *
     * @param string $args
     * @param array $configs
     * @param array $stopper ループ回避用解析済み変数
     * @return array
     */
    private function parseArguments($args, $configs, $stopper = array()){
        if (strlen(trim($args)) != 0){
            $ret = explode(',', $args);
            $ret = array_map('trim', $ret);

            // 変数の解析
            foreach ($ret as $key => $value){
                if (preg_match('/^\$[a-zA-Z_]*[a-zA-Z0-9_]+$/', $value))
                    $ret[$key] = $this->parseArgumentValue($value, $configs, $stopper);
            }

        }else{
            $ret = array();
        }

        return $ret;
    }

    /**
     * iniファイルのメソッド引数を解析して結果を返却する
     *
     * @param string $value
     * @param array $configs
     * @param array $stopper
     * @return mixed
     */
    private function parseArgumentValue($value, $configs, $stopper){
        if (in_array($value, $stopper))
            trigger_error("Configuration variable [$value] is looped.", E_USER_ERROR);
        $stopper[] = $value;
        
        if (!isset($configs[$value]))
            trigger_error("Variable [$value] is not defined in configuration file.", E_USER_WARNING);

        $parts = explode('.', $configs[$value]);

        if (strlen($parts[0]) === 0)
            trigger_error("Value of variable [$value] is not defined.", E_USER_WARNING);

        if (!is_object($component = $this->getComponent($parts[0]))){
            if (!$this->create($parts[0])){
                trigger_error("Failed to create object [ $parts[0] ] used by variable [$value] of configuration file.", E_USER_WARNING);
            }else{
                $component = $this->getComponent($parts[0]);
            }
        }

        // コンポーネントのインスタンスを返却
        if (!isset($parts[1]))
            return $component;

        // メソッドの結果を返却
        if (!isset($parts[2]))
            return $component->{$parts[1]};

        // メソッド引数がある場合の実行結果を返却
        $args = $this->parseArguments($parts[2], $configs, $stopper);
        return call_user_func_array(array($component, $parts[1]), $args);
    }

    /**
     * コンポーネントのiniファイルを解析して登録する
     *
     * @param string $iniFile
     */
    public function parseComponentsIni($iniFile, $defPriority = 100)
    {
        $configs = parse_ini_file($iniFile);

        foreach ($configs as $className => $priority){
            if (substr($className, -4) === '.ini'){
                $this->parseComponentsIni($this->COMPONENTS_DIR . $className, $priority);
                continue;
            }

            if (!$priority)
                $priority = $defPriority;

            $obj = $this->create($className, $priority);
            if ($obj instanceof Laiz_Component)
                $this->registerInterface($obj, $priority);
        }
    }

    /**
     * インターフェース名を受け取り、それを実装しているオブジェクトの配列を返却する。
     * 
     * @param string $interface
     * @return array
     */
    public function getComponents($interface)
    {
        if (isset($this->interfaces[$interface])){
            if ($this->sortCounter[$interface] > 1){
                ksort($this->interfaces[$interface], SORT_NUMERIC);
                $this->sortCounter[$interface] = 1;
            }
            return $this->interfaces[$interface];

        }else{
            return array();
        }
    }

    /**
     * コンポーネント名を受け取り優先度を返却する
     *
     * @param mixed $className
     * @return int
     */
    public function getPriority($componentName)
    {
        if (is_object($componentName))
            $componentName = get_class($componentName);

        if (isset($this->priorities[$componentName]))
            return $this->priorities[$componentName];
        else
            return 0;
    }
}
