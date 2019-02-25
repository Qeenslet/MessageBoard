<?php
/**
 * Created by PhpStorm.
 * User: gulidoveg
 * Date: 25.02.19
 * Time: 16:30
 */

require_once('DB.php');
class Model
{
    protected $db;

    public function __construct(){
        $this->db = DB::establishConnction()->getDB();
    }


    public function getMessages(){
        return $this->fetchAll("SELECT * FROM messages ORDER BY id ASC LIMIT 10");
    }


    protected function fetchAll($sql, $params = []){

        if (!empty($params))
        {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            $st = $this->db->query($sql);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }
    }


    protected function fetchRow(&$sql, $params){
        if (!empty($params))
        {
            $stmt = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        else
        {
            $st = $this->db->query($sql);
            return $st->fetch(PDO::FETCH_ASSOC);
        }
    }


    protected function prepareInsert($array){
        $part1 = [];
        $part2 = [];
        foreach ($array as $key => $value)
        {
            $part1[] = $key;
            $part2[] = ':' . $key;
        }
        $p1 = implode(', ', $part1);
        $p2 = implode(', ', $part2);
        return ('(' . $p1 . ') VALUES (' . $p2 . ')');
    }

    protected function prepareUpdate($array){
        $string = ' SET ';
        $tmp = [];
        foreach ($array as $key => $value)
        {
            $tmp[] = $key . ' = :' . $key;
        }
        $string .= implode(', ', $tmp);
        return $string . ' ';
    }



    public function getUserByName($name){
        $sql = "SELECT u_pass, id FROM users WHERE u_name = :uname";
        return $this->fetchRow($sql, array('uname' => $name));
    }


    public function delete($table, $id){
        try
        {
            $sql = "DELETE FROM `" . strval($table) . "` WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        }
        catch (Exception $e)
        {
            return false;
        }
    }


    public function insertData($save, $table){

        try
        {
            $SQL = 'INSERT INTO `' . $table . '` ' . $this->prepareInsert($save);
            //echo '<pre>'; print_r($save); print_r($SQL); die;
            $this->db->prepare($SQL)->execute($save);
            return 0;
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }

    }
}