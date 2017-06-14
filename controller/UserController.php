<?php

/**
 * UserController
 *
 * @author bumer
 */

include_once __DIR__ . '/BaseController.php';

class UserController extends BaseController {
         
    public function loginAction()
    {
        /* Страница авторизации */
       
        $msg = '';
        //проверка на POST или GET
        if (count($_POST) > 0) {
            $mUsers = new UserModel(DB::get());      
            $userLogin = $mUsers->getOne($_POST['login']);
            if($_POST['login'] == '' || $_POST['password'] == ''){
                $msg = 'Заполните все поля'; 
            } elseif ($_POST['login'] != '' && $userLogin === false){
                $msg = 'Пользователя с таким логином не существует';
            } else {
                if($_POST['login'] == $userLogin['login'] && Core::myCrypt($_POST['password']) == $userLogin['password']){
                    $_SESSION['auth'] = true;
                    $_SESSION['login'] = $userLogin['login'];

                    if(isset($_POST['remember'])){
                        setcookie('login', $userLogin['login'], time() + 3600 * 24 * 365);
                        setcookie('password', $userLogin['password'], time() + 3600 * 24 * 365);
                    }
                    header("Location:". BASE_PATH . 'posts');
                    exit();
                } else {
                    $msg = 'Неправильный пароль!';
                }
            }     
        } else {
            unset($_SESSION['auth']);
            Core::deleteCookie('login');
            Core::deleteCookie('password');
            $this->login = false;
            $this->user = 'Гость';
            if (isset($_GET['success']) && !empty($_GET['success'])){
                $success = $_GET['success'];
                $msgs = ['reg' => 'Регистрация прошла успешно, теперь вы можете авторизоваться'];

                $msg = ($msgs[$success]) ? $msgs[$success] : '';

            } 
        }
    
        $this->content = $this->template('view_login', [
            'msg'  => $msg
        ]);

        $this->title = 'Авторизация';
        $this->aside = '';
    }
    
    public function regAction()
    {
        $msg = '';
        if(count($_POST) > 0){
            $mUsers = new UserModel(DB::get()); 
            $login = trim(htmlspecialchars($_POST['login']));
            $password = trim(htmlspecialchars($_POST['password']));
            if($login == '' || $password == ''){
                $msg = 'Заполните все поля';
            } elseif (!Core::checkName($login, 'user')){ 
                $msg = 'Запрещенные символы в поле "Логин"'; 
            } elseif ($mUsers->getOne($login)!==false){
                $msg = 'Логин занят!'; 
            } else {
                $mUsers->add (['login'=> $login, 'password' => Core::myCrypt($password)]);     
                header("Location: ".BASE_PATH. "login?success=reg");
                exit();
            } 
        } else {
            /* зашли на страницу методом GET */

            $login = '';
            $password = '';
            $msg = '';
        }

        $this->content = $this->template('view_reg', [
            'back' => $_SERVER['HTTP_REFERER'],
            'msg'  => $msg,
            'login' => $login
        ]);
        $this-> title = 'Регистрация'; 
        $this->aside = '';
    }
    
    
}