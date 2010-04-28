<?php
/**
 * Class file of filter component.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\action;

/**
 * Class of filter component.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  350
 */
class Component_Display implements Component
{
    /** @var laiz\action\Component_Filter */
    private $filter;

    public function __construct(Component_Filter $filter)
    {
        $this->filter = $filter;
    }

    public function run(Array $config)
    {
        return $this->filter->main($config, 'display');
    }
}
