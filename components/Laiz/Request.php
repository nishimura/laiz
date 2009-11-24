<?php
/**
 * HTTP Request Management Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2009 Satoshi Nishimura
 */

/**
 * HTTP Request管理クラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Request{

    /**
     * @var array $param リクエスト情報を格納する配列
     */
    var $params = array();

    private $ACTION_KEY;
    private $PATH_INFO_ACTION;

    private $actionName;

    /**
     * GET, POST の変数データと REQUEST_METHOD をメンバ変数に代入
     * @access public
     */
    function __construct(){
        if (is_array($_REQUEST)){
            foreach ($_REQUEST as $key => $value){
                $this->add($key, $value);
            }
        }

        $this->add("REQUEST_METHOD", $_SERVER["REQUEST_METHOD"]);
    }

    /**
     * アクションに使うリクエストキー名を登録する
     *
     * @param string $name
     */
    public function setActionKey($name)
    {
        $this->ACTION_KEY = $name;
    }

    /**
     * 変数(オブジェクト)をリクエスト変数に追加
     *
     * @param string $name
     * @param mixed $data
     * @access public
     */
    function add($name, $data){
        $this->params[$name] = $data;
    }

    /**
     * リクエスト変数の名前から値を返却
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
     * 全リクエスト変数を配列で返却
     *
     * @return mixed
     * @access public
     */
    function getAll(){
        return $this->params;
    }

    /**
     * PATH_INFOの何番目をアクション変数とするかを設定する
     *
     * @param int $num
     */
    public function setPathInfoAction($num)
    {
        $this->PATH_INFO_ACTION = $num;
        return $this;
    }

    /**
     * PATH_INFOの何番目をアクション変数とするかを返却する
     * @return int
     */
    public function getPathInfoAction()
    {
        return $this->PATH_INFO_ACTION;
    }

    /**
     * アクション名を初期化して設定する
     *
     * @access public
     */
    public function initActionName(){
        if (isset($this->params[$this->ACTION_KEY])){
            $action = $this->params[$this->ACTION_KEY];

        }elseif (isset($_SERVER['PATH_INFO'])){
            // PATH_INFOをアクションとして解釈
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
     * アクション名を返却する
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    public function setRequestsByPathInfo($configs){
        // PATH_INFOからプロパティ設定
        if ($this->PATH_INFO_ACTION && isset($_SERVER['PATH_INFO']) && isset($configs['pathinfo'])){
            $pathInfo = explode('/', $_SERVER['PATH_INFO']);
            foreach ($configs['pathinfo'] as $key => $value){
                if (isset($pathInfo[$value + $this->PATH_INFO_ACTION]))
                    $this->add($key, $pathInfo[$value + $this->PATH_INFO_ACTION]);
            }
        }

    }

    /**
     * クラスプロパティにリクエスト変数を設定
     *
     * @param Object $class 解析するオブジェクト
     * @access public
     */
    function setPropertiesByRequest($class){
        $properties = get_object_vars($class);
        foreach ($properties as $property => $var){
            // Requestから_以外で始まる変数をセット
            if (preg_match('/^([^_].*)/', $property, $matches)){
                $value = $this->get($matches[1]);

                if ($value !== null){
                    $class->$property = $value;
                }
            }
        }

    }


}
