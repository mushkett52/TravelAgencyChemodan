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

$tours = [];
$query = "SELECT * FROM tours ORDER BY price ASC LIMIT 4";
$result = mysqli_query($link, $query);
if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $tours[] = $row;
    }
}

$count_query = "SELECT COUNT(*) as total FROM tours";
$count_result = mysqli_query($link, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$tours_count = $count_row['total'];

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Туристическое агентство - туры, горящие путевки</title>
    <meta name="description" content="Туристическое агентство - поиск и бронирование туров. Горящие путевки в Турцию, Египет, Таиланд, ОАЭ. Вылет из Москвы, Санкт-Петербурга.">
    <link rel='stylesheet' href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css">
    <link rel='stylesheet' href="style.css">
</head>
<body>
<div class="navbar-container">
    <nav class="navbar navbar-expand-lg navbar-light navbar-main">
        <div class="container">
            <a href="index.php" class="navbar-brand fw-bold fs-3">ЧЕМОДАН</a>
            <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a href="index.php" class="nav-link active">Главная</a></li>
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
    
    <div class="container text-center mb-4">
        <h1 class="main-title">Ищите легко, Бронируйте выгодно!</h1>
    </div>
    <div class="container">
        <div class="search-container">
            <ul class="nav nav-tabs search-tabs">
                <li class="nav-item"><a class="nav-link active" href="#">Поиск туров</a></li>
            </ul>
            <form class="search-form" action="search.php" method="GET" id="searchForm">
                <input type="hidden" name="people" id="hidden_people" value="2">
                <input type="hidden" name="city" id="hidden_city" value="Москва">
                <div class="search-input">
                    <input type="text" name="destination" class="form-control form-control-lg" placeholder="Куда хотите поехать?">
                </div>
                <div class="d-flex gap-3">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" id="peopleButton">2 взрослых</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="setPeople(1); return false;">1 взрослый</a></li>
                            <li><a class="dropdown-item" href="#" onclick="setPeople(2); return false;">2 взрослых</a></li>
                            <li><a class="dropdown-item" href="#" onclick="setPeople(3); return false;">Семейный (2+1)</a></li>
                            <li><a class="dropdown-item" href="#" onclick="setPeople(4); return false;">Семейный (2+2)</a></li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" id="cityButton">из Москвы</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="setCity('Москва'); return false;">из Москвы</a></li>
                            <li><a class="dropdown-item" href="#" onclick="setCity('Санкт-Петербург'); return false;">из Санкт-Петербурга</a></li>
                            <li><a class="dropdown-item" href="#" onclick="setCity('Екатеринбург'); return false;">из Екатеринбурга</a></li>
                            <li><a class="dropdown-item" href="#" onclick="setCity('Казань'); return false;">из Казани</a></li>
                        </ul>
                    </div>
                </div>
                <button type="submit" class="btn btn-warning search-btn">Найти</button>
            </form>
            <div class="stats-text"><br>Найдено туров за сутки <?php echo number_format($tours_count, 0, '', ' '); ?></div>
        </div>
    </div>
</div>

<?php if (isset($message)): ?>
<div class="container mt-3">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<div class="container py-5" style="min-height: 400px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title">Лучшие цены</h2>
        <a href="all_tours.php" class="btn btn-outline-success">Смотреть все туры →</a>
    </div>
    <div class="row">
        <?php if(empty($tours)): ?>
        <div class="col-12"><div class="alert alert-warning text-center">Нет данных о турах в базе данных</div></div>
        <?php else: ?>
            <?php foreach($tours as $tour): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card tour-card">
                    <img src="<?php echo htmlspecialchars(trim($tour['image'] ?? 'images/placeholder.jpg')); ?>" 
                    class="card-img-top tour-img" alt="<?php echo htmlspecialchars($tour['title'] .
                     ' - тур в ' . $tour['destination'] . ' от ' . number_format($tour['price'], 0, '', ' ') . ' рублей'); ?>">
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
            <div class="col-md-4"><h5>ТУРАГЕНСТВО</h5><p>Ваш надежный партнер в мире путешествий</p></div>
            <div class="col-md-4"><h5>Контакты</h5><p>+7 (999) 123-45-67<br>info@travelagency.ru<br>г. Ярославль</p></div>
            <div class="col-md-4"><h5>Мы в соцсетях</h5><p><a href="#" class="text-white me-2">ВКонтакте</a><a href="#" class="text-white me-2">Telegram</a><a href="#" class="text-white">Одноклассники</a></p></div>
        </div>
        <hr class="bg-white">
        <p class="text-center mb-0">&copy; 2026 Турагентство. Все права защищены.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
<script>
function setPeople(count) {
    const texts = {1:'1 взрослый', 2:'2 взрослых', 3:'Семейный (2+1)', 4:'Семейный (2+2)'};
    document.getElementById('peopleButton').innerHTML = texts[count] || count + ' взрослых';
    document.getElementById('hidden_people').value = count;
}
function setCity(city) {
    document.getElementById('cityButton').innerHTML = 'из ' + city;
    document.getElementById('hidden_city').value = city;
}
</script>
</body>
</html>