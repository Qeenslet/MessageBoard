<?php
/**
 * Created by PhpStorm.
 * User: gulidoveg
 * Date: 22.02.19
 * Time: 14:39
 */
require_once ('Model.php');
require_once ('Sypher.php');
class Controller
{
    private $model;
    private $sets;

    public function __construct()
    {
        $this->model = new Model();
        $this->sets = parse_ini_file('conf.ini', true, INI_SCANNER_TYPED);
    }

    public function getAppFolder()
    {
        $path = '';
        if (!empty($this->sets['subfolder']['sitefolder'])){
            $path .= $this->sets['subfolder']['sitefolder'];
        }
        return $path;
    }

    /**
     * Main page
     * @return bool|string
     */
    public function index(){
        $html = file_get_contents(__DIR__ . '/../html/index.html');
        if ($html){
            $path = $this->getAppFolder();
            $globalUser = 'THE_USER = null;';
            if ($this->checkSession()){
                $user = $this->getLoginedUser();
                if ($user){
                    $globalUser = "THE_USER = " . json_encode($user) . ";";
                }
            }
            $html .= '<script>$(function () {' . $globalUser . 'TOTAL_ENTRIES = 0; APP_PATH = \'' . $path . '\'; Controller.renderPage();});</script>';

        }
        return $html ? $html : '<h2>Sorry! Something has been broken!</h2>';
    }


    /**
     * List of messages split by 10
     * @param Request|null $request
     * @return string
     */
    public function json(Request $request = null){
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


    /**
     * Adding message to board
     * @param Request $request
     * @return string
     */
    public function posted(Request $request){
        $posted = $request->getBody();
        $data = [];
        $user = [];
        if ($this->checkSession()){
            $user = $this->getLoginedUser();
            if (!empty($user)){
                $posted['name'] = $user['name'];
                $posted['email'] = $user['email'];
                $posted['user_id'] = $user['id'];
            }
        }
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
        if (!empty($posted['email']) && empty($user) && !filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)){
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


    /**
     * Add new user to DB
     * @param Request $param
     * @return string
     */
    public function newUser(Request $param){
        $userData = $param->getBody();
        foreach ($userData as $k => $value){
            $userData[$k] = trim($value);
        }
        if (empty($userData['u_name'])){
            $data['errors'][] = ['tgt' => 'u_name',
                'msg' => 'Your name is required!'];
        }
        if (empty($userData['u_mail'])){
            $data['errors'][] = ['tgt' => 'u_mail',
                'msg' => 'Your email is required!'];
        }
        if (empty($userData['u_pass'])){
            $data['errors'][] = ['tgt' => 'u_pass',
                'msg' => 'Your password is required!'];
        }
        if (!empty($userData['u_mail']) && !filter_input(INPUT_POST, 'u_mail', FILTER_VALIDATE_EMAIL)){
            $data['errors'][] = ['tgt' => 'u_mail',
                'msg' => 'Your email has incorrect format! It should have the pattern name@domain.zone'];
        }
        if ($this->model->getUserByName($userData['u_name'])){
            $data['errors'][] = ['tgt' => 'u_name',
                'msg' => 'User with the same name already exists!'];
        }
        if (empty($data['errors'])){
            try{
                $userData['u_pass'] = Sypher::encode($userData['u_pass']);
                $this->model->insertData($userData, 'users');
                $data['ok'] = true;
            } catch (Exception $e){
                $data['fail'] = $e->getMessage();
            }
        }
        return json_encode($data);
    }

    /**
     * Check user name and password
     * @param Request $param
     * @return string
     */
    public function checkUser(Request $param){
        $userData = $param->getBody();
        $data = [];
        if ($this->checkCredencials($userData)) {
            $_SESSION['_smart_control'] = true;
            $user = $this->model->getUserByName($userData['u_name']);
            unset($user['u_pass']);
            $_SESSION['_uname'] = $user['u_name'];
            $_SESSION['_uid'] = $user['id'];
            $_SESSION['_umail'] = $user['u_mail'];
            header('location: ' . $this->getAppFolder());

        } else {
            $data['errors'][] = ['tgt' => 'u_name', 'msg' => 'Unknown user'];
            $data['errors'][] = ['tgt' => 'u_pass', 'msg' => 'Or incorrect password'];

        }
        return json_encode($data);
    }


    /**
     * @param $post
     * @return bool
     */
    private function checkCredencials($post){
        if (!empty($post['u_name'])) {
            $data = $this->model->getUserByName($post['u_name']);
            if (empty($data)) return false;

            return Sypher::verify($data['u_pass'], $post['u_pass']);
        }
        return false;
    }


    /**
     * Check if the user is logged in
     * @return bool
     */
    private function checkSession(){
        if (!empty($_SESSION['_smart_control'])) {
            if ($_SESSION['_smart_control'] == true) {
                $_SESSION['_smart_control'] = true;
                return true;
            }
        }
        return false;
    }


    /**
     * Get logined user params
     * @return array
     */
    private function getLoginedUser(){
        if (!empty($_SESSION['_uid']) && !empty($_SESSION['_umail']) && !empty($_SESSION['_uname'])){
            return ['id' => $_SESSION['_uid'], 'email' => $_SESSION['_umail'], 'name' => $_SESSION['_uname']];
        }
        return [];
    }


    /**
     * logout, destroy session
     */
    public function logout(){
        $_SESSION['_smart_control'] = false;
        header('location: ' . $this->getAppFolder());
    }


    /**
     * @param Request $request
     * @return string
     */
    public function delete(Request $request){
        $in = $request->getBody();
        $data = [];
        if ($this->checkSession()){
            $message = $this->model->getMessageById($in['message_id']);
            if (!empty($message['user_id'])){
                $user = $this->getLoginedUser();
                if ($user['id'] == $message['user_id']){
                    if ($this->model->delete('messages', $in['message_id'])){
                        $data['ok'] = true;
                        $data['total'] = $this->model->countMessages();
                    }
                } else {
                    $data['error'] = 'Deletion failed!';
                }
            } else {
                $data['error'] = 'You can delete only your own messages!';
            }
        } else {
            $data['error'] = 'You must be logged in to delete messages!';
        }
        return json_encode($data);
    }

}