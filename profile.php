<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}
$host = 'localhost';
$database = 'travel_agency';
$user = 'root';
$password = '';
$link = mysqli_connect($host, $user, $password, $database) or die("Ошибка подключения: " . mysqli_connect_error());
mysqli_set_charset($link, "utf8");

$user_id = $_SESSION['user_id'];
$message = '';
$messageClass = '';


if (isset($_POST['update_profile'])) {
    $Familia = mysqli_real_escape_string($link, $_POST['Familia']);
    $Name = mysqli_real_escape_string($link, $_POST['Name']);
    $Patronymic = mysqli_real_escape_string($link, $_POST['Patronymic']);
    $telefon = mysqli_real_escape_string($link, $_POST['telefon']);
    $update_query = "UPDATE Users SET Familia = '$Familia', Name = '$Name', Patronymic = '$Patronymic', telefon = '$telefon' WHERE id_user = $user_id";
    if (mysqli_query($link, $update_query)) {
        $_SESSION['user_name'] = $Name;
        $_SESSION['user_familia'] = $Familia;
        $message = "Данные успешно обновлены";
        $messageClass = 'success';
    }
}

if (isset($_POST['update_login'])) {
    $new_login = mysqli_real_escape_string($link, $_POST['new_login']);
    $check_query = "SELECT * FROM Users WHERE Login = '$new_login' AND id_user != $user_id";
    $check_result = mysqli_query($link, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $message = "Этот логин уже занят";
        $messageClass = 'error';
    } else {
        $update_query = "UPDATE Users SET Login = '$new_login' WHERE id_user = $user_id";
        if (mysqli_query($link, $update_query)) {
            $_SESSION['user_login'] = $new_login;
            $message = "Логин успешно изменен";
            $messageClass = 'success';
        }
    }
}

if (isset($_POST['update_password'])) {
    $current_password = mysqli_real_escape_string($link, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($link, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($link, $_POST['confirm_password']);
    $check_query = "SELECT * FROM Users WHERE id_user = $user_id AND Password = '$current_password'";
    $check_result = mysqli_query($link, $check_query);
    if (mysqli_num_rows($check_result) == 0) {
        $message = "Текущий пароль неверен";
        $messageClass = 'error';
    } elseif ($new_password != $confirm_password) {
        $message = "Новый пароль и подтверждение не совпадают";
        $messageClass = 'error';
    } elseif (strlen($new_password) < 3) {
        $message = "Пароль должен содержать минимум 3 символа";
        $messageClass = 'error';
    } else {
        $update_query = "UPDATE Users SET Password = '$new_password' WHERE id_user = $user_id";
        if (mysqli_query($link, $update_query)) {
            $message = "Пароль успешно изменен";
            $messageClass = 'success';
        }
    }
}

$user_query = "SELECT * FROM Users WHERE id_user = $user_id";
$user_result = mysqli_query($link, $user_query);
$user_data = mysqli_fetch_assoc($user_result);

$tours_query = "SELECT t.*, tu.booking_date 
                FROM Tours t
                JOIN Tours_Users tu ON t.id = tu.tour_id
                WHERE tu.user_id = $user_id
                ORDER BY tu.booking_date DESC";
$tours_result = mysqli_query($link, $tours_query);
$user_tours = [];
while ($row = mysqli_fetch_assoc($tours_result)) {
    $user_tours[] = $row;
}

if (isset($_GET['cancel_tour'])) {
    $tour_id = intval($_GET['cancel_tour']);
    $delete_query = "DELETE FROM Tours_Users WHERE user_id = $user_id AND tour_id = $tour_id";
    mysqli_query($link, $delete_query);
    header("Location: profile.php");
    exit();
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link rel='stylesheet' href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css">
    <link rel='stylesheet' href="style.css">
    <style>
        .profile-container { max-width: 1200px; margin: 0 auto; padding: 20px; min-height: calc(100vh - 200px); }
        .profile-header { background: linear-gradient(135deg, #7bff94 0%, #34db7d 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; }
        .profile-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); margin-bottom: 30px; }
        .profile-card h3 { color: #34db7d; margin-bottom: 20px; border-bottom: 2px solid #34db7d; padding-bottom: 10px; }
        .info-row { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .info-label { font-weight: bold; color: #666; width: 150px; display: inline-block; }
        .info-value { color: #333; }
        .tour-history-card { border: 1px solid #eee; border-radius: 10px; padding: 15px; margin-bottom: 15px; transition: all 0.3s; }
        .tour-history-card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.1); transform: translateY(-2px); }
        .btn-cancel { background-color: #dc3545; color: white; border: none; padding: 5px 15px; border-radius: 5px; }
        .btn-cancel:hover { background-color: #c82333; }
        .footer { background: linear-gradient(135deg, #7bff94 0%, #34db7d 100%); color: white; padding: 30px 0; margin-top: 50px; }
        .edit-section { margin-top: 20px; padding-top: 20px; border-top: 1px dashed #34db7d; }
        .btn-edit { background-color: #34db7d; color: white; border: none; padding: 5px 15px; border-radius: 5px; font-size: 0.9rem; }
        .btn-edit:hover { background-color: #2bbd6a; }
        .home-btn { background-color: white; color: #34db7d; border: 2px solid white; padding: 8px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; }
        .home-btn:hover { background-color: rgba(255,255,255,0.2); color: white; }
    </style>
</head>
<body>
<div class="profile-container">
    <div class="profile-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Личный кабинет</h1>
            <div class="d-flex gap-3">
                <a href="index.php" class="home-btn">На главную</a>
                <a href="logout.php" class="btn btn-light">Выйти</a>
            </div>
        </div>
        <p class="mt-3">Добро пожаловать, <?php echo htmlspecialchars($user_data['Familia'] . ' ' . $user_data['Name']); ?>!</p>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageClass == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="profile-card" id="personal">
                <h3>Личные данные</h3>
                <form method="POST">
                    <div class="info-row">
                        <span class="info-label">Фамилия:</span>
                        <input type="text" name="Familia" class="form-control" value="<?php echo htmlspecialchars($user_data['Familia']); ?>" required>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Имя:</span>
                        <input type="text" name="Name" class="form-control" value="<?php echo htmlspecialchars($user_data['Name']); ?>" required>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Отчество:</span>
                        <input type="text" name="Patronymic" class="form-control" value="<?php echo htmlspecialchars($user_data['Patronymic'] ?? ''); ?>">
                    </div>
                    <div class="info-row">
                        <span class="info-label">Телефон:</span>
                        <input type="text" name="telefon" class="form-control" value="<?php echo htmlspecialchars($user_data['telefon'] ?? ''); ?>">
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-success">Сохранить изменения</button>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="profile-card" id="security">
                <h3>Безопасность</h3>
                <form method="POST" class="mb-4">
                    <h5>Изменение логина</h5>
                    <div class="info-row">
                        <span class="info-label">Текущий логин:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user_data['Login']); ?></span>
                    </div>
                    <div class="mb-3">
                        <label for="new_login" class="form-label">Новый логин</label>
                        <input type="text" name="new_login" id="new_login" class="form-control" required>
                    </div>
                    <button type="submit" name="update_login" class="btn btn-edit">Изменить логин</button>
                </form>
                <form method="POST" class="edit-section">
                    <h5>Изменение пароля</h5>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Текущий пароль</label>
                        <input type="password" name="current_password" id="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Новый пароль</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Подтвердите пароль</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-edit">Изменить пароль</button>
                </form>
            </div>
        </div>
    </div>

    <div class="profile-card" id="tours">
        <h3>Мои туры</h3>
        <?php if (empty($user_tours)): ?>
            <p class="text-muted">У вас пока нет забронированных туров.</p>
            <a href="index.php" class="btn btn-success">Посмотреть туры</a>
        <?php else: ?>
            <?php foreach ($user_tours as $tour): ?>
            <div class="tour-history-card">
                <div class="row">
                    <div class="col-md-2">
                        <img src="<?php echo htmlspecialchars(trim($tour['image'] ?? 'https://via.placeholder.com/150x100?text=Тур')); ?>" 
                             class="img-fluid rounded" 
                             alt="<?php echo htmlspecialchars($tour['title']); ?>">
                    </div>
                    <div class="col-md-7">
                        <h5><?php echo htmlspecialchars($tour['title']); ?></h5>
                        <p class="mb-1">📍 <?php echo htmlspecialchars($tour['destination']); ?></p>
                        <p class="mb-1">💰 <?php echo number_format($tour['price'], 0, '', ' '); ?> ₽</p>
                        <p class="mb-1">📅 Дата бронирования: <?php echo date('d.m.Y', strtotime($tour['booking_date'])); ?></p>
                    </div>
                    <div class="col-md-3 text-end">
                        <a href="?page=tour_details&id=<?php echo $tour['id']; ?>" class="btn btn-sm btn-outline-success mb-2 w-100">Подробнее</a>
                        <a href="?cancel_tour=<?php echo $tour['id']; ?>" 
                           class="btn btn-sm btn-cancel w-100" 
                           onclick="return confirm('Вы уверены, что хотите отменить тур?')">Отменить</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4"><h5>ТУРАГЕНСТВО</h5><p>Ваш надежный партнер в мире путешествий</p></div>
            <div class="col-md-4"><h5>Контакты</h5><p>+7 (999) 123-45-67<br>info@travelagency.ru<br>г. Ярославль</p></div>
            <div class="col-md-4"><h5>Мы в соцсетях</h5><p><a href="#" class="text-white me-2">ВКонтакте</a><a href="#" class="text-white me-2">Telegram</a><a href="#" class="text-white">Одноклассники</a></p></div>
        </div>
        <hr class="bg-white">
        <p class="text-center mb-0">&copy; 2026 Турагентство. Все права защищены.</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>