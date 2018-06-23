<?php

/**
 *  Classe que gerencia transações com o banco de dados
 */
final class Transaction {

    private static $conn; //conexão ativa
    private static $logger; //objeto de log

    /**
     *  haverá apenas uma instância de Transaction por isso seu construtor será private
     */
    private function __construct() {
        
    }

    /**
     *  Abre uma transação e por consequência uma conexão ao BD
     */
    public static function open() {
        try {
            //abre conexão e armazena em $conn
            if (empty(self::$conn)) {
                self::$conn = Connection::open();
                //iniciando transação
                self::$conn->beginTransaction();
                self::$logger = null;
            }
        } catch (PDOException $ePdo) {
            throw new Exception($ePdo->getMessage());
        }
    }

    /**
     *  Retorna a conexão ativa da transação
     */
    public static function get() {
        return self::$conn;
    }

    /**
     *  desfaz operações da transação ativa a fecha a conexão
     */
    public static function rollback() {
        if (self::$conn) {
            self::$conn->rollback();
            self::$conn = null;
        }
    }

    /**
     *  Commita e fecha a conexão ativa
     */
    public static function commit() {
        if (self::$conn) {
            self::$conn->commit();
            self::$conn = null;
        }
    }

    /**
     * fecha a conexão ativa
     */
    public static function close() {
        if (self::$conn) {
            self::$conn = null;
        }
    }

    /**
     *  define qual tipo de log será usado
     * @param $logger = objeto Logger
     */
    public static function setLogger(Logger $logger) {
        self::$logger = $logger;
    }

    /**
     *  armazena a mensagem de log
     * @param $message = mensagem de log
     */
    public static function log($message) {
        if (self::$logger) {
            self::$logger->write($message);
        }
    }

}
