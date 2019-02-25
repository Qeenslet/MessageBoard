<?php
/**
 * Created by PhpStorm.
 * User: gulidoveg
 * Date: 22.02.19
 * Time: 14:39
 */
require_once ('Model.php');

class Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public function index(){
        $html = file_get_contents(__DIR__ . '/../html/index.html');
        return $html ? $html : '<h2>Sorry! Something has been broken!</h2>';
    }


    public function json(){
        return json_encode($this->model->getMessages());
    }


    public function test($param){
        ob_start();
        echo '<pre>'; print_r($param); echo '</pre>';
        return ob_get_clean();
    }

    public function posted($request){
        $posted = $request->getBody();
        $data = [];
        if (empty($posted['name'])){
            $data['errors'][] = ['tgt' => 'name',
                                   'msg' => 'Your name is required!'];
        }
        if (empty($posted['email'])){
            $data['errors'][] = ['tgt' => 'email',
                                   'msg' => 'Your email is required!'];
        }
        if (empty($posted['html'])){
            $data['errors'][] = ['tgt' => 'html',
                                   'msg' => 'Your message is empty!'];
        }
        if (!empty($posted['email']) && !filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)){
            $data['errors'][] = ['tgt' => 'email',
                                   'msg' => 'Your email has incorrect format! It should have the pattern name@domain.zone'];
        }
        if (empty($data['errors'])){
            try{
                $this->model->insertData($posted, 'messages');
                $data['ok'] = $posted;
            } catch (Exception $e){

            }
        }


        return json_encode($data);
    }
}