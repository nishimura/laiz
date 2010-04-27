<?php
/**
 * View Abstract Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\view;

use \laiz\core\Configure;
use \laiz\builder\Singleton;
use \laiz\action\Response;

/**
 * View Abstract Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
abstract class Template implements View, Singleton
{
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

    public function __construct()
    {
        $configs = Configure::get(__NAMESPACE__);
        $this->ERROR_TEMPLATE      = $configs['ERROR_TEMPLATE'];
        $this->OUTPUT_404_HEADER   = $configs['OUTPUT_404_HEADER'];
        $this->TEMPLATE_EXTENSION  = $configs['TEMPLATE_EXTENSION'];
        $this->OUTPUT_TEMPLATE_ERROR = $configs['OUTPUT_TEMPLATE_ERROR'];
    }

    /**
     * ビューの実行
     *
     * @access public
     * @return string テンプレートを指定する文字列
     */
    public function execute(Response $res, $baseName){
        $this->main($res, $baseName)->output();
    }

    protected function main(Response $res, $baseName)
    {
        $templateName = $this->getTemplateName($baseName);
        $outputObj = $res->getObject();
        $this->setVariables($templateName, $outputObj);

        return $this;
    }

    /**
     * テンプレート設定の抽象メソッド
     *
     * @param string $templateName
     * @access protected
     */
    abstract protected function setVariables($templateName, $obj);

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
        $config = Configure::get('base');
        $projectBase = $config['PROJECT_BASE_DIR'] . 'app/';

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
    private function getTemplateName($baseName){
        $templateName = $this->templatePrefix
            . $baseName
            . $this->templateSuffix
            . $this->getTemplateExtension();
        $templateName = str_replace('_', '/', $templateName);

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
     * return output
     *
     * @param string $template
     * @return string
     */
    public function bufferedOutput(Response $res, $template){
        return $this->main($res, $template)->getOutput();
    }

    abstract protected function getOutput();

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