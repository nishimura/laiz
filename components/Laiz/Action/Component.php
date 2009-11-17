<?php
/**
 * Interface File of action class configure.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Interface of action class configure.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Laiz_Action_Component
{
    /**
     * @param array $configs configuration in ini file.
     * @return array parsed config of this component
     */
    public function parse(Array $config);

    /**
     * run component
     */
    public function run();
}
