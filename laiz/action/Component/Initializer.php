<?php
/**
 * Action Component Executed First.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\action;

use \StdClass;
use \laiz\lib\aggregate\laiz\action\Results;

/**
 * Action Component Executed First.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  1
 */
class Component_Initializer implements Component
{
    private $container;
    private $request;
    private $response;
    private $results;

    public function __construct(Request $req, Response $resp, Results $ress)
    {
        $this->request  = $req;
        $this->response = $resp;
        $this->results   = $ress;
    }

    public function run(Array $configs)
    {
        $this->request->setRequestsByConfigs($configs);

        $a = new StdClass();
        if (isset($configs['property']))
            foreach ($configs['property'] as $key => $val)
                $a->$key = $val;

        // override property used request
        Util::setPropertiesByRequest($this->request, $a);

        if (isset($configs['pathinfo']))
            foreach ($configs['pathinfo'] as $key => $val)
                $a->$key = $this->request->get($key);

        $this->response->addObject($a);
        foreach ($this->results as $result)
            $this->response->addObject($result);
    }
}
