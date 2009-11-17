<?php
/**
 * View Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * ビュークラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_View_Flexy extends Laiz_View
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
    /**
     * 変数の初期化
     *
     * @param array $args
     * @access public
     */
    public function init($args){
        parent::init($args);
        
        // Flexy設定
        $this->FLEXY_COMPILE_DIR    = $args['FLEXY_COMPILE_DIR'];
        $this->FLEXY_FORM_SET_VALUE = $args['FLEXY_FORM_SET_VALUE'];
        $this->FLEXY_FORM_SELECTED  = $args['FLEXY_FORM_SELECTED'];
        $this->FLEXY_FORCE_COMPILE  = $args['FLEXY_FORCE_COMPILE'];

        if (isset($args['FLEXY_FORM_DEFAULT_ACTION']))
            $this->FLEXY_FORM_DEFAULT_ACTION = $args['FLEXY_FORM_DEFAULT_ACTION'];
        if (isset($args['FLEXY_FORM_DEFAULT_METHOD']))
            $this->FLEXY_FORM_DEFAULT_METHOD = $args['FLEXY_FORM_DEFAULT_METHOD'];
        
        $this->_obj = new StdClass;
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
    function _setTemplate($templateName){
        // Hidden値の設定
        // Flexyを生成する前に $this->_hidden に値を代入しておく
        // Flexyから $this->_hidden が利用される
        $hidden = Laiz_Container::getInstance()->getComponent('Laiz_Action_Component_Hidden');
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
                         'forceCompile' => $this->FLEXY_FORCE_COMPILE,
                         'compiler'     => 'FlexyEx');
        // ユーザ独自の設定
        if ($this->_flexyOptions){
            $options = array_merge($options, $this->_flexyOptions);
        }

        $rep = error_reporting();
        error_reporting($rep & E_ALL); // PEARの関係上Strictエラーを除外する
        
        $this->flexy = new HTML_Template_Flexy($options);

        // Hidden値の設定
        $this->flexy->setHiddens($this->_hiddens);

        $this->flexy->compile($templateName);
        $this->_elements = $this->flexy->getElements();
        error_reporting($rep);
    }

    /**
     * 変数の設定
     *
     * @param array $configs
     * @access protected
     */
    function _setVariables(){
        // プロパティを取得
        $classes = $this->getClasses();



        // 親クラスでアンダースコア以外の変数を取得して
        // それを引数にすべき？


        
        foreach ($classes as $class){
            $properties = get_object_vars($class);

            // アンダースコア以外で始まる変数を設定する
            foreach ($properties as $key => $value){
                if (is_object($value) && preg_match('/^_laizVo/', $key)){
                    // _laizVoで始まる変数にオブジェクトが設定されている場合は展開する
                    $voProperties = get_object_vars($value);
                    foreach ($voProperties as $key => $value){
                        $this->_obj->$key = $value;
                    }

                }else if (!preg_match('/^_/', $key)){
                    // 通常の変数
                    $this->_obj->$key = $class->$key;
                }

            }
        }

        if (!$this->FLEXY_FORM_SET_VALUE){
            // フォームの値を自動設定しない場合はここでリターン
            return;
        }

        /*
         * フォーム要素の設定
         */
        // ビジネスロジックで設定されたフォーム値の取得
        $objectVars = get_object_vars($this->_obj);
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
                $request = Laiz_Container::getInstance()->getComponent('Laiz_Request');
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
    protected function _bufferedOutput(){
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
