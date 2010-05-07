<?php
/**
 * O/R Mapper Interface File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\lib\db;

/**
 * O/R Mapper Interface
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Orm
{
    public function getVo($where = null);
    public function getVos($options = array());
    public function save($vo);
    public function delete($vo);
    public function createVo();
    public function currval();
    public function begin();
    public function commit();
    public function abort();
}
