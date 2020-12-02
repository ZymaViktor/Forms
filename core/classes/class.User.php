<?php
require_once(realpath(dirname(__FILE__) . '/../../config.php'));

class User{

    function dashboard() {
        if($this->is_login()) {
            return '<a href="/../../exit.php">Exit</a>';
        } else {
            echo 'Session expired!';
        }
        
    }

    function generateCode($length=6) {
        $chars = "abcd123456789";
        $code = "";
        $clean = strlen($chars) - 1;
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0, $clean)];
        }
        return $code;
    }

    function checkLoginData($email, $pass) {
        $db = new Connect;
        $result = '';
        if(isset($email) && isset($pass)) {
            $email = stripslashes(htmlspecialchars($email));
            $pass = stripslashes(htmlspecialchars(md5(md5(trim($pass)))));
            if(empty($email) or empty($pass)) {
                $result .= "<div clas=\"error\"><p><strong>ERROR:</strong> All fields are required!</p></div>";
            }else{
                $user = $db -> prepare("SELECT * FROM users WHERE email =:email AND password = :pass");
                $user->execute(array(
                    'email' => $email,
                    'pass' => $pass
                ));
                $info = $user->fetch(PDO::FETCH_ASSOC);
                if($user->rowCount() == 0) {
                    $result .= "<div class=\"error\"><p><stong>ERROR:</stong> Login failed!</p></div>";
                }else{
                    $hash = $this->generateCode(10);
                    $upd = $db->prepare("UPDATE users SET session=:hash WHERE id=:ex_user");
                    $upd ->execute(array(
                        'hash' => $hash,
                        'ex_user' => $info['id']
                    ));
                    setcookie('id', $info['id'], time() + 60*60*24*30, "/", null);
                    setcookie('sess', $hash, time() + 60*60*24*30, "/", null);
                    echo ("<script>location.href='?a=dashboard';</script>");
                }
            }
        }

        return $result;

    }


    function LoginForm() {
        return '
            <div class="form_block">
                <div class="title">Login</div>
                <div class="body">' .
                ($_POST ? $this->checkLoginData($_POST['email'], $_POST['pass']) : '')
                    . '<form id="logform" action="?a=login" method="POST">
                        <input type="text" name="email" placeholder="Email">
                        <input type="password" name="pass" placeholder="Password">
                        <input type="submit" value="Enter"><a class="regBtn" href="?a=register">Register</a>
                    </form>
                </div>
            </div>
        ';
    }

    function checkRegisterData($f_name, $l_name, $email, $pass1, $pass2) {
        $db = new Connect;
        $result = '';
        if(isset($f_name) && isset($l_name) && isset($email) && isset($pass1) && isset($pass2)) {
            $email = stripslashes(htmlspecialchars($email));
            $f_name = stripslashes(htmlspecialchars($f_name));
            $l_name = stripslashes(htmlspecialchars($l_name));
            $pass1 = stripslashes(htmlspecialchars(md5(md5(trim($pass1)))));
            $pass2 = stripslashes(htmlspecialchars(md5(md5(trim($pass2)))));
            if(empty($email) or empty($f_name) or empty($l_name) or empty($pass1) or empty($pass2)) {
                $result .= "<div clas=\"error\"><p><strong>ERROR:</strong> All fields are required!</p></div>";
            }elseif($pass1 != $pass2) {
                $result .= "<div class=\"error\"><p><stong>ERROR:</stong> Your passwords do not match</p></div>";
            }else{
                $user = $db -> prepare("SELECT * FROM users WHERE email =:email");
                $user->execute(array(
                    'email' => $email,
                ));
                $info = $user->fetch(PDO::FETCH_ASSOC);
                if($user->rowCount() == 0) {
                    $user = $db->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (:first_name, :last_name, :email, :password)");
                    $user->execute(array(
                        'first_name' => $f_name,
                        'last_name' => $l_name,
                        'email' => $email,
                        'password' => $pass1
                    ));
                    if(!$user) {
                        $result .= "<div class=\"error\"><p><stong>ERROR:</stong> All fields are required!</p></div>";
                    }      
                    else {
                        echo ("<script>location.href='?a=login';</script>");
                    }
                } else {
                    $result .= "<div class=\"error\"><p><stong>ERROR:</stong> This email already exist!</p></div>";
                }
            }
        }

        return $result;

    }

    function RegisterForm() {
        return '
            <div class="form_block">
                <div class="title">Register</div>
                <div class="body">' .
                ($_POST ? $this->CheckRegisterData(
                    $_POST['f_name'],
                    $_POST['l_name'],
                    $_POST['email'],
                    $_POST['pass1'],
                    $_POST['pass2']
                ) : '')
                    . '<form id="logform" action="?a=register" method="POST">
                        <input type="text" name="f_name" placeholder="First Name">
                        <input type="text" name="l_name" placeholder="Last Name">
                        <input type="text" name="email" placeholder="Email">
                        <input type="password" name="pass1" placeholder="Password">
                        <input type="password" name="pass2" placeholder="Repeat Password">
                        <input type="submit" value="Register"><a class="regBtn" href="?=login">Login</a>
                    </form>
                </div>
            </div>
        ';
    }

    function is_login() {
        $db = new Connect;
        if(isset($_COOKIE['id']) && isset($_COOKIE['sess'])) {
            $id = intval($_COOKIE['id']);
            $userdata = $db->prepare("SELECT id, session FROM users WHERE id=:id_user LIMIT 1");
            $userdata->execute(array(
                'id_user' => $id
            ));
            $userdata1 = $userdata->fetch(PDO::FETCH_ASSOC);
            if(($userdata1['session'] != $_COOKIE['session']) or ($userdata1['id'] != intval($_COOKIE['id']))) {
                return false;
            }else {
                return true;
            }
        }else {
            return false;
        }
    } 
}

