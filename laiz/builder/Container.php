<?php
/**
 * Container Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\builder;

use \laiz\core\Configure;
use \laiz\parser\Ini_Simple;

use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;
use \UnexpectedValueException;

use \ReflectionClass;
use \ReflectionException;

/**
 * コンテナクラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Container
{
    const CACHE_FILENAME = 'aggregatableInterfaces.serialized';
    const DEFAULT_PRIORITY = 100;

    /** @var Laiz_Container */
    static private $instance;

    /** @var string project default components list file */
    private $COMPONENTS_INI;

    /**
     * @var Object Instances of component.
     * @access private
     */
    private $components = array();

    /** @var array $interfaces[interfaceName][priority] = component */
    private $interfaces = array();

    /** @var array $priorities[componentName] = priority */
    private $priorities = array();

    /** @var array */
    private $ignoreInterfaces = array();

    /** @var array */
    private $ignoreClasses = array();

    /**
     * 初期化処理
     *
     * @access public
     */
    private function __construct(){
        $this->ignoreInterfaces = get_declared_interfaces();
        $this->ignoreClasses = get_declared_classes();
    }

    private function __clone(){
        trigger_error('Clone is not allowed', E_USER_ERROR);
    }

    /**
     * コンテナインスタンスを返却
     *
     * @return Laiz_Container
     * @access public
     */
    static public function getInstance(){
        if (self::$instance === null){
            $class = __CLASS__;
            self::$instance = new $class();

            self::$instance->init();
        }

        return self::$instance;
    }

    public function init()
    {
        $this->initInterfaces();
        $this->initComponents();
    }

    private function initComponents()
    {
        $modes = $this->getComponents('laiz.builder.Mode');
        foreach ($modes as $mode){
            if (!$mode->accept())
                continue;

            $mode->buildComponents($this);
            break;
        }
    }

    private function initInterfaces()
    {
        /*
         * Initialize Aggregatable Interfaces.
         */
        $base = Configure::get('base');

        if ($base['INTERFACES_CACHE']){
            $cacheFile = $base['CACHE_DIR'] . self::CACHE_FILENAME;
            if (file_exists($cacheFile)){
                // using cache.
                // Delete cache file to clear caching.
                $this->interfaces = unserialize(file_get_contents($cacheFile));
                return;
            }
        }

        // ==debug==
        //$start = microtime(true);

        $projectDir = $base['PROJECT_BASE_DIR'] . 'app/';
        $laizDir = dirname(dirname(__FILE__));

        // if not parsed
        $interfaces = array();
        $interfaces = $this->getAggregatable($projectDir, $interfaces);
        $interfaces = $this->getAggregatable($laizDir, $interfaces);

        $this->registerInterfaces($interfaces);

        if ($base['INTERFACES_CACHE'])
            file_put_contents($cacheFile, serialize($this->interfaces));

        // ==debug==
        //$end = microtime(true);
        //var_dump($end-$start);exit;
    }

    private function getAggregatable($basePath, $aggregatableInterfaces)
    {
        $iterator = new RecursiveIteratorIterator(new AutoIncludeFilter(new RecursiveDirectoryIterator($basePath)), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $fileInfo){
            $file = $fileInfo->getFilename();
            if (!preg_match('/^[A-Z][^.]+\.php$/', $file))
                continue;

            include_once $fileInfo->getPathname();
        }

        $interfaces = get_declared_interfaces();
        foreach ($interfaces as $interface){
            if (in_array($interface, $this->ignoreInterfaces))
                continue;

            // try { // ==check== TODO
            $ref = new ReflectionClass($interface);

            foreach ($ref->getInterfaces() as $i){
                if ($i->name === 'laiz\builder\Aggregatable'){
                    if (!isset($aggregatableInterfaces[$interface]))
                        $aggregatableInterfaces[$interface] = array();
                    $aggregatableInterfaces[$interface] = $interface;
                }
            }
        }

        return $aggregatableInterfaces;
    }

    private function registerInterfaces($aggregatableInterfaces)
    {
        $classes = get_declared_classes();
        foreach ($classes as $class){
            if (in_array($class, $this->ignoreClasses))
                continue;

            $ref = new ReflectionClass($class);
            if ($ref->isAbstract())
                continue;

            $priority = self::DEFAULT_PRIORITY;
            $comment = $ref->getDocComment();
            if ($comment && preg_match('/@priority +([0-9]+)/', $comment, $matches)){
                $priority = (int)$matches[1];
            }

            foreach ($ref->getInterfaces() as $i){
                if (in_array($i->name, $aggregatableInterfaces)){
                    if (!isset($this->interfaces[$i->name]))
                        $this->interfaces[$i->name] = array();
                    $this->interfaces[$i->name][$class] = $priority;
                }
            }
        }
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

        return $this;
    }

    /**
     * コンポーネントの作成
     *
     * @param string $componentName
     * @param string $registerName
     * @return Object
     */
    public function create($componentName, $registerName = '')
    {
        $componentName = str_replace('.', '\\', $componentName);
        // コンポーネントが既に存在する場合はここでリターン
        if (is_object($this->get($componentName))){
            return $this->get($componentName);
        }

        $obj = Object::build($componentName);

        if (strlen($registerName) > 0)
            $componentName = str_replace('.', '\\', $registerName);

        if ($obj instanceof Singleton)
            $this->register($obj, $componentName);
        return $obj;
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
    public function register($component, $name = ''){
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
     * コンポーネントインスタンスの返却
     *
     * @param string $name
     * @return Object
     * @access public
     */
    public function get($name){
        $name = str_replace('.', '\\', $name);
        if (isset($this->components[$name])){
            $component = $this->components[$name];
        }else if ($name === __CLASS__){
            // return self object
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
     * インターフェース名を受け取り、それを実装しているオブジェクトの配列を返却する。
     *
     * @param string $interface
     * @return array
     */
    public function getComponents($interface)
    {
        $ret = array();
        $interface = str_replace('.', '\\', $interface);

        if (!isset($this->interfaces[$interface]))
            return $ret;

        $classes = $this->interfaces[$interface];

        // Return unused priority.
        $used = array();
        $getPriority = function($want) use (&$used, &$getPriority){
            if (!in_array($want, $used)){
                $used[] = $want;
                return $want;
            }
            $want++;
            return $getPriority($want);
        };
        foreach ($classes as $class => $priority){
            $obj = $this->get($class);
            if (!$obj)
                $obj = $this->create($class);
            $priority = $getPriority($priority);
            $ret[$priority] = $obj;
        }
        ksort($ret);

        return $ret;
    }
}
