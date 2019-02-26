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
    private $sets;

    public function __construct()
    {
        $this->model = new Model();
        $this->sets = parse_ini_file('conf.ini', true, INI_SCANNER_TYPED);
    }

    public function index(){
        $html = file_get_contents(__DIR__ . '/../html/index.html');
        if ($html){
            $path = '/';
            if (!empty($this->sets['subfolder']['sitefolder'])){
                $path .= $this->sets['subfolder']['sitefolder'];
            }
            $html .= '<script>$(function () {TOTAL_ENTRIES = 0; APP_PATH = \'' . $path . '\'; Controller.renderPage();});</script>';

        }
        return $html ? $html : '<h2>Sorry! Something has been broken!</h2>';
    }


    public function json($request = null){
        $page = 1;
        if ($request){
            $params = $request->getBody();
            if (!empty($params['p'])){
                $page = $params['p'];
            }
        }
        $data['messages'] = $this->model->getMessages($page);
        $data['total'] = $this->model->countMessages();
        return json_encode($data);
    }


    public function test($param){
        ob_start();
        echo '<pre>'; print_r($param->getBody()); echo '</pre>';
        return ob_get_clean();
    }

    public function posted($request){
        $posted = $request->getBody();
        $data = [];

        foreach ($posted as $k => $value){
            $posted[$k] = trim($value);
        }
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
                $data['fail'] = $e->getMessage();
            }
        }
        $data['total'] = $this->model->countMessages();

        return json_encode($data);
    }
}