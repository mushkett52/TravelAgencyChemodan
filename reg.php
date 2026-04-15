<?php
session_start();

$host = 'localhost';
$database = 'travel_agency';
$user = 'root';
$password = '';

$link = mysqli_connect($host, $user, $password, $database) or die("Ошибка подключения: " . mysqli_connect_error());

$message = '';
$messageClass = '';

if (isset($_POST['Familia']) && isset($_POST['Name']) && isset($_POST['Login']) && isset($_POST['Password'])) {
    $Familia = mysqli_real_escape_string($link, $_POST['Familia']);
    $Name = mysqli_real_escape_string($link, $_POST['Name']);
    $Patronymic = mysqli_real_escape_string($link, $_POST['Patronymic'] ?? '');
    $telefon = mysqli_real_escape_string($link, $_POST['telefon'] ?? '');
    $Login = mysqli_real_escape_string($link, $_POST['Login']);
    $Password = mysqli_real_escape_string($link, $_POST['Password']);
    
    
    $check_query = "SELECT * FROM Users WHERE Login = '$Login'";
    $check_result = mysqli_query($link, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $message = "Пользователь с таким логином уже существует";
        $messageClass = 'error';
    } else {
        if ($Familia && $Name && $Login && $Password) {
            $query = "INSERT INTO Users (Familia, Name, Patronymic, telefon, Login, Password, id_role) 
                      VALUES ('$Familia', '$Name', '$Patronymic', '$telefon', '$Login', '$Password', 2)";
            $result = mysqli_query($link, $query);
            
            if ($result) {
                $message = "Регистрация успешна! Теперь вы можете войти.";
                $messageClass = 'success';
            } else {
                $message = "Ошибка при регистрации: " . mysqli_error($link);
                $messageClass = 'error';
            }
        } else {
            $message = "Заполните все обязательные поля";
            $messageClass = 'error';
        }
    }
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #7bff94 0%, #34db7d 100%);
            font-family: Arial, sans-serif;
        }
        form {
            border: 1px solid #34db7d;
            padding: 30px;
            border-radius: 10px;
            background-color: #defff1;
            width: 400px;
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
            width: 400px;
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
            width: 100px;
            display: inline-block;
        }
        input[type="text"], input[type="password"], input[type="tel"] {
            padding: 8px;
            border: 1px solid #34db7d;
            border-radius: 5px;
            width: 220px;
        }
        input[type="submit"] {
            background-color: #34db7d;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 20px;
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
    </style>
</head>
<body>
    <?php if ($message): ?>
        <div class="message <?php echo $messageClass; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <h3>Регистрация</h3>
        
        <div style="margin-bottom: 10px;">
            <label for="Familia">Фамилия:*</label>
            <input type="text" name="Familia" id="Familia" required/>
        </div>
        
        <div style="margin-bottom: 10px;">
            <label for="Name">Имя:*</label>
            <input type="text" name="Name" id="Name" required/>
        </div>
        
        <div style="margin-bottom: 10px;">
            <label for="Patronymic">Отчество:</label>
            <input type="text" name="Patronymic" id="Patronymic"/>
        </div>
        
        <div style="margin-bottom: 10px;">
            <label for="telefon">Телефон:</label>
            <input type="tel" name="telefon" id="telefon" placeholder="+7(999)123-45-67"/>
        </div>
        
        <div style="margin-bottom: 10px;">
            <label for="Login">Логин:*</label>
            <input type="text" name="Login" id="Login" required/>
        </div>
        
        <div style="margin-bottom: 10px;">
            <label for="Password">Пароль:*</label>
            <input type="password" name="Password" id="Password" required/>
        </div>
        
        <div style="text-align: center;">
            <input type="submit" value="Зарегистрироваться">
        </div>

        <div style="margin-top: 20px; text-align: center;">
            <p>Уже есть аккаунт? <a href="auth.php">Авторизоваться</a></p>
            <p><a href="index.php">← На главную</a></p>
        </div>
    </form>
</body>
</html>