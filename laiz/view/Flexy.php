<?php
/**
 * View Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\view;

use \StdClass;
use \laiz\builder\Container;
use \Fly_Flexy;
use \laiz\core\Configure;

/**
 * ビュークラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Flexy extends Template
{
    /** @var HTML_Template_Flexy $flexy */
    private $flexy;

    /** @var array Flexyに渡すオプション */
    private $_flexyOptions;

    /**
     * @var StdClass $_obj Flexyに渡す変数
     * @access private
     */
    private $_obj;

    /**
     * @var HTML_Template_Flexy_Element[] 
     * @access private
     */
    private $_elements;

    /**
     * @var string[] formのhidden値設定用変数
     * @access private
     */
    private $_hiddens;

    private $FLEXY_COMPILE_DIR;
    private $FLEXY_FORM_SET_VALUE;
    private $FLEXY_FORM_SELECTED;
    private $FLEXY_FORCE_COMPILE;
    private $FLEXY_FORM_DEFAULT_ACTION;
    private $FLEXY_FORM_DEFAULT_METHOD;

    public function __construct()
    {
        $base = Configure::get('base');
        $this->FLEXY_COMPILE_DIR    = $base['CACHE_DIR'];
        
        //$configs = Configure::get(__CLASS__);
        $configs = Configure::get(__NAMESPACE__);
        parent::__construct($configs);

        $this->FLEXY_FORM_SET_VALUE = $configs['FLEXY_FORM_SET_VALUE'];
        $this->FLEXY_FORM_SELECTED  = $configs['FLEXY_FORM_SELECTED'];
        $this->FLEXY_FORCE_COMPILE  = $configs['FLEXY_FORCE_COMPILE'];

        if (isset($configs['FLEXY_FORM_DEFAULT_ACTION']))
            $this->FLEXY_FORM_DEFAULT_ACTION = $configs['FLEXY_FORM_DEFAULT_ACTION'];
        if (isset($configs['FLEXY_FORM_DEFAULT_METHOD']))
            $this->FLEXY_FORM_DEFAULT_METHOD = $configs['FLEXY_FORM_DEFAULT_METHOD'];
        
        $this->_elements = array();
    }

    /**
     * Flexyオプションの設定
     *
     * @param array $options
     * @access public
     */
    function setFlexyOptions($options){
        $this->_flexyOptions = $options;
    }

    /**
     * テンプレートの設定
     * @param $templateName
     * @access private
     */
    protected function setVariables($templateName, $obj){
        // Hidden値の設定
        // Flexyを生成する前に $this->_hidden に値を代入しておく
        // Flexyから $this->_hidden が利用される
        $hidden = Container::getInstance()->getComponent('Laiz_Action_Component_Hidden');
        if ($hidden instanceof Laiz_Action_Component_Hidden){
            foreach ($hidden->getHiddens() as $key => $value){
                if (is_array($value)){
                    foreach ($value as $hiddenName => $hiddenValue){
                        $this->setHidden($key, $hiddenName, $hiddenValue);
                    }
                }
            }
        }

        $options = array('templateDir' => $this->TEMPLATE_DIR,
                         'multiSource' => true,
                         'compileDir'  => $this->FLEXY_COMPILE_DIR,
                         'numberFormat' => ', 0',
                         'forceCompile' => $this->FLEXY_FORCE_COMPILE);
        // ユーザ独自の設定
        if ($this->_flexyOptions){
            $options = array_merge($options, $this->_flexyOptions);
        }

        $rep = error_reporting();
        error_reporting($rep & E_ALL); // PEARの関係上Strictエラーを除外する
        $this->flexy = new Fly_Flexy($options);

        // Hidden値の設定
        //$this->flexy->setHiddens($this->_hiddens);

        $this->flexy->compile($templateName);
        $this->_elements = $this->flexy->getElements();
        error_reporting($rep);

        $this->_setVariables($obj);
        $this->_obj = $obj;
    }

    /**
     * 変数の設定
     *
     * @param array $configs
     * @access protected
     */
    function _setVariables($obj){
        if (!$this->FLEXY_FORM_SET_VALUE){
            // フォームの値を自動設定しない場合はここでリターン
            return;
        }

        /*
         * フォーム要素の設定
         */
        // ビジネスロジックで設定されたフォーム値の取得
        $objectVars = get_object_vars($obj);
        foreach ($this->_elements as $key => $element){
            if (!isset($element->tag)){ continue; }

            // fromタグの属性自動設定
            if (strtolower($element->tag) == 'form'){
                $this->_setFormTag($this->_elements[$key]);
            }

            if (!isset($objectVars[$key])){
                $tmpKey = preg_replace('/\[\]$/', '', $key);
                if (isset($objectVars[$tmpKey])){
                    // キーが配列の場合の設定
                    $objectVars[$key] = $objectVars[$tmpKey];
                    unset($objectVars[$tmpKey]);
                }else{
                    // アクションにプロパティが存在しない場合は何もしない
                    continue;
                }
            }
            //var_dump($key);
            // valueの設定
            $this->_setTagValue($this->_elements[$key], $objectVars[$key]);
        }
    }

    /**
     * formタグの属性設定
     *
     * @param HTML_Template_Flexy_Element $emenet $this->_elementsのひとつ
     * @access private
     */
    function _setFormTag($element){
        // action属性の自動設定
        if (!isset($element->attributes['action'])
            && isset($this->FLEXY_FORM_DEFAULT_ACTION)){
            $element->attributes['action'] = $this->FLEXY_FORM_DEFAULT_ACTION;
        }
        // method属性の自動設定
        if (!isset($element->attributes['method'])
            && isset($this->FLEXY_FORM_DEFAULT_METHOD)){
            $element->attributes['method'] = $this->FLEXY_FORM_DEFAULT_METHOD;
        }
    }

    /**
     * value属性の設定
     *
     * @param HTML_Template_Flexy_Element $emenet $this->_elementsのひとつ
     * @param mixed $var inputやtextareaは文字列、selectの場合は配列
     * @access private
     */
    function _setTagValue($element, $var){
        switch ($element->tag){
        case 'input':
            if (isset($element->attributes['type'], $element->attributes['name'])
                && $element->attributes['type'] == 'checkbox'
                && preg_match('/\[\]$/', $element->attributes['name'])
                ){
                // チェックボックスの配列
                $element->setValue((array)$var);

            }elseif (method_exists($element, 'setValue')){
                $element->setValue($var);
            }
            break;

        case 'select':
            if (!is_array($var)){
                // selectの設定は配列でなければならない
                break;
            }

            $options  = array();
            if (!array_key_exists($this->FLEXY_FORM_SELECTED, $var)){
                // 初期値が指定されていない場合
                $request = Container::getInstance()->getComponent('laiz.action.Request');
                $selected = $request->get($element->attributes['name']);
                $selectedKey = null;
                foreach ($var as $sKey => $sValue){
                    $options[$sKey] = $sValue;
                    if ($selected == $sValue){
                        // リクエスト変数からデフォルト値の設定
                        $selectedKey = $sKey;
                    }
                }
                $element->setOptions($options);
                $element->setValue($selectedKey);
            }else{
                // 初期値が指定されている場合
                foreach ($var as $sKey => $sValue){
                    if ($sKey != $this->FLEXY_FORM_SELECTED){
                        $options[$sKey] = $sValue;
                    }
                }
                $element->setOptions($options);
                $element->setValue($var[$this->FLEXY_FORM_SELECTED]);
            }
            break;

        case 'textarea':
            if (method_exists($element, 'setValue')){
                $element->setValue($var);
            }

            break;
                            
        default:
            break;
        }

    }

    /**
     * 表示処理
     *
     * @access protected
     */
    protected function output(){
        if ($this->_encoding){
            echo mb_convert_encoding($this->flexy->bufferedOutputObject($this->_obj, $this->_elements),
                                     $this->_encoding['to'],
                                     $this->_encoding['from']);
        }else{
            $this->flexy->outputObject($this->_obj, $this->_elements);
        }
    }

    /**
     * 出力を返却
     *
     * @return string
     * @access public
     */
    protected function getOutput(){
        if ($this->_encoding){
            return mb_convert_encoding($this->flexy->bufferedOutputObject($this->_obj, $this->_elements),
                                     $this->_encoding['to'],
                                     $this->_encoding['from']);
        }else{
            return $this->flexy->bufferedOutputObject($this->_obj, $this->_elements);
        }
    }

    /**
     * フォームのhidden値設定
     *
     * @param string $formName
     * @param string $name
     * @param string $value
     * @access public
     */
    function setHidden($formName, $name, $value){
        $this->_hiddens[$formName][$name] = $value;
    }
}
