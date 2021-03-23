<?php
namespace Db;
use PDO;    

/**
 *  Classe que gerencia conexões com BDs através de arquivos de configuração
 */
final class Connection {
    
    private static $name = "../database.ini"; //nome do arquivo ini com as configurações de conexão com o banco

    /**
     *  não haverá instancias de Connection por isso seu construtor será private
     */
    private function __construct() {}

    /**
     *  Lê o arquivo de config e instancia o objeto PDO correspondente
     * @param $name = nome do arquivo .ini de configuração
     */
    public static function open() {       
        try {
            //lê o .ini e retorna uma array com as configurações
            $db = parse_ini_file(self::$name);
        } catch (\Exception $e) {
            die('Erro ao ler o arquivo de configuração!');
        }

        //informações contidas no arquivo
        $user = $db['user'];
        $pass = $db['pass'];
        $base = $db['name'];
        $host = $db['host'];
        $type = $db['type'];

        switch ($type) {
            case 'pgsql':
                $conn = new PDO("pgsql:dbname={$base};user={$user};password={$pass};host={$host}");
                break;
            case 'mysql':
                $conn = new PDO("mysql:host={$host};port={3306};dbname={$base}", $user, $pass);
                break;
            case 'sqlite':
                $conn = new PDO("sqlite:{$base}");
                break;
        }

        //define lançamento de Exceptions do PDO
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }

}