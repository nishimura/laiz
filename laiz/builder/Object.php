<?php
/**
 * Object Builder Class File.
 *
 * ==TODO== Change file and class name more better.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\builder;

use \laiz\parser\Ini_Simple;
use \ReflectionClass;
use \ReflectionMethod;
use \ReflectionException;
use \laiz\util\Inflector;

/**
 * Object Builder Class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Object
{
    /**
     * @var Object Instances of component.
     * @access private
     */
    private $components = array();

    private function __clone(){
        trigger_error('Clone is not allowed', E_USER_ERROR);
    }

    /**
     * Build a object.
     *
     * @param string $componentName
     * @param int $priority
     * @return Object
     */
    static public function build($componentName)
    {
        $componentName = str_replace('.', '\\', $componentName);
        $iniFile = str_replace('_', '/', $componentName);
        $iniFile = str_replace('\\', '/', $iniFile);
        $parser = new Ini_Simple();
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
                $params = self::getMethodParamObjectsByReflection($className, $method);
            else
                $params = array();
        }catch (ReflectionException $e){
            $params = array();
        }

        if (isset($configs['main']['constructor'])){
            $params = array_merge($params, self::parseArguments($configs['main']['constructor'], $configs['main']));
        }

        // オブジェクトの生成と登録
        $component = self::createObject($className, $params);

        self::initObject($component, $configs);

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
    static public function getMethodParamObjects($className, $method){
        try{
            $method = new ReflectionMethod($className, $method);
        }catch (ReflectionException $e){
            return array();
        }

        return self::getMethodParamObjectsByReflection($className, $method);
    }

    /**
     * クラス名と関数名から引数のオブジェクトを生成して返却
     *
     * @param string $className
     * @param ReflectionMethod
     * @return Object[]
     * @access private
     */
    static public function getMethodParamObjectsByReflection($className, ReflectionMethod $method){
        $params = array();
        $paramRefs = $method->getParameters();
        $container = Container::getInstance();
        foreach ($paramRefs as $paramRef){
            try{
                $obj = $paramRef->getClass();
                if (!is_object($obj)){
                    if (!$paramRef->isArray())
                        continue;

                    $name = $paramRef->getName();
                    $objs = $container->getComponents(Inflector::classify($name));
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

            if (is_object($component = $container->get($name))){
                $params[] = $component;
            }elseif ($component = $container->create($name)){
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
    static public function createObject($className, $args = array()){
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
    static public function execMethod($obj, $method, $fromArgs = array()){
        if (!method_exists($obj, $method)){
            trigger_error("Undefined $method method in ".get_class($obj)." class.", E_USER_ERROR);
            return null;
        }

        $objects = self::getMethodParamObjects($obj, $method);
        
        $args = array();
        foreach ($objects as $object)
            $args[] = $object;
        foreach ($fromArgs as $fromArg)
            $args[] = $fromArg;
        return call_user_func_array(array($obj, $method), $args);
    }

    /**
     * クラスインスタンスの初期設定
     *
     * @param Object $class
     * @param string[] $configs
     * @return Object
     * @access public
     */
    static public function initObject($class, $configs){
        // 初期値設定
        // 設定ファイルから変数のセット
        if (isset($configs['property'])){
            self::setPropertiesByConfigs($class, $configs['property']);
        }

        // メソッドの実行
        if (isset($configs['method'])){
            self::execMethodsByIniFile($class, $configs['method']);
        }
    }

    /**
     * iniファイルからクラスプロパティを設定
     *
     * @param Object $class 解析するオブジェクト
     * @param string[] $configs プロパティの設定配列
     * @access private
     */
    static private function setPropertiesByConfigs($class, $configs){
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
    static private function execMethodsByIniFile($class, $configs){
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

            $objs = self::getMethodParamObjects(get_class($class), $method);
            $args = array_merge($objs, self::parseArguments($args, $configs));
            
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
    static private function parseArguments($args, $configs, $stopper = array()){
        if (strlen(trim($args)) != 0){
            $ret = explode(',', $args);
            $ret = array_map('trim', $ret);

            // 変数の解析
            foreach ($ret as $key => $value){
                if (preg_match('/^\$[a-zA-Z_]*[a-zA-Z0-9_]+$/', $value))
                    $ret[$key] = self::parseArgumentValue($value, $configs, $stopper);
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
}
