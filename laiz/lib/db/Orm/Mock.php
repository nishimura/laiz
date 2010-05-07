<?php
/**
 * O/R Mapper Mock Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\lib\db;

/**
 * O/R Mapper Mock Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Orm_Mock implements Orm
{
    protected $primaryKeyName;
    protected $voName = 'StdClass';
    protected $vos = array();

    public function __construct($name)
    {
        $key = join('', array_map('ucfirst', explode('_', $name))) . 'Id';
        $key[0] = strtolower($key[0]);
        $this->primaryKeyName = $key;
            
    }

    public function setVoName($name)
    {
        $this->voName = $name;
    }

    public function createVo()
    {
        return new $this->voName();
    }

    public function getVo($where = null)
    {
        if (count($this->vos) == 0)
            return null;

        if (is_numeric($where) && isset($this->vos[$where])){
            $vo = $this->vos[$where];
        }else if (is_array($where)){
            foreach ($this->vos as $a){
                $match = true;
                foreach ($where as $k => $v){
                    if ($a->$k != $v)
                        $match = false;
                }
                if ($match){
                    $vo = $a;
                    break;
                }
            }
        }else{
            reset($this->vos);
            $tmp = each($this->vos);
            $vo = $tmp['value'];
        }
        if (!isset($vo)){
            $vo = null;
        }
        return $vo;
    }

    public function getVos($options = array())
    {
        return $this->vos;
    }

    public function save($vo)
    {
        if (is_array($vo))
            foreach ($vo as $v)
                $this->_save($v);
        else
            $this->_save($vo);
        return true;
    }

    private function _save($vo)
    {
        $keyName = $this->primaryKeyName;
        if (!isset($vo->$keyName)){
            $key = count($this->vos)+1;
            $vo->$keyName = $key;
        }

        $this->vos[$vo->$keyName] = $vo;
    }

    public function delete($vo)
    {
        $keyName = $this->primaryKeyName;
        if (is_object($vo))
            $key = $vo->$keyName;
        else
            $key = $vo;
        unset($this->vos[$key]);
        return 1;
    }

    public function currval()
    {
        $max = 0;
        foreach ($this->vos as $vo)
            if ($max < $vo->{$this->primaryKeyName})
                $max = $vo->{$this->primaryKeyName};
        return $max;
    }

    public function begin(){}
    public function commit(){}

    /** unimplementation abort method. */
    public function abort()
    {
        trigger_error('Called abort method!');
    }
}
