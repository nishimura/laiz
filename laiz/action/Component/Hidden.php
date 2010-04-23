<?php
/**
 * Class file of parsing hidden section in setting file.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\action;

/**
 * Class of parsing hidden section in setting file.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  280
 */
class Component_Hidden implements Component
{
    private $hiddens = array();

    public function run(Array $config)
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

    public function getHiddens()
    {
        return $this->hiddens;
    }
}
