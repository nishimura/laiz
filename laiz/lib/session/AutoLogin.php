<?php
/**
 * Simple Auto Login Management Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\lib\session;

use \laiz\lib\data\DataStore;
use \laiz\lib\data\DataStore_Sqlite;

use \laiz\command\Help;

/**
 * Simple auto login manager of user login.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class AutoLogin implements Help
{
    /** @var SessionUtils */
    private $session;

    private $dataSource;

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
     * @access public
     */
    public function __construct(Session $sess){
        $this->session = $sess;
    }

    public function setDataStore(DataStore $ds)
    {
        $this->dataSource = $ds;
    }

    public function getDataStore()
    {
        if ($this->dataSource instanceof DataStore)
            return $this->dataSource;

        // create default data source.
        // default is sqlite.
        $ds = new DataStore_Sqlite();
        if (!$ds->setDsn(array('scope' => 'laiz_session_autologin'))){
            trigger_error('Cannot create data source.', E_USER_WARNING);
            return null;
        }

        // TODO: refactoring of GC.
        if (substr((string)microtime(true), -3, 1) === '0')
            $ds->clearExpiration();

        $this->dataSource = $ds;
        return $ds;
    }

    public function setDomain($domain)
    {
        $this->session->setDomain($domain);
    }

    /**
     * login process
     *
     * @author Satoshi Nishimura <nishim314@gmail.com>
     * @return bool
     */
    public function login($id, $auto, $expire = null, $path = null)
    {
        $expire = $expire !== null ? $expire : 3600*24*7;

        $ds = $this->getDataStore();

        $path = $path ? $path : '/';

        if ($auto)
            $this->setupAutoLogin($ds, $id, $path, $expire);
        else
            $this->cleanupAutoLogin($ds, $path);

        $this->setLogined($id);
        return true;
    }

    /**
     * @param int $expire default is 3600*24*7
     * @param string $path cookie's path
     */
    private function setupAutoLogin(DataStore $ds, $id, $path = '/', $expire = 604800, $data = null){
        // register information of cookie to database.
        $loginKey = sha1(uniqid().mt_rand());

        $limit = time() + $expire;
        // send auto login cookie.
        setcookie(self::COOKIE_KEY, $loginKey, $limit, $path);

        // set user id to data store
        $ds->set($loginKey, $id, date('Y-m-d H:i:s', $limit));
    }

    private function cleanupAutoLogin(DataStore $ds, $path){
        if (!isset($_COOKIE[self::COOKIE_KEY]))
            return;
        
        // delete old cookie.
        setcookie(self::COOKIE_KEY, '', time() - 3600, $path);

        // delete old information in database.
        $ds->delete($_COOKIE[self::COOKIE_KEY]);
    }

    private function isStartedSession(){
        return ($this->session->get(self::SESSION_STARTED) === true);
    }

    private function startSession(){
        $this->session->add(self::SESSION_STARTED, true);
    }

    public function isLogined(){
        return $this->session->get(self::LOGINED_KEY);
    }
    private function setLogined($id){
        // set user id to session.
        $this->session->add(self::USER_ID_KEY, $id);
        $this->session->add(self::LOGINED_KEY, true);
    }

    public function getUserId(){
        return $this->session->get(self::USER_ID_KEY);
    }

    public function logout($path = '/'){
        $ds = $this->getDataStore();

        $this->session->add(self::LOGINED_KEY, false);
        $this->session->add(self::USER_ID_KEY, null);

        $this->cleanupAutoLogin($ds, $path);
    }

    /**
     *
     * @param int $expire
     * @param string $path
     * @return bool start now?
     */
    public function autoLoginStart($expire = 604800, $path = '/'){
        // Return when session is started.
        if ($this->isStartedSession())
            return false;

        if (!empty($_COOKIE[self::COOKIE_KEY])){
            $ds = $this->getDataStore();
            $value = $ds->get($_COOKIE[self::COOKIE_KEY]);

            // set login flag
            $this->setLogined($value);

            // delete old cookie.
            $this->cleanupAutoLogin($ds, $path);

            // setup auto login
            $this->setupAutoLogin($ds, $value, $path, $expire);
        }

        // start session
        $this->startSession();

        return true;
    }

    public function help()
    {
        $docFile = str_replace('\\', '/', __CLASS__) . '.md';
        return file_get_contents('doc/' . $docFile, FILE_USE_INCLUDE_PATH);
    }
}
