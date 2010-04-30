laiz.lib.db.Factory
===================

Database Utility Class.
Create method returns O/R Mapper or Iterator.

Example:

    <?php
    use laiz\lib\db\Factory;
    class FooAction
    {
        public $items;
        public $item;
        public function act(Factory $factory)
        {
            // Getting O/R Mapper of 'item' table for one Object.
            $itemOrm = $factory->create('item');
            $itemKey = 123;
            $this->item = $itemOrm->getVo($itemKey);

            // Getting iterator of 'item' table.
            $this->items = $factory->create('items');

            // Getting iterator with plain sql text.
            $this->items = $factory->create(__CLASS__);
            // This is reading from FooAction.sql
        }
    }

