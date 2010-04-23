<?php
/**
 * File of class for aggregation parsers.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\parser;

use \laiz\lib\aggregate\laiz\parser\Parseables;

/**
 * Class for aggregation parsers.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Aggregate
{
    private $parsers;

    public function __construct(Parseables $parsers)
    {
        $this->parsers = $parsers;
    }

    public function parse($name)
    {
        foreach ($this->parsers as $parser){
            preg_match('/([A-Z][^_]*)_[^_]+$/', get_class($parser), $matches);
            if (!isset($matches[1]))
                continue;

            $extension = '';
            switch ($matches[1]){
            case 'Ini':
                $extension = '.ini';
                break;

            case 'Yaml':
                $extension = '.yml';
                break;

            default:
                break;
            }

            if (preg_match('/' . preg_quote($extension) . '$/', $name))
                $file = $name;
            else
                $file = $name . $extension;
            $ret = $parser->parse($file);
            if ($ret !== false)
                return $ret;
        }

        return array();
    }
}
