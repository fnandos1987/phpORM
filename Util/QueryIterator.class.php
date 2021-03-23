<?php
namespace Util;
use Db\Transaction, Db\LoggerTXT;
/**
 * Classe base para interação sobre registros vindos de selects no banco de dados
 * @author fernando.schwmbach
 */
abstract class QueryIterator implements Iterator {

    protected $Query;
    protected $line;
    protected $key = 0;

    protected function executeStmt() {
        try {
            $this->Query->execute();
        } catch (\Exception $e) {
            $this->addLog($e->getMessage());
        }
    }

    abstract protected function fetchObject();

    public function __construct(\PDOStatement $stmt) {
        $this->Query = $stmt;
    }

    public function current() {
        return $this->line;
    }

    public function key() {
        return $this->key;
    }

    public function next() {
        $this->line = $this->fetchObject();
        $this->key++;
    }

    public function rewind() {
        $this->key = 0;
        $this->executeStmt();
        $this->next();
    }

    public function valid() {
        if ($this->line === false) {
            Transaction::close();
            return false;
        }
        return true;
    }

    private function addLog($sEntry) {
        Transaction::setLogger(new LoggerTXT('../log.txt'));
        Transaction::log($sEntry);
    }

}