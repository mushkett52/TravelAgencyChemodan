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

$search_results = [];
$search_destination = isset($_GET['destination']) ? mysqli_real_escape_string($link, $_GET['destination']) : '';
$search_people = isset($_GET['people']) ? intval($_GET['people']) : 0;
$search_city = isset($_GET['city']) ? mysqli_real_escape_string($link, $_GET['city']) : '';

$query_search = "SELECT * FROM tours WHERE 1=1";

if (!empty($search_destination)) {
    $query_search .= " AND (destination LIKE '%$search_destination%' OR title LIKE '%$search_destination%' OR description LIKE '%$search_destination%')";
}

if ($search_people > 0) {
    $query_search .= " AND people_count >= $search_people";
}

if (!empty($search_city)) {
    $query_search .= " AND departure_city LIKE '%$search_city%'";
}

$query_search .= " ORDER BY price ASC";

$result_search = mysqli_query($link, $query_search);
if ($result_search) {
    while($row = mysqli_fetch_assoc($result_search)) {
        $search_results[] = $row;
    }
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поиск туров - Туристическое агентство</title>
    <link rel='stylesheet' href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css">
    <link rel='stylesheet' href="style.css">
</head>
<body>
<div class="navbar-container" style="height: auto; min-height: 150px; padding: 10px 0;">
    <nav class="navbar navbar-expand-lg navbar-light navbar-main">
        <div class="container">
            <a href="index.php" class="navbar-brand fw-bold fs-3">ЧЕМОДАН</a>
            <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a href="index.php" class="nav-link">Главная</a></li>
                    <li class="nav-item"><a href="info.html" class="nav-link">О нас</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a href="profile.php" class="nav-link">Личный кабинет</a></li>
                        <li class="nav-item"><a href="logout.php" class="nav-link">Выйти (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a href="auth.php" class="nav-link">Войти</a></li>
                        <li class="nav-item"><a href="reg.php" class="nav-link">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</div>

<div class="container py-5" style="min-height: 400px;">
    <h1 class="mb-4">Поиск туров</h1>
    <?php if(!empty($search_destination) || $search_people > 0 || !empty($search_city)): ?>
        <p class="lead mb-4">
            По запросу "<?php echo htmlspecialchars($search_destination); ?>"
            <?php if ($search_people > 0): ?>, <?php echo $search_people; ?> чел.<?php endif; ?>
            <?php if (!empty($search_city)): ?>, вылет из <?php echo htmlspecialchars($search_city); ?><?php endif; ?>
        </p>
    <?php endif; ?>
    <div class="row">
        <?php if(empty($search_results)): ?>
        <div class="col-12"><div class="alert alert-info text-center">По вашему запросу ничего не найдено</div></div>
        <?php else: ?>
            <?php foreach($search_results as $tour): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card tour-card">
                    <img src="<?php echo htmlspecialchars(trim($tour['image'] ?? 'https://via.placeholder.com/400x200?text=Тур')); ?>" 
                         class="card-img-top tour-img" 
                         alt="<?php echo htmlspecialchars($tour['title']); ?>">
                    <div class="card-body">
                        <h3 class="tour-title"><?php echo htmlspecialchars($tour['title']); ?></h3>
                        <p class="tour-people">👥 <?php echo htmlspecialchars($tour['people_count']); ?> человек(а)</p>
                        <p class="tour-description"><?php echo htmlspecialchars(mb_substr($tour['description'], 0, 80)); ?>...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="tour-price"><?php echo number_format($tour['price'], 0, '', ' '); ?> ₽</span>
                            <a href="tour_details.php?id=<?php echo $tour['id']; ?>" class="btn btn-success btn-details">Подробнее</a>
                        </div>
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