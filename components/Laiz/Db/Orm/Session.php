<?php
/**
 * O/R Mapper Mock with Session Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 */

/**
 * O/R Mapper Mock with Session Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Db_Orm_Session extends Laiz_Db_Orm_Mock
{
    private $s;
    private $className;

    public function __construct(Laiz_Session $s, $name)
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
        $className = 'Laiz_Db_Vo_'. $this->className;
        $dirs = Laiz_Configure::get('LaizContainer');
        if (!class_exists($className, false)){
            eval("class $className implements Laiz_Db_Vo{}");
        }
        return new $className();
    }

    private function getVosFromSession()
    {
        $a = $this->s->get('__Session_Vo__');
        return $a[$this->className];
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
            $key = count($this->vos)+1;
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
