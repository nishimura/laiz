<?php
/**
 * Class file of parsing validator section in setting file.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Class of parsing validator section in setting file.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Action_Component_Validator implements Laiz_Action_Component
{
    /** @var Laiz_Container */
    private $container;

    /** @var array Validator config */
    private $config = array();

    public function __construct(Laiz_Container $container)
    {
        $this->container = $container;
    }

    public function parse(Array $config)
    {
        $this->config = $config;
    }

    public function run()
    {
        $filter = $this->container->create('Laiz_Validator_Filter');
        $filter->setConfig($this->config);

        $a = new Laiz_Action_Executable_Simple($filter, 'run');
        $this->container->registerInterface($a, 10);
    }
}
