<?php
/**
 * File of class for merging action configration.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Class to create class that inserted request variables from Laiz_Request
 * and action setting files.
 *
 * getClass method is called after execution of all actions,
 * keeping variables from Laiz_Request for view.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Action_Response_Request implements Laiz_Action_Response
{
    private $configs;
    private $request;

    public function __construct(Laiz_Request $req, $configs)
    {
        $this->request = $req;
        $this->configs = $configs;
    }

    public function getClass()
    {
        $a = new StdClass();
        if (isset($this->configs['property']))
            foreach ($this->configs['property'] as $key => $val)
                $a->$key = $val;
        $this->request->setPropertiesByRequest($a);

        if (isset($this->configs['pathinfo']))
            foreach ($this->configs['pathinfo'] as $key => $val)
                $a->$key = $this->request->get($key);

        return $a;
    }
}
