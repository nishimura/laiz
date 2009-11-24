<?php
/**
 * File of class for auto creation components by action ini file.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 */

/**
 * Class for auto creation components by action ini file.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Action_Component_Runner
{
    /** @var array */
    private $ignoreSections = array('property', 'method', 'pathinfo', 'result', 'view', 'class');

    /** @var Laiz_Container */
    private $container;

    /** @var array */
    private $aliases = array();

    public function __construct(Laiz_Container $container)
    {
        $this->container = $container;
    }

    public function setAliasFile(Laiz_Parser_Ini $parser, $file)
    {
        $this->aliases = $parser->parseIniFile($file);
    }

    /**
     * Parse configuration of action.
     * 
     * @param array $configs 
     */
    public function run(Array $configs)
    {
        $new = $configs;
        foreach ($configs as $section => $config){
            // use $new[$section] for include new config
            $ret = $this->parseLine($section, $new[$section]);
            if (is_array($ret) && $ret)
                $new = array_merge_recursive($ret, $new);
        }

        return $new;
    }

    private function parseLine($section, $config)
    {
        if (!is_array($config))
            return;

        if (in_array($section, $this->ignoreSections))
            return;

        if (isset($this->aliases[$section]))
            $componentName = $this->aliases[$section];
        else
            $componentName = $section;

        $component = $this->container->create($componentName);
        if (!$component)
            return;

        if (!($component instanceof Laiz_Action_Component)){
            trigger_error(get_class($component) . ' don\'t implement Laiz_Action_Component.', E_USER_WARNING);
            return;
        }

        $ret = $component->parse($config);
        // recursive parse config
        if (is_array($ret) && $ret)
            $ret = $this->run($ret);
        $component->run();

        return $ret;
    }

}
