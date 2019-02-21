<?php

class User{

    private $db;
    private $config;
    private $data;
    private $session_name;
    private $isLoggedIn = false;

    public function __construct(){
        $this->config = Config::get('session');
        $this->session_name = $this->config['session']['session_name'];
        $this->db = DB::getInstance();

        if (Session::exists($this->session_name)) {
            $user = Session::get($this->session_name);

            if($this->find($user)){
                $this->isLoggedIn = true;
            }
        } 
    }

    public function create($fields = array()){
        if(!$this->db->insert('users', $fields)){
            throw new Exception("There was a problem creating an account");
        }
    }

    public function find($userIdentification){
        if ($userIdentification) {
            $field = (is_numeric($userIdentification)) ? 'id' : 'username';
            $userData = $this->db->get('*', 'users', [$field, '=', $userIdentification]);

            if ($userData->getCount()) {
                $this->data = $userData->getFirst();
                return true;
            }
        }
        return false;
    }

    public function login($username, $password){

        $user = $this->find($username);

        if ($user) {
                //     password iz baze === Heširan password
            if ($this->data()->password === Hash::make($password, $this->data()->salt)) {
                Session::put($this->session_name , $this->data()->id);
                return true;
            }
        }
        return false;
    }

    public function logout(){
        Session::delete($this->session_name);
        session_destroy();
    }

    public function data(){
        return $this->data;
    }

    public function check(){
        return $this->isLoggedIn;
    }
}



?>