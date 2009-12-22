<?php
class Laiz_Db_Factory_Session implements Laiz_Db_Factory, Laiz_Autoload_Component
{
    private $s;
    private $orms = array();
    public function __construct(Laiz_Session $s)
    {
        $this->s = $s;
    }

    /**
     * Ini setting is needed to start session using object.
     * For example, Laiz_Db_Factory_Session = 10 in components.ini.
     */
    public function autoload($name)
    {
        if (preg_match('/^Laiz_Db_Vo_/', $name)){
            $createName = str_replace('Laiz_Db_Vo_', '', $name);
            $createName[0] = strtolower($createName[0]);
            $dao = $this->create($createName);
            $dao->createVo($name);
        }
    }

    public function create($name)
    {
        if (!isset($this->orms[$name]))
            $this->orms[$name] = new Laiz_Db_Orm_Session($this->s, $name);
        return $this->orms[$name];
    }
}
