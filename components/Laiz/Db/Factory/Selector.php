<?php
/**
 * Selector of Db_Factory Class File.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 */

/**
 * Selector of Db_Factory Class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Db_Factory_Selector implements Laiz_Db_Factory
{
    protected $config;

    public function __construct()
    {
        $this->config = Laiz_Configure::get('Laiz_Db');
    }

    /**
     * Return factory by name
     *
     * @param string $name
     * @return Db_Factory
     */
    public function select($name)
    {
        switch (true){
        case ($name === 'transaction'):
        case ($name === 'trans'):
            $factory = new Laiz_Db_Factory_Transaction($this->config);
            break;
        case (preg_match('/[A-Z]/', $name)):
            $factory = new Laiz_Db_Factory_View($this->config);
            break;
        default:
            $factory = new Laiz_Db_Factory_Orm($this->config);
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
}
