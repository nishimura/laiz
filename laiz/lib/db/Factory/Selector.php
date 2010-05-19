<?php
/**
 * Selector of Db_Factory Class File.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\lib\db;

use \laiz\core\Configure;
use \laiz\command\Help;

/**
 * Selector of Db_Factory Class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Factory_Selector implements Factory, Help
{
    protected $config;

    /**
     * Return factory by name
     *
     * @param string $name
     * @return Db_Factory
     */
    public function select($name)
    {
        if (!$this->config)
            $this->config = Configure::get(__NAMESPACE__);

        switch (true){
        case ($name === 'transaction'):
        case ($name === 'trans'):
            $factory = new Factory_Transaction($this->config);
            break;
        case (preg_match('/[A-Z]/', $name)):
            $factory = new Factory_View($this->config);
            break;
        default:
            $factory = new Factory_Orm($this->config);
            break;
        }
        return $factory;
    }

    /**
     * Select and create object for DB.
     *
     * @param string $name
     * @return Object
     */
    public function create($name)
    {
        $factory = $this->select($name);
        return $factory->create($name);
    }

    /**
     * Alias of create.
     *
     * @param string $name
     * @return Object
     */
    public function get($name)
    {
        return $this->create($name);
    }

    public function help()
    {
        $docFile = str_replace('_Selector', '', __CLASS__);
        $docFile = str_replace('\\', '/', $docFile) . '.md';
        return file_get_contents('doc/' . $docFile, FILE_USE_INCLUDE_PATH);
    }
}
