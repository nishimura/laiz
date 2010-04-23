<?php
/**
 * Simple O/R Mapper Iterator Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\lib\db;

use \Iterator as BuiltinIterator;
use \PDO;
use \PDOStatement;
use \StdClass;

/**
 * Simple O/R Mapper Iterator Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Iterator_View implements Iterator, BuiltinIterator
{
    protected $dao;
    protected $sqlFile;
    protected $params;
    protected $replacements;
    protected $key;
    protected $vo;
    protected $isContinue;

    public function __construct(View $dao, $sqlFile)
    {
        $this->sqlFile = $sqlFile;
        $this->setVo($dao->createVo($sqlFile));
        $this->dao = $dao;
    }

    protected function setVo(Vo $vo)
    {
        $this->vo = $vo;
    }

    /**
     * Set arguments of prepared statement.
     *
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Format string of replacement SQL.
     *
     * @param string $file
     */
    public function setReplacements($reps)
    {
        $this->replacements = $reps;
        return $this;
    }

    protected function getStatement(){
        return $this->dao->prepareStmt($this->sqlFile, $this->params, $this->replacements);
    }

    protected function bind(PDOStatement $stmt, $vo){
        $this->dao->bind($stmt, $vo);
    }

    public function rewind(){
        $this->stmt = $this->getStatement();
        if (!$this->stmt instanceof PDOStatement){
            trigger_error('Can not get PDOStatement.', E_USER_WARNING);
            return;
        }

        if (!is_object($this->vo))
            $this->vo = new StdClass;
        $this->bind($this->stmt, $this->vo);
        $this->key = 0;
        $this->isContinue = $this->stmt->fetch(PDO::FETCH_BOUND);
    }

    public function current()
    {
        return $this->vo;
    }

    public function key(){
        return $this->key;
    }

    public function next(){
        $this->isContinue = $this->stmt->fetch(PDO::FETCH_BOUND);
    }

    public function valid(){
        return $this->isContinue;;
    }

    public function count()
    {
        $stmt = $this->getStatement();
        return $stmt->rowCount();
        // Warning: This function was checked PostgreSQL only.
    }
        
}
