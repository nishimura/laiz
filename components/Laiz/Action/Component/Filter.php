<?php
/**
 * Class file of filter component.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 */

/**
 * Class of filter component.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Action_Component_Filter implements Laiz_Action_Component
{
    /** @var array */
    private $config = array();

    /** @var Laiz_Container */
    private $container;

    /** @var Laiz_Parser */
    private $parser;

    /** @var Laiz_Request */
    private $req;

    /** @var int */
    private $priority = 100;

    public function __construct(Laiz_Container $container, Laiz_Parser $parser
                                , Laiz_Request $req)
    {
        $this->container = $container;
        $this->parser = $parser;
        $this->req = $req;
    }

    public function parse(Array $config)
    {
        $this->config = $config;
        return array();
    }

    public function run()
    {
        foreach ($this->config as $key => $val){
            if (strlen(trim($val)) === 0)
                continue;

            $method = 'filter';
            $priority = $this->priority++;

            if (strpos($val, '.')){
                list($class, $method) = explode('.', $val);
            }else{
                $class = $val;
            }

            $thisConfig = array();
            $cfg = $this->parser->parse(str_replace('_', '/', $class));
            if ($cfg !== false)
                $thisConfig = $cfg;
            $obj = $this->container->newInstance($class);
            $this->req->setRequestsByPathInfo($thisConfig);
            $this->req->setPropertiesByRequest($obj);
            // ==check== unused thisConfigs
            $a = new Laiz_Action_Executable_Simple($obj, $method, $thisConfig);
            $this->container->registerInterface($a, $priority);
        }
    }
}
