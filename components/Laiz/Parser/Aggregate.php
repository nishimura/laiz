<?php
/**
 * File of class for aggregation parsers.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 */

/**
 * Class for aggregation parsers.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Parser_Aggregate implements Laiz_Parser
{
    private $parsers;

    public function __construct(Array $laizParsers)
    {
        $this->parsers = $laizParsers;
    }

    public function parse($name)
    {
        foreach ($this->parsers as $parser){
            if ($parser === $this)
                continue;

            preg_match('/_([A-Z][^_]*)_[^_]+$/', get_class($parser), $matches);
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
