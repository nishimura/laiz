<?php
/**
 * Check Login Interface File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\lib\session;

/**
 * checking user login interface
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Login
{
    /**
     * check user and password.
     *
     * @param string $user
     * @param string $pass
     * @return userId need return user id for insert session database
     * @access public
     */
    public function login($user, $pass);
}
