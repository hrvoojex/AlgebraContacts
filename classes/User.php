<?php

class User{

    private $db;
    private $data;
    private $session_name;
    private $cookieName;
    private $cookieDuration;
    private $isLoggedIn = false;

    public function __construct($userId = null){
        $config = Config::get('session');
        $this->session_name = $config['session']['session_name'];
        $this->cookieName = $config['remember']['cookie_name'];
        $this->cookieDuration = $config['remember']['cookie_expiery'];
        $this->db = DB::getInstance();

        if (!$userId) {
            if (Session::exists($this->session_name)) {
                $user = Session::get($this->session_name);
    
                if($this->find($user)){
                    $this->isLoggedIn = true;
                }
            }
        } else {
            $this->find($userId);
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

    public function login($username = null, $password = null, $remember = null){

        if (!$username && !$password && $this->exists()) {
            Session::put($this->session_name, $this->data()->id);
            return true;
        } else {
            $user = $this->find($username);
            if ($user) {
                    //     password iz baze === Heširan password
                if ($this->data()->password === Hash::make($password, $this->data()->salt) && $this->data()->username === $username) {
                    Session::put($this->session_name , $this->data()->id);
                    if ($remember) {
                        $hash = Hash::unique();
                        // provjeravamo da li postoji zapis u bazi u tablici sessions za usera
                        $hashCheck = $this->db->get('hash', 'sessions', ['user_id', '=', $this->data()->id]);
                        // ako ne postoji upisujemo ga u sessions tablicu
                        if (!$hashCheck->getCount()) {
                            $this->db->insert('sessions', [
                                'hash'      => $hash,
                                'user_id'   => $this->data()->id
                            ]);
                        } else {
                            $hash = $hashCheck->getFirst()->hash;
                        }
                        Cookie::put($this->cookieName, $hash, $this->cookieDuration);    
                    }
                    return true;
                }
            }
        }        
        return false;
    }

    public function logout(){
        $this->db->delete('sessions', ['user_id', '=', $this->data()->id]);
        Session::delete($this->session_name);
        Cookie::delete($this->cookieName);
        session_destroy();
    }

    public function exists(){
        return (!empty($this->data)) ? true : false;
    }

    public function data(){
        return $this->data;
    }

    public function check(){
        return $this->isLoggedIn;
    }
}



?>