<?php
/**
 * View Abstract Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2009 Satoshi Nishimura
 */

/**
 * View Abstract Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
abstract class Laiz_View
{
    /** メソッドが指定されていない場合のフィルタメソッド */
    const DEFAULT_FILTER_METHOD = 'viewFilter';

    /**
     * @var string $_template
     * @access protected
     */
    private $template;

    /**
     * @var string[] $_encoding
     * @access protected
     */
    var $_encoding = array();

    protected $TEMPLATE_DIR;
    protected $VIEW_LAYERING;
    protected $ERROR_TEMPLATE;
    protected $OUTPUT_404_HEADER;
    protected $TEMPLATE_EXTENSION;
    protected $OUTPUT_TEMPLATE_ERROR;
    protected $templatePrefix = '';
    protected $templateSuffix = '';
    public function init($args){
        // ビュー共通設定
        $this->VIEW_LAYERING       = $args['VIEW_LAYERING'];
        $this->ERROR_TEMPLATE      = $args['ERROR_TEMPLATE'];
        $this->OUTPUT_404_HEADER   = $args['OUTPUT_404_HEADER'];
        $this->TEMPLATE_EXTENSION  = $args['TEMPLATE_EXTENSION'];
        $this->OUTPUT_TEMPLATE_ERROR = $args['OUTPUT_TEMPLATE_ERROR'];
    }

    /**
     * テンプレートHTMLファイルの設定
     * @param string $template
     * @access public
     */
    public function setTemplate($template){
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * ビューの実行
     *
     * @access public
     * @return string テンプレートを指定する文字列
     */
    public function execute(){
        // テンプレート名の取得
        $templateName = $this->getTemplateName($this->template);

        // テンプレートの設定
        $this->parseTemplate($templateName);

        // 変数の設定
        $this->_setVariables();

        // 表示
        $this->output();
    }

    /**
     * テンプレート設定の抽象メソッド
     *
     * @param string $templateName
     * @access protected
     */
    abstract protected function parseTemplate($templateName);

    /**
     * 変数設定の抽象メソッド
     *
     * @access protected
     */
    abstract protected function _setVariables();

    /**
     * テンプレート名からパスを返却
     *
     * @param string $templateName
     * @return string
     * @access public
     */
    public function parseTemplatePath($templateName){
        if ($this->VIEW_LAYERING){
            return str_replace('_', '/', $templateName);
        }else{
            return $templateName;
        }
    }

    /**
     * テンプレートの拡張子を返却
     *
     * @return string
     * @access public
     */
    public function getTemplateExtension(){
        return $this->TEMPLATE_EXTENSION;
    }

    /**
     * テンプレートの拡張子設定
     *
     * @param string $templateExtension
     * @access public
     */
    public function setTemplateExtension($templateExtension){
        $this->TEMPLATE_EXTENSION = $templateExtension;
    }

    /**
     * テンプレートのディレクトリ設定
     *
     * @param string $dir 
     */
    public function setTemplateDir($dir)
    {
        $frameworkBase = dirname(__FILE__) . '/';
        $config = Laiz_Configure::get('base');
        $projectBase = $config['PROJECT_BASE_DIR'] . 'components/';

        $dirs = array();
        foreach ((array)$dir as $d)
            $dirs[] = $projectBase . $d . '/';
        $dirs[] = dirname(__FILE__) . '/Base/templates/';

        $this->TEMPLATE_DIR = $dirs;
    }

    /**
     * テンプレートファイル名の決定
     * @return string
     * @access protected
     */
    private function getTemplateName($actionName){
        $templateName = $this->parseTemplatePath($this->templatePrefix . $actionName . $this->templateSuffix . $this->getTemplateExtension());

        $fileExists = false;
        foreach ((array)$this->TEMPLATE_DIR as $dir){
            if (file_exists($dir . $templateName)){
                $fileExists = true;
            }
        }

        if (!$fileExists){
            if ($this->OUTPUT_404_HEADER){
                // 404ヘッダを出力
                header('HTTP/1.0 404 Not Found');
                //die();
            }

            if ($this->OUTPUT_TEMPLATE_ERROR)
                trigger_error("Template [$templateName] not found in " . join(PATH_SEPARATOR, (array)$this->TEMPLATE_DIR), E_USER_NOTICE);
            $templateName = $this->ERROR_TEMPLATE;
        }

        return $templateName;
    }

    /**
     * 変数設定済クラスの返却
     *
     * @param array $configs
     * @return Object[]
     * @access protected
     */
    protected function getClasses(){
        $responses = Laiz_Container::getInstance()->getComponents('Laiz_Action_Response');
        // foreach ($responses as $response)
        //     var_dump($response->getClass());
        // exit;
        $classes = array();
        foreach ($responses as $response)
            $classes[] = $response->getClass();
        return $classes;
    }

    /**
     * 変数の返却
     *
     * @param array $configs
     * @return array
     * @access protected
     */
    protected function _getTemplateVars($configs){
        $classes = $this->getClasses($configs);

        $allProperties = array();
        // プロパティを取得
        foreach ($classes as $class){
            $properties = get_object_vars($class);

            // アンダースコア以外で始まる変数を返す
            foreach ($properties as $key => $value){
                if (preg_match('/^_/', $key))
                    continue;
                
                if (is_object($value))
                    continue;
                
                $allProperties[$key] = $class->$key;
            }

        }
        return $allProperties;
    }

    /**
     * 出力エンコーディング設定
     *
     * @param $string $from
     * @param $string $to
     * @access public
     */
    public function setEncoding($to, $from = null){
        if ($from === null)
            $from = mb_internal_encoding();

        if ($from != $to)
            $this->_encoding = array('from' => $from,
                                     'to'   => $to);
    }

    /**
     * 出力
     *
     * @access protected
     */
    abstract protected function output();

    /**
     * 出力を返却
     *
     * @param string $template
     * @return string
     * @access public
     */
    public function bufferedOutput($template){
        // テンプレート名の取得
        $templateName = $this->getTemplateName($template);

        // テンプレートの設定
        $this->parseTemplate($templateName);

        // 変数の設定
        $this->_setVariables();

        // 表示
        return $this->_bufferedOutput();
    }

    abstract protected function _bufferedOutput();

    public function setTemplatePrefix($prefix)
    {
        $this->templatePrefix = $prefix;
        return $this;
    }

    public function setTemplateSuffix($suffix)
    {
        $this->templateSuffix = $suffix;
        return $this;
    }
}
