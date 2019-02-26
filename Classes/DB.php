<?php

class DB
{
    private $db;
    private static $instance;
    private $dbType = '';
    private $dbName;

    /**
     * DB constructor.
     */
    private function __construct(){
        $this->connect();
    }


    /**
     * @return PDO
     * @throws Exception
     */
    private function getPDObject(){
        $sets = parse_ini_file('conf.ini', true, INI_SCANNER_TYPED);
        try{
            if ($sets){
                if (!empty($sets['database_type'])){
                    if (!empty($sets['database_type']['mysql']) && !empty($sets['mysql'])){
                        if (!empty($sets['mysql']['host']) &&
                            !empty($sets['mysql']['dbname']) &&
                            !empty($sets['mysql']['username']) &&
                            !empty($sets['mysql']['password'])){
                            $pdoString = 'mysql:dbname=' . $sets['mysql']['dbname'] . ';host=' . $sets['mysql']['host'];
                            $this->dbType = 'mysql';
                            $this->dbName = $sets['mysql']['dbname'];
                            return new PDO($pdoString, $sets['mysql']['username'], $sets['mysql']['password']);
                        } else throw new Exception('Not enough params to use database!');
                    } else if (!empty($sets['database_type']['mysqlite'])) {
                        if (!empty($sets['mysqlite']) && !empty($sets['mysqlite']['filename'])) $filename = $sets['mysqlite']['filename'];
                        else $filename = 'default.db';

                        $this->dbType = 'sqlite';
                        $this->dbName = $filename;
                        return new PDO('sqlite:' . $filename);
                    }
                } else {
                    throw new Exception('Not specified database type');
                }
            } else {
                throw new Exception('No settings!');
            }
        } catch (PDOException $e){
            throw new Exception($e->getMessage());
        }

    }

    /**
     * Получение экземпляра класса
     *
     * @return DB
     */
    public static function establishConnction(){
        if (empty(self::$instance))
        {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * @return mixed
     */
    public function getDB(){
        return $this->db;
    }

    /**
     *
     */
    private function connect(){
        try { // Создаем или открываем созданную ранее базу данных
            $db = $this->getPDObject(); // Создаем таблицы, если не найдены
            if ($this->dbType === 'sqlite')
            {
                $st = $db->prepare('SELECT type FROM sqlite_master WHERE name = :tablename');
                $st->execute([':tablename' => 'messages']);
            }
            else if ($this->dbType === 'mysql'){
                $st = $db->prepare("SELECT table_name FROM information_schema.tables
                                            WHERE table_schema = :dbname 
                                             AND table_name = :tablename
                                            LIMIT 1");
                $st->execute([':tablename' => 'messages', ':dbname' => $this->dbName]);
            }
            else $st = null;
            $result = $st->fetchAll();
            //$st3 = $db->query('SELECT name FROM sqlite_master WHERE name = \'users\'');
            if ($this->dbType === 'sqlite')
            {
                $st3 = $db->prepare('SELECT type FROM sqlite_master WHERE name = :tablename');
                $st3->execute([':tablename' => 'users']);
            }
            else if ($this->dbType === 'mysql'){
                $st3 = $db->prepare("SELECT table_name FROM information_schema.tables
                                            WHERE table_schema = :dbname 
                                             AND table_name = :tablename
                                            LIMIT 1");
                $st3->execute([':tablename' => 'users', ':dbname' => $this->dbName]);
            }
            else $st3 = null;


            $result3 = $st3->fetchAll();

            if (sizeof($result) == 0 ||
                sizeof($result3) == 0) {
                $this->makeTable($db);
                $this->db = $db;
            } else {

                $this->db = $db;
            }

        } catch (Exception $e) {
            echo $e->getMessage();
            die;
            //die($e->getMessage());
        }

    }

    private function makeTable(PDO $db){

        $autoIncr = $this->dbType === 'mysql' ? 'AUTO_INCREMENT' : 'AUTOINCREMENT';
        $timestamp =  $this->dbType === 'mysql' ?
            "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP" :
            "DATETIME DEFAULT CURRENT_TIMESTAMP";
        try{
           $db->exec('CREATE TABLE IF NOT EXISTS messages ( id INTEGER NOT NULL PRIMARY KEY ' . $autoIncr . ',
                                                             name VARCHAR(255) NOT NULL,
                                                             email VARCHAR(255) NOT NULL,
                                                             user_id INTEGER NULL,
                                                             html TEXT NOT NULL,
                                                             `date_added` ' . $timestamp . ');');
            $db->exec('CREATE TABLE IF NOT EXISTS users ( id INTEGER NOT NULL PRIMARY KEY ' . $autoIncr . ',
                                                             u_name VARCHAR(255) NOT NULL,
                                                             date_added ' . $timestamp . ',
                                                             u_mail VARCHAR(255) NOT NULL,
                                                             u_pass VARCHAR(255) NOT NULL);');

        } catch (PDOException $e){
            echo $e->getMessage();
        }

    }



    public function fetchAll($query, $params = []){
        if (!$params){
            $st3 = $this->db->query($query, PDO::FETCH_ASSOC);
            return $st3->fetchAll();
        }
    }
}