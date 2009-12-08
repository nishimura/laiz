<?php
/**
 * Check Login Interface File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2009 Satoshi Nishimura
 */

/**
 * checking user login interface
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Laiz_Session_LoginChecker{
    /**
     * ログインチェック用関数
     *
     * @param string $user
     * @param string $pass
     * @return userId need return user id for insert session database
     * @access public
     */
    public function login($user, $pass);
}
