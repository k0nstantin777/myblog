<?php

/**
 * PostController
 *
 * @author bumer
 */

namespace controller;

use controller\BaseController,
    model\PostModel,
    core\database\DB,
    core\database\DBDriver,
    core\module\Validator,
    core\Request,
    core\helper\ArrayHelper,
    core\ServiceContainer,
    core\exception\ValidatorException,
    core\exception\PostException,
    core\exception\AccessException,
    core\exception\PageNotFoundException;

class PostController extends BaseController
{

    public $mArticles;
   
    public function __construct(Request $request, ServiceContainer $container)
    {
        parent::__construct($request, $container);
        //$this->mArticles = new PostModel(new DBDriver(DB::get()), new Validator());
        $this->mArticles = $this->container->get('model.post');
        $this->menu = $this->template('view_menu', [
            'articles' => $this->mArticles->getAll(),
            'prives' => $this->user_prives,
        ]);
        
        
    }

    /**
     *  главная страница 
     */
    public function indexAction()
    {
        $this->title = 'Мой блог';
        $this->aside = $this->template('view_anons', ['articles' => $this->mArticles->getRandLimit(7)]);
        $this->content = $this->template('view_index', ['login' => $this->login, 'prives' => $this->user_prives]);
    }

    /* страница вывода одной статьи /post */

    public function postAction()
    {
        $id = $this->request->get['id'];
        $article = $this->mArticles->getOne($id);
        if (!$article){
            throw new PageNotFoundException ();
        }     
        $this->content = $this->template('view_post', [
                'article' => $article,
                'back' => ArrayHelper::get($this->request->server, 'HTTP_REFERER', BASE_PATH),
                'prives' => $this->user_prives,
        ]);
        $this->title = $article['title'];
        $this->aside = '';
    }

    /**
     *  страница вывода всех статей /posts 
     */
    public function postsAction()
    {
        /* вывод сообщения после выполнения действия */
        $msg = '';
       
        if ($this->login !== 'Гость') {
            $success = isset($this->request->get['success']) ? $this->request->get['success'] : '';
            $msgs = ['edit' => 'Изменения сохранены', 'add' => 'Статья добавлена', 'delete' => 'Статья удалена'];
            $msg = isset($msgs[$success]) ? $msgs[$success] : '';
        }
        $this->content = $this->template('view_posts', [
            'articles' => $this->mArticles->getAll(),
            'prives' => $this->user_prives,
            'msg' => $msg,
        ]);
        $this->aside = $this->template('view_anons', ['articles' => $this->mArticles->getRandLimit(7)]);
        $this->title = 'Статьи';
    }

    /**
     *  страница добавление статьи /add 
     */
    public function addAction()
    {
        $this->priv_name = 'add_post';
        
        if (!$this->user->can($this->priv_name)) {
            throw new AccessException ();
        }
        $errors = [];
        $msg = '';
        
        //хэш полей формы
        $hash_form = md5('title'.'content'.'submit'); 
        if ($this->request->isPost()) {
            
            try {
                if ($hash_form !== md5(implode(array_keys($this->request->post)))){
                    throw new PostException('Не пытайтесь подделать форму!');
                }
                $name = $this->request->post['title'];
                $text = $this->request->post['content'];
                $this->mArticles->add(['title' => $name, 'text' => $text]);
                header("Location:" . BASE_PATH . "posts?success=add");
                exit();
            } catch (ValidatorException $e) {
                $errors = $e->getErrors();
            } catch (PostException $e) {
                $msg = $e->getMessage();
            } 
        } 
        
        $this->content = $this->template('view_add', [
            'name' => ArrayHelper::get($this->request->post, 'title'),
            'text' => ArrayHelper::get($this->request->post, 'content'),
            'back' => ArrayHelper::get($this->request->server, 'HTTP_REFERER', BASE_PATH),
            'errors' => $errors,
            'msg' => $msg
        ]);

        $this->title = 'Добавить статью';
    }

    /* страница редактрирование статьи /edit/id */
    public function editAction()
    {
        $this->priv_name = 'edit_post';

        if (!$this->user->can($this->priv_name)) {
            throw new AccessException ();
        }
        
        $id = $this->request->get['id'];
               
        $article = $this->mArticles->getOne($id);
        if (!$article){
            throw new PageNotFoundException ();
        } 
        
        $errors = [];
        $msg = '';
          
        // обработка формы методом POST 
        if ($this->request->isPost()) {
            //хэшируем поля формы
            $hash_form = md5('id'.'title'.'content'.'submit'.$id);
            try {    
                if ($hash_form !== md5(implode(array_keys($this->request->post)). $this->request->post['id'] )){
                    throw new PostException('Не пытайтесь подделать форму!');
                }
                $id = $this->request->post['id'];
                $name = $this->request->post['title'];
                $text = $this->request->post['content'];
                $this->mArticles->edit(['id_article' => $id, 'title' => $name, 'text' => $text]);
                header("Location:" . BASE_PATH . "posts?success=edit");
                exit();
            } catch (ValidatorException $e) {
                $errors = $e->getErrors();
            } catch (PostException $e) {
                $msg = $e->getMessage();
            } 
        } 

        
        $this->content = $this->template('view_edit', [
                'name' => ArrayHelper::get($this->request->post, 'title', $article['title']),
                'text' => ArrayHelper::get($this->request->post, 'content', $article['text']),
                'back' => ArrayHelper::get($this->request->server, 'HTTP_REFERER', BASE_PATH),
                'errors'=> $errors,
                'msg' => $msg,
                'prives' => $this->request->session->get('prives'),
                'id' => $id
                
        ]);
        $this->title = 'Изменить статью';
        
    }

    /**
     * удаление статьи /delete/id 
     */

    public function deleteAction()
    {
        $this->priv_name = 'delete_post';
        
        if (!$this->user->can($this->priv_name)) {
            throw new AccessException ();
        }
        
        $id = $this->request->get['id'];
       
        //удалеяем статью
        if (!$this->mArticles->delete($id)) {
            throw new PageNotFoundException ();
        }
        header("Location: " . BASE_PATH . "posts?success=delete");
        exit();
    }

}
