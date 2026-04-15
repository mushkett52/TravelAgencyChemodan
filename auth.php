<?php
session_start();

$host = 'localhost';
$database = 'travel_agency';
$user = 'root';
$password = '';

$link = mysqli_connect($host, $user, $password, $database) or die("Ошибка подключения: " . mysqli_connect_error());

$message = '';
$messageClass = '';

if (isset($_POST['login']) && isset($_POST['password'])) {
    $login = mysqli_real_escape_string($link, $_POST['login']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    
    if (!empty($login) && !empty($password)) {
        $query = "SELECT * FROM Users WHERE Login = '$login' AND Password = '$password'";
        $result = mysqli_query($link, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);
            
            $_SESSION['user_id'] = $user_data['id_user'];
            $_SESSION['user_login'] = $user_data['Login'];
            $_SESSION['user_name'] = htmlspecialchars($user_data['Name']);
            $_SESSION['user_familia'] = htmlspecialchars($user_data['Familia']);
            $_SESSION['user_role'] = $user_data['id_role'];
            
            mysqli_close($link);
            header("Location: index.php");
            exit();
        } else {
            $message = "Неверный логин или пароль";
            $messageClass = 'error';
        }
        
        if ($result) {
            mysqli_free_result($result);
        }
    } else {
        $message = "Заполните все поля";
        $messageClass = 'error';
    }
}

if (isset($link)) {
    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #7bff94 0%, #34db7d 100%);
            font-family: Arial, sans-serif;
        }
        .auth-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        form {
            border: 1px solid #34db7d;
            padding: 30px;
            border-radius: 10px;
            background-color: #defff1;
            width: 350px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #2bbd6a;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
            width: 350px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        label {
            width: 80px;
            display: inline-block;
        }
        input[type="text"], input[type="password"] {
            padding: 8px;
            border: 1px solid #34db7d;
            border-radius: 5px;
            width: 200px;
        }
        input[type="submit"] {
            background-color: #34db7d;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        input[type="submit"]:hover {
            background-color: #2bbd6a;
        }
        p {
            text-align: center;
        }
        a {
            color: #34db7d;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            color: #2bbd6a;
        }
        .footer {
            background: linear-gradient(135deg, #7bff94 0%, #34db7d 100%);
            color: white;
            padding: 40px 0 20px;
            margin-top: 50px;
            width: 100%;
        }
        .footer a {
            color: white;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <?php if ($message): ?>
            <div class="message <?php echo $messageClass; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <h3>Авторизация</h3>
            
            <div style="margin-bottom: 15px;">
                <label for="login">Логин:</label>
                <input type="text" name="login" id="login" required/>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="password">Пароль:</label>
                <input type="password" name="password" id="password" required/>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <input type="submit" value="Войти">
            </div>

            <div style="margin-top: 20px; text-align: center;">
                <p>Еще нет аккаунта? <a href="reg.php">Зарегистрироваться</a></p>
                <p><a href="index.php">← На главную</a></p>
            </div>
        </form>
    </div>

 
</body>
</html>