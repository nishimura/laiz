<?php
/**
 * Class file of parsing include section in setting file.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Class of parsing include section in setting file.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Action_Component_Include implements Laiz_Action_Component
{
    /** @var Array Laiz_Parser */
    private $parser;

    public function __construct(Laiz_Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(Array $config)
    {
        $configs = array();
        foreach ($config as $file){
            $file = str_replace('_', '/', $file);
            $cfg = $this->parser->parse($file);
            $configs = array_merge_recursive($configs, $cfg);
        }

        return $configs;
    }

    public function run()
    {
    }
}
