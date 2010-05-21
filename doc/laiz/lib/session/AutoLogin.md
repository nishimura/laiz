laiz.lib.session.AutoLogin
==========================

This class is auto login session utility.

Action
------

    <?php
    use laiz\lib\session\AutoLogin;
    class Base_Action_Login
    {
        public $id;
        public $password;
        public $autologinCheckbox;
        public function act(AutoLogin $auto)
        {
            if (!MyAuth::login($this->id, $this->password))
                return 'error';
            $auto->login($this->id, $this->autologinCheckbox);
        }
    }

    <?php
    use laiz\lib\session\AutoLogin;
    class Base_Action_Logout
    {
        public function act(AutoLogin $auto)
        {
            $auto->logout();
        }
    }

Filter
------

    <?php
    use laiz\lib\session\AutoLogin;
    class Filter_Autologin
    {
        public function filter(AutoLogin $auto)
        {
            $startNow = $auto->autoLoginStart();
            if ($startNow){
                $userId = $auto->getUserId();
                // initialization
            }
        }
    }

