<?php
/**
 * Class file of parsing validator section in setting file.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\action;

use laiz\builder\Container;

/**
 * Class of parsing validator section in setting file.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  20
 */
class Component_Validator implements Component
{
    /** @var Laiz_Container */
    private $container;

    /** @var array Validator config */
    private $config = array();

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function run(Array $config)
    {
        $filter = $this->container->create('Laiz_Validator_Filter');
        $filter->setConfig($config);

        $a = new Laiz_Action_Executable_Simple($filter, 'run');
        $this->container->registerInterface($a, 10);
    }
}
