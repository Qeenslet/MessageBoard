<?php
/**
 * Created by PhpStorm.
 * User: gulidoveg
 * Date: 22.02.19
 * Time: 14:39
 */

class Controller
{
    public function index(){
        $html = file_get_contents(__DIR__ . '/../html/index.html');
        return $html ? $html : '<h2>Sorry! Something has been broken!</h2>';
    }


    public function test($param){
        ob_start();
        echo '<pre>'; print_r($param); echo '</pre>';
        return ob_get_clean();
    }
}