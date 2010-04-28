<?php
/**
 * Interface File of action class configure.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\action;

use \laiz\builder\Aggregatable;
use \laiz\builder\Singleton;

/**
 * Interface of action class configure.
 *
 * priority:
 *   Initializer   :   1
 *   Converter     :  10
 *   Validator     :  20
 *   Filter        :  80
 *   Action        : 200
 *   ViewConfigure : 300
 *   Display       : 350
 *   Hidden        : 380
 *   ViewRunner    : 500
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Component extends Aggregatable, Singleton
{
    /**
     * run component
     */
    public function run(Array $config);
}
