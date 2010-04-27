<?php
/**
 * Request Interface.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\action;

use \laiz\builder\Singleton;

/**
 * Request Interface.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Request extends Singleton
{
    public function add($name, $value);
    public function get($name);
    public function setRequestsByConfigs(Array $configs);
}
