<?php

use laiz\lib\test\ActionTest;
use laiz\lib\test\Assert;

use laiz\lib\data\DataStore;
use laiz\lib\data\DataStore_Mock;

class Base_Action_ActionTest2 implements ActionTest
{
    public $prop1;
    public $prop2;
    public $prop3;
    public $ret;
    public function act(DataStore $dataStore)
    {
        $dataStore->set('foo', 'bar');
        return 'baz';
    }

    /**
     * @ActionTest selfact
     */
    public function testSelfact($a)
    {
        $ds = new DataStore_Mock();
        $ret = $this->act($ds);
        $a->equal($ds->get('foo'), 'bar');
        $a->equal($ret, 'baz');
    }

    /**
     * @ActionTest arguments:laiz\lib\data\DataStore_Mock
     * @ActionTest return:baz
     */
    public function testArguments($a, $ds)
    {
        $a->equal($ds->get('foo'), 'bar');
    }

    public function getActionName()
    {
        return 'ActionTest2';
    }
}
