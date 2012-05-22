<?php
/**
 * O/R Mapper Mock with Session Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\lib\db;

use \laiz\lib\session\Session;
use \laiz\lib\db\Vo;

/**
 * O/R Mapper Mock with Session Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Orm_Session extends Orm_Mock
{
    private $s;
    private $className;

    public function __construct(Session $s, $name)
    {
        parent::__construct($name);
        $this->className =
            join('', array_map('ucfirst', explode('_', $name)));
        $this->s = $s;
    }

    public function setVoName($name)
    {
        $this->voName = $name;
    }

    public function createVo()
    {
        $ns = 'laiz\\lib\\db\\';
        $className = 'Vo_'. $this->className;
        if (!class_exists($ns . $className, false)){
            eval("namespace laiz\\lib\\db;\nclass $className implements Vo{}");
        }
        $className = $ns . $className;
        return new $className();
    }

    private function getVosFromSession()
    {
        $a = $this->s->get('__Session_Vo__');
        if (isset($a[$this->className]))
            return $a[$this->className];
        else
            return array();
    }

    public function getVo($where = null)
    {
        $vos = $this->getVosFromSession();

        if (count($vos) == 0)
            return null;

        if (is_numeric($where) && isset($vos[$where])){
            $vo = $vos[$where];
        }else if (is_array($where)){
            foreach ($vos as $a){
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
            reset($vos);
            $tmp = each($vos);
            $vo = $tmp['value'];
        }
        if (!isset($vo)){
            $vo = null;
        }
        return $vo;
    }

    public function getVos($options = array())
    {
        return $this->getVosFromSession();
    }

    private function saveVoToSession($vo)
    {
        $a = $this->s->get('__Session_Vo__');
        $keyName = $this->primaryKeyName;
        $a[$this->className][$vo->$keyName] = $vo;
        $this->s->add('__Session_Vo__', $a);
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
            $key = count($this->getVos())+1;
            $vo->$keyName = $key;
        }

        $this->saveVoToSession($vo);
    }

    public function currval()
    {
        $max = 0;
        foreach ($this->getVos() as $vo)
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
