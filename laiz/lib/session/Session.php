<?php
/**
 * Interface file of framework session.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\lib\session;

use \laiz\builder\Singleton;

/**
 * Interface of framework session.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Session extends Singleton
{
    public function get($name);
    public function add($name, $value);
}
