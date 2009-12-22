<?php
/**
 * Simple Auto Login Management Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2009 Satoshi Nishimura
 */

/**
 * Simple auto login manager of user login.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Session_AutoLogin
{
    /** @var SessionUtils */
    private $session;

    /** @var string sqlite database file */
    private $dbFile;

    /** @var string */
    const COOKIE_KEY = '__auto_login__';
    
    /** @var string */
    const SESSION_STARTED = '__started__';

    /** @var string */
    const LOGINED_KEY = '__logined__';

    /** @var int */
    const USER_ID_KEY = '__userId__';

    /**
     * Store session class.
     *
     * @param Laiz_Session $sess
     * @access public
     */
    public function __construct(Laiz_Session $sess){
        $this->session = $sess;
    }

    private function createDsn()
    {
        $configs = Laiz_Configure::get('Laiz_View');
        $dsn = 'sqlite:'.$configs['FLEXY_COMPILE_DIR'].'userlogin.sq3';
        return $dsn;
    }

    private function createPdo($dsn = null)
    {
        $dsn = $dsn ? $dsn : $this->createDsn();
        $pdo = new PDO($dsn);
        return $pdo;
    }

    private function initDatabase($pdo)
    {
        // check table
        $tablesQuery = 'select name from sqlite_master where type=\'table\' and name = \'auto_login\'';
        $stmt = $pdo->query($tablesQuery);
        if ($stmt)
            $row = $stmt->fetch();
        if (!$stmt || !isset($row['name']) || $row['name'] !== 'auto_login'){
            $stmt = null;
            $ret = $pdo->exec('CREATE TABLE auto_login(user_id, key, expire, data)');

            $info = $pdo->errorInfo();
            if ($info[0] !== '00000'){
                trigger_error('Failed to start session of auto login: ['
                              . $info[0] . '] ' . $info[2], E_USER_WARNING);
                return false;
            }
        }

        return true;
    }

    /**
     * login process
     *
     * @author Satoshi Nishimura <nishim314@gmail.com>
     * @return bool
     */
    public function login(Laiz_Session_Login $login,
                          $user, $pass, $auto,
                          $expire = null, $dsn = null, $path = null)
    {
        if (!$id = $login->login($user, $pass))
            return false;

        $expire = $expire !== null ? $expire : 3600*24*7;

        $pdo = $this->createPdo($dsn);

        $path = $path ? $path : '/';

        if ($auto)
            $this->setupAutoLogin($pdo, $id, $path, $expire);
        else
            $this->cleanupAutoLogin($pdo, $path);

        $this->setLogined();
        return true;
    }

    /**
     * Store session data to auto login database.
     */
    public function store($userId, $data, $expire = 604800,
                          $dsn = null, $path = '/')
    {
        $pdo = $this->createPdo($dsn);
        $this->setupAutoLogin($pdo, $userId, $path, $expire, $data);
    }

    /**
     *
     * @param PDO $pdo
     * @param int $expire default is 3600*24*7
     * @param string $path cookie's path
     */
    private function setupAutoLogin(PDO $pdo, $userId, $path = '/', $expire = 604800, $data = null){
        if (!$this->initDatabase($pdo)){
            trigger_error('Cannot create auto login table.', E_USER_WARNING);
            return false;
        }

        // register information of cookie to database.
        $loginKey = sha1(uniqid().mt_rand());
        $sql = "insert into auto_login(user_id, key, expire, data) values ("
            . $pdo->quote($userId) . ', '
            . $pdo->quote($loginKey) . ', '
            . $pdo->quote(date('Y-m-d H:i:s', time()+$expire)) . ', '
            . $pdo->quote(serialize($data))
            . ')';
        // TODO: insert data => $_SESSION
        $ret = $pdo->exec($sql);

        $info = $pdo->errorInfo();
        if ($info[0] !== '00000'){
            trigger_error('Failed to start session of auto login: ['
                          . $info[0] . '] ' . $info[2], E_USER_WARNING);
            return false;
        }

        // send auto login cookie.
        setcookie(self::COOKIE_KEY, $loginKey, time() + $expire, $path);

        // set user id to session.
        $this->session->add(self::USER_ID_KEY, $userId);
    }

    private function cleanupAutoLogin(PDO $pdo, $path){
        if (!isset($_COOKIE[self::COOKIE_KEY]))
            return;
        
        // delete old cookie.
        setcookie(self::COOKIE_KEY, '', time() - 3600, $path);

        // delete old information in database.
        $sql = 'delete from auto_login where key = ' . $pdo->quote($_COOKIE[self::COOKIE_KEY])
            . ' or expire < ' . $pdo->quote(date('Y-m-d H:i:s')) . ';'; // delete old data
        $pdo->exec($sql);
    }

    private function isStartedSession(){
        return ($this->session->get(self::SESSION_STARTED) === true);
    }

    private function startSession(){
        $this->session->add(self::SESSION_STARTED, true);
    }

    private function isLogined(){
        return $this->session->get(self::LOGINED_KEY);
    }
    private function setLogined(){
        $this->session->add(self::LOGINED_KEY, true);
    }

    private function getUserId(){
        return $this->session->get(self::USER_ID_KEY);
    }

    public function logout($dsn = null, $path = '/'){
        $dsn = $dsn ? $dsn : $this->createDsn();
        $pdo = new PDO($dsn);

        $this->session->add(self::LOGINED_KEY, false);

        $this->cleanupAutoLogin($pdo, $path);
    }

    /**
     *
     * @param int $expire
     * @param string $path
     * @param string $dsn
     * @return array(bool, bool, int, array) startNow, isLogined, userId, data
     */
    public function autoLogin($expire = 604800, $path = '/', $dsn = null){
        // First argument is dsn, not PDO object.
        // PDO object doesn't need in alot of cases.
        $data = array();
        // Return when session is started.
        if ($this->isStartedSession())
            return array(false, $this->isLogined(), $this->getUserId(), $data);

        if (!empty($_COOKIE[self::COOKIE_KEY])){
            $pdo = $this->createPdo($dsn);

            if (!$this->initDatabase($pdo)){
                trigger_error('Cannot create auto login table.', E_USER_WARNING);
                return array(false, false, null, $data);
            }

            $sql = 'select * from auto_login where key = '
                . $pdo->quote($_COOKIE[self::COOKIE_KEY]) . ' and expire > '
                . $pdo->quote(date('Y-m-d H:i:s')) . ';';
            
            $stmt = $pdo->query($sql);
            if ($stmt){
                $ret = $stmt->fetch();
                $stmt = null;           // free statement resource
                if ($ret['key'] === $_COOKIE[self::COOKIE_KEY]){
                    // restore data
                    $data = unserialize($ret['data']);

                    // set login flag
                    $this->setLogined();

                    // delete old cookie.
                    $this->cleanupAutoLogin($pdo, $path);

                    // setup auto login
                    $this->setupAutoLogin($pdo, $ret['user_id'], $path, $expire);
                }
            }
        }

        // start session
        $this->startSession();

        return array(true, $this->isLogined(), $this->getUserId(), $data);
    }

}
