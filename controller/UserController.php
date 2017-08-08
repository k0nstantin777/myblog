<?php

/**
 * UserController
 *
 * @author Noskov Konstantin <noskov.kos@gmail.com>
 */

namespace controller;

use controller\BaseController,
    model\UserModel,
    model\SessionModel,
    core\database\DB,
    core\module\User,    
    core\database\DBDriver,
    core\Request,
    core\ServiceContainer,
    core\module\Validator, 
    core\exception\ValidatorException,
    core\exception\UserException,    
    core\exception\PageNotFoundException;


class UserController extends BaseController {
         
    /**
     * Объект класса UserModel
     * @var object 
     */
    public $mUser;
     
    /**
     * Объект класса User
     * @var object 
     */
    //public $userObj;
    
    public $mSession;


     public function __construct(Request $request, ServiceContainer $container)
    {
        parent::__construct($request, $container);
        //$this->mUser = new UserModel(new DBDriver(DB::get()), new Validator());
        $this->mUser = $this->container->get('model.user');
        
    }   
    
    
    /* авторизация */
    public function loginAction()
    {
        /* Страница авторизации */
        $msg = '';
        $errors = [];
                        
        //проверка на POST или GET
        if ($this->request->isPost()) {
           
            try {
                $this->user->login();     
                header("Location:". BASE_PATH . 'posts');
                exit();
            } catch (UserException $e) {
                $errors = $e->getErrors();
                $msg = $e->getMessage();
            }
        }
          
        $this->content = $this->template('view_login', [
            'errors'  => $errors,
            'post' => $this->request->post,
            'msg' => $msg,
            'login' => $this->login
        ]);

        $this->title = 'Авторизация';
    }
    
    /* регистрация нового пользователя */
    public function regAction()
    {
        $errors = [];
        $msg = '';
        $this->mUser->setSchema(['login', 'password']);
        if($this->request->isPost()){
            
            try {
                $this->user->registration($this->request->post);     
                header("Location: ".BASE_PATH. "reg?success=reg");
                exit();
            } catch (UserException $e) {
                $errors = $e->getErrors();
                $msg = $e->getMessage();
            } 
                
        } 

        $this->content = $this->template('view_reg', [
            'errors'  => $errors,
            'post' => $this->request->post,
            'msg' => $msg,
            'login' => $this->login,
            'get' => $this->request->get
        ]);
        $this-> title = 'Регистрация'; 
    }
    
    public function unloginAction ()
    {
        $this->user->unLogin();
        $this->login = 'Гость';
        header("Location: ".BASE_PATH);
        exit();
    }
  
}
