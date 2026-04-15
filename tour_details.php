<?php
session_start();
$host = 'localhost';
$database = "travel_agency";
$user = 'root';
$password = '';

$link = mysqli_connect($host, $user, $password, $database);
if (!$link) {
    die("Ошибка подключения: " . mysqli_connect_error());
}
mysqli_set_charset($link, "utf8");

if (isset($_GET['take_tour']) && isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $tour_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    $check_query = "SELECT * FROM Tours_Users WHERE tour_id = $tour_id AND user_id = $user_id";
    $check_result = mysqli_query($link, $check_query);
    if (mysqli_num_rows($check_result) == 0) {
        $take_query = "INSERT INTO Tours_Users (tour_id, user_id, status) VALUES ($tour_id, $user_id, 'активен')";
        mysqli_query($link, $take_query);
        $message = "Тур успешно добавлен в ваш список!";
    } else {
        $message = "Вы уже взяли этот тур";
    }
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tour_details = null;
$user_has_tour = false;

if ($id > 0) {
    $query_details = "SELECT t.*, h.name as hotel_name
                      FROM tours t
                      LEFT JOIN hotels h ON t.hotel_id = h.id
                      WHERE t.id = $id";
    $result_details = mysqli_query($link, $query_details);
    if ($result_details) {
        $tour_details = mysqli_fetch_assoc($result_details);
        if (isset($_SESSION['user_id']) && $tour_details) {
            $check_user_tour = "SELECT * FROM Tours_Users WHERE tour_id = $id AND user_id = " . $_SESSION['user_id'];
            $check_result = mysqli_query($link, $check_user_tour);
            $user_has_tour = mysqli_num_rows($check_result) > 0;
        }
    }
}

mysqli_close($link);

if (!$tour_details) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tour_details['title']); ?> - туристическое агентство</title>
    <meta name="description" content="Тур <?php echo htmlspecialchars($tour_details['title']); ?> - цена <?php echo number_format($tour_details['price'], 0, '', ' '); ?> ₽. Вылет из <?php echo htmlspecialchars($tour_details['departure_city']); ?>, длительность <?php echo htmlspecialchars($tour_details['duration_days']); ?> ночей.">
    <link rel='stylesheet' href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css">
    <link rel='stylesheet' href="style.css">
</head>
<body>
<div class="container py-4">
    <a href="index.php" class="btn btn-outline-secondary mb-4">← На главную</a>
</div>

<?php if (isset($message)): ?>
<div class="container mt-3">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<div class="container py-3" style="min-height: 400px;">
    <div class="row">
        <div class="col-md-6 mb-4">
            <img src="<?php echo htmlspecialchars(trim($tour_details['image'] ?? '')); ?>" 
                 class="detail-image" 
                 alt="<?php echo htmlspecialchars($tour_details['title']); ?>">
        </div>
        <div class="col-md-6">
            <h1 class="mb-3"><?php echo htmlspecialchars($tour_details['title']); ?></h1>
            <p class="lead"><?php echo htmlspecialchars($tour_details['description']); ?></p>
            <table class="table table-borderless mt-4">
                <tr>
                    <td><strong>👥 Количество человек:</strong></td>
                    <td><?php echo htmlspecialchars($tour_details['people_count']); ?></td>
                </tr>
                <tr>
                    <td><strong>💰 Цена:</strong></td>
                    <td><span class="tour-price"><?php echo number_format($tour_details['price'], 0, '', ' '); ?> ₽</span></td>
                </tr>
                <tr>
                    <td><strong>📍 Город вылета:</strong></td>
                    <td><?php echo htmlspecialchars($tour_details['departure_city']); ?></td>
                </tr>
                <tr>
                    <td><strong>🌙 Длительность:</strong></td>
                    <td><?php echo htmlspecialchars($tour_details['duration_days']); ?> ночей</td>
                </tr>
                <?php if (!empty($tour_details['hotel_name'])): ?>
                <tr>
                    <td><strong>🏨 Отель:</strong></td>
                    <td><?php echo htmlspecialchars($tour_details['hotel_name']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
            <div class="mt-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($user_has_tour): ?>
                        <button class="btn btn-success btn-lg" disabled>Тур уже в вашем списке</button>
                    <?php else: ?>
                        <a href="?take_tour=1&id=<?php echo $tour_details['id']; ?>" class="btn btn-success btn-lg">Взять тур</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="auth.php" class="btn btn-warning btn-lg">Войдите, чтобы взять тур</a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-outline-secondary btn-lg ms-2">На главную</a>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4"><h5>ЧЕМОДАН</h5><p>Ваш надежный партнер в мире путешествий</p></div>
            <div class="col-md-4"><h5>Контакты</h5><p>+7 (999) 123-45-67<br>info@travelagency.ru<br>г. Ярославль</p></div>
            <div class="col-md-4"><h5>Мы в соцсетях</h5><p><a href="#" class="text-white me-2">ВКонтакте</a><a href="#" class="text-white me-2">Telegram</a><a href="#" class="text-white">Одноклассники</a></p></div>
        </div>
        <hr class="bg-white">
        <p class="text-center mb-0">&copy; 2026 ЧЕМОДАН. Все права защищены.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>