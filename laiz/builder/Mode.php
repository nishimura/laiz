<?php
/**
 * Building and Setting with Framework Run Mode.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\builder;

/**
 * Building and Setting with Framework Run Mode.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Mode extends Aggregatable
{
    /**
     * Setting mode by config.ini or environment arguments.
     *
     * @return bool
     */
    public function accept();

    /**
     * Building components for specific mode.
     *
     * Implemented Method must create
     * laiz\action\Request, laiz\lib\db\Orm and any other components.
     * See Mode_Development, Mode_Mock and Mode_Commandline.
     */
    public function buildComponents(Container $container);
}
