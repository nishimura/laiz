<?php
/**
 * Interface file of executable action class.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Interface of executable action class.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Laiz_Action_Executable extends Laiz_Action_Response
{
    public function getMethod();
}
