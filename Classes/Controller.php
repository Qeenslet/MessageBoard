<?php
/**
 * Created by PhpStorm.
 * User: gulidoveg
 * Date: 22.02.19
 * Time: 14:39
 */
require_once ('DB.php');

class Controller
{
    private $db;

    public function __construct()
    {
        $this->db = DB::establishConnction();
    }

    public function index(){
        $html = file_get_contents(__DIR__ . '/../html/index.html');
        return $html ? $html : '<h2>Sorry! Something has been broken!</h2>';
    }


    public function json(){

        $result = $this->db->fetchAll("SELECT * FROM messages ORDER BY id DESC LIMIT 10");
        return json_encode($result);
    }


    public function test($param){
        ob_start();
        echo '<pre>'; print_r($param); echo '</pre>';
        return ob_get_clean();
    }

    public function posted($request){
        $aaa = $request->getBody();
        $aaa['control'] = 'controlled';
        return json_encode($aaa);
    }
}