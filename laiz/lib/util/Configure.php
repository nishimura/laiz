<?php
/**
 * Configure Class for User.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\lib\util;

use laiz\core\Configure as Core;

/**
 * Configure Class for User.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Configure
{
    static public function getCacheDir()
    {
        $base = Core::get('base');
        return $base['CACHE_DIR'];
    }
}
