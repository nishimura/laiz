<?php
/**
 * Action Preparer Interface.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\action;

/**
 * Action Preparer Interface.
 *
 * @see laiz.action.Component_Runner
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Preparer
{
    /**
     * Prepare needed processing and run action.
     *
     * @param callback $setter
     * @return string || null
     */
    public function prepare($setter);
}
