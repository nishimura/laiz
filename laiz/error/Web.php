<?php
/**
 * Error Message Utility of Web Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\error;

/**
 * Web用エラー関連処理クラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Web extends Base
{
    /** @var Object singleton instance */
    static protected $instance;

    protected function init($args){
        $this->LAIZ_ERROR_WEB_LEVEL = $args['LAIZ_ERROR_WEB_LEVEL'];
        $this->initLevel($this->LAIZ_ERROR_WEB_LEVEL);
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

    /**
     * エラーを画面に出力
     *
     * @param string[] $backTrace
     * @access private
     */
    private function _output($backTrace){
        echo '<table cellpadding=4 cellspacing=0 style="border: 1px solid #999999; width:100%" border="1px">';
        echo "\n";
        $head = array_shift($backTrace);
        echo "<tr><th colspan=2 style=\"background-color:pink;\">$head</th></tr>\n";
        foreach ($backTrace as $b){
            list($func, $body) = explode(': in', $b);
            echo "<tr><td>$func</td><td>$body</td></tr>\n";
        }
        echo "</table>\n";
    }

}

