<?php
/**
 * Simple Session Management Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2009 Satoshi Nishimura
 */

/**
 * 簡易セッション管理クラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Session_Simple implements Laiz_Session
{
    /** @var bool 二度以上呼ばれたときのためにセッションが開始されているかのチェックフラグ */
    private $isStarted = false;

    /**
     * セッションの開始
     * @param string $sid セッションID
     * @param string $path セッションクッキーパス
     * @access public
     */
    public function __construct($sessionName = null, $sid = null, $path = '/'){
        if ($sid)
            session_id($sid);
        
        if ($sessionName)
            session_name($sessionName);
        $path = $path ? $path : $this->_path;
        
    }

    public function setSid($sid)
    {
        session_id($sid);
    }

    public function start()
    {
        session_start();
        $this->isStarted = true;
        $this->init();
    }        

    /**
     * セッションの破棄
     *
     * @access public
     */
    function end(){
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])){
            setcookie(session_name(), '', time()-4200);
        }

        session_destroy();
        $this->isStarted = false;
    }

    /**
     * セッション情報の初期化
     * @access public
     */
    function init(){
        $this->add('_ip_'  , $_SERVER['REMOTE_ADDR']);
        if (isset($_SERVER['HTTP_USER_AGENT']))
            $this->add('_ua_', $_SERVER['HTTP_USER_AGENT']);
        else
            $this->add('_ua_', 'undefined');
        $this->add('_time_', time());
    }

    /**
     * IPアドレス妥当性チェック
     * @return bool
     * @access public
     */
    function checkIp(){
        list($first, $second, $third, $forth) = explode('.',$_SERVER['REMOTE_ADDR']);
        if (!$s_ip = $this->get('_ip_')){ return false; }
        
        list($s_first, $s_second, $s_third, $s_forth) = explode('.', $s_ip);

        if ($first != $s_first || $second != $s_second || $third != $s_third){
            return false;
        }

        return true;
    }

    /**
     * UserAgent妥当性チェック
     *
     * @return bool
     * @access public
     */
    function checkUa(){
        if (isset($_SERVER['HTTP_USER_AGENT']))
            $ua = $_SERVER['HTTP_USER_AGENT'];
        else
            $ua = 'undefined';

        return ($this->get('_ua_') ==  $ua);
    }

    /**
     * セッション変数の返却
     * @param string $name
     * @return mixed
     * @access public
     */
    function get($name){
        if (!$this->isStarted)
            $this->start();

        if (isset($_SESSION[$name])){
            return $_SESSION[$name];
        }
    }

    /**
     * セッション変数の設定
     * @param string $name
     * @param mixed $value;
     * @access public
     */
    function add($name, $value){
        if (!$this->isStarted)
            $this->start();
        $_SESSION[$name] = $value;
    }

    /**
     * セッション変数の削除
     *
     * @param string $name
     * @access public
     */
    function remove($name){
        if (!$this->isStarted)
            $this->start();
        unset($_SESSION[$name]);
    }

    /**
     * セッションIDを返却するエイリアス
     *
     * @return string
     */
    public function getSid(){
        if (!$this->isStarted)
            $this->start();
        return $this->getSessionId();
    }

    /**
     * セッションIDを返却する
     *
     * @return string
     */
    public function getSessionId(){
        if (!$this->isStarted)
            $this->start();
        return session_id();
    }

    /**
     * セッション名を返却する
     *
     * @return string
     */
    public function getSessionName(){
        if (!$this->isStarted)
            $this->start();
        return session_name();
    }

    /**
     * セッションIDを変更する
     * @return bool
     */
    public function changeSessionId(){
        if (!$this->isStarted)
            $this->start();
        return session_regenerate_id(true);
    }
}
