<?php
/**
 * Error Message Utility Parent Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\error;

/**
 * Error abstract class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
abstract class Base
{
    /** @var Object singleton instance */
    static protected $instance;

    /** @var int エラー出力レベル */
    protected $level;

    /** @var string[] エラー出力文字列 */
    protected $levelTag = array();

    final private function __construct($args){
        static::init($args);
    }

    // must call initLevel($level)
    abstract protected function init($args);

    /**
     * エラー出力レベルの設定
     *
     * @access public
     */
    protected function initLevel($level){
        $this->level = $level;
        $this->levelTag
            = array(E_USER_ERROR   => 'Error',
                    E_USER_WARNING => 'Warning',
                    E_USER_NOTICE  => 'Notice');
    }

    /**
     * バックトレースのエラー発生時の部分までを返却
     *
     * @return array
     * @access public
     */
    public function getBackTrace(){
        $ret = array();
        $b = debug_backtrace();


        /*
         * エラー処理クラス以前の呼び出し元を先頭にするための並び変え
         */
        for ($i = 0; $i < count($b); $i++){
            // 標準エラーから呼ばれた場合は file が msg に含まれている
            if (!isset($b[$i]['file']))
                continue;

            // エラー処理クラスのトレースは表示しない
            if (dirname(__FILE__) == dirname($b[$i]['file']))
                continue;
            
            $ret[] = $b[$i];
        }

        return $ret;
    }

    /**
     * 唯一のインスタンスを返却
     *
     * @return Object
     * @access public
     */
    static public function getInstance($args = array()){
        if (static::$instance === null){
            static::$instance = new static($args);
        }

        return static::$instance;
    }

    /**
     * 出力用にフォーマットしたメッセージを返却
     *
     * @param string $level
     * @param string[] $msg array(array('file', 'line', 'function', 'args'[, 'class', 'type']));
     * @access protected
     */
    protected function _getFormatBackTrace($lavel, $msg){
        $bt = $this->getBackTrace();
        $head = $bt[0];

        if (!isset($head['file'])){ $head['file'] = ''; }
        if (!isset($head['line'])){ $head['line'] = ''; }
        $ret = array();
        $ret[] = "[$lavel]: $msg in $head[file] on line $head[line]";
        foreach ($bt as $line){
            // 引数の表示書式設定
            $args = array();
            if (!isset($line['file'])){ $line['file'] = ''; }
            if (!isset($line['line'])){ $line['line'] = ''; }
            if (!isset($line['args'])){ $line['args'] = array(); }

            foreach ($line['args'] as $key => $arg){
                if (is_array($arg)){
                    //$arg = 'array '.var_export($arg, true);
                    $arg = 'array array';

                }elseif (gettype($arg) == 'object'){
                    $arg =  'object ' . get_class($arg);
                    //$arg =  'object ' . var_export($arg, true);
                }elseif (gettype($arg) == 'string'){
                    $arg = "string '$arg'";
                }else{
                    $arg = gettype($arg) . ' ' . (string)$arg;
                }
                $args[$key] = $arg;
            }
            // 関数の表示書式設定
            if (isset($line['class']) && isset($line['type'])){
                $function = "$line[class]$line[type]$line[function](".implode(', ', $args).')';
            }else{
                $function = "$line[function](".implode(', ', $args).')';
            }

            $ret[] = "$function: in $line[file] on line $line[line]";
        }

        return $ret;
    }

    /**
     * エラー発生
     *
     * @param string $msg
     * @access public
     */
    public function error($level, $msg){
        if ($this->level & $level){
            $backTrace = $this->_getFormatBackTrace($this->levelTag[$level], $msg);
            $this->_output($backTrace);
        }
    }
}
