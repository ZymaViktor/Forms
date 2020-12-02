<?php
// import user object
require_once('core/classes/class.User.php');
$user = new User;

// getting the action
$a = isset($_GET['a']) ? $_GET['a'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php 
    switch($a) {
        case 'login': {
            echo $user->LoginForm();
            break;    
        }
        case 'register': {
            echo $user->RegisterForm();
            break;
        }
        case 'dashboard': {
            echo $user->dashboard();
            break;
        }
        
        default: {
            echo $user->LoginForm();
            break;
        }
    }
    
    ?>

     
</body>
</html>