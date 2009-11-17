<?php
/**
 * Class file of parsing hidden section in setting file.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Class of parsing hidden section in setting file.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Action_Component_Hidden implements Laiz_Action_Component
{
    private $hiddens = array();

    public function parse(Array $config)
    {
        foreach ($config as $key => $value){
            if (preg_match('/^([^:]+):(.+)$/', $key, $matches)){
                $this->hiddens[$matches[1]][$matches[2]] = $value;
            }else{
                trigger_error("Failed of parsing $key key in hidden section.");
                return false;
            }
        }
    }

    public function run()
    {
    }

    public function getHiddens()
    {
        return $this->hiddens;
    }
}
