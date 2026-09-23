<?php
session_start();
include('db_connection.php');
include('config.php');
$db_conn = connectToDb();

// Ensure user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? "User";

// --- Live "today's weather" lookup via OpenWeatherMap (current conditions) ---
function fetchTodaysWeather($district) {
    $apiKey = OPENWEATHERMAP_API_KEY;
    if ($apiKey === '' || $apiKey === 'YOUR_OPENWEATHERMAP_API_KEY_HERE') {
        return ['error' => 'Weather API key not set yet. Add your OpenWeatherMap key in admin/config.php.'];
    }

    $url = "https://api.openweathermap.org/data/2.5/weather?" . http_build_query([
        'q'     => $district . ',MW',
        'appid' => $apiKey,
        'units' => 'metric',
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['error' => "Could not reach the weather service ({$curlError}). Check your internet connection."];
    }
    if ($httpCode === 401) {
        return ['error' => "OpenWeatherMap rejected the API key. A brand-new key can take up to an hour to activate - try again shortly."];
    }
    if ($httpCode === 404) {
        return ['error' => "No weather data found for \"{$district}\". Try the nearest major town's name instead."];
    }
    if ($httpCode !== 200) {
        return ['error' => "Could not fetch weather for {$district} (HTTP {$httpCode})."];
    }

    $data = json_decode($response, true);
    $tzOffset = $data['timezone'] ?? 0; // seconds from UTC for this location

    return [
        'district'    => $district,
        'description' => ucfirst($data['weather'][0]['description'] ?? ''),
        'temp'        => $data['main']['temp'] ?? null,
        'feels_like'  => $data['main']['feels_like'] ?? null,
        'humidity'    => $data['main']['humidity'] ?? null,
        'wind'        => $data['wind']['speed'] ?? null,
        'clouds'      => $data['clouds']['all'] ?? null,
        'rain_1h'     => $data['rain']['1h'] ?? null,
        'sunrise'     => isset($data['sys']['sunrise']) ? gmdate('H:i', $data['sys']['sunrise'] + $tzOffset) : null,
        'sunset'      => isset($data['sys']['sunset']) ? gmdate('H:i', $data['sys']['sunset'] + $tzOffset) : null,
    ];
}

// --- Rest-of-today forecast (same free API key, the 5 day / 3 hour forecast endpoint) ---
function fetchTodayForecast($district) {
    $apiKey = OPENWEATHERMAP_API_KEY;
    if ($apiKey === '' || $apiKey === 'YOUR_OPENWEATHERMAP_API_KEY_HERE') {
        return [];
    }

    $url = "https://api.openweathermap.org/data/2.5/forecast?" . http_build_query([
        'q'     => $district . ',MW',
        'appid' => $apiKey,
        'units' => 'metric',
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return [];
    }

    $data = json_decode($response, true);
    $today = date('Y-m-d');
    $slots = [];
    foreach (($data['list'] ?? []) as $entry) {
        if (strpos($entry['dt_txt'], $today) === 0) {
            $slots[] = [
                'time'        => date('H:i', strtotime($entry['dt_txt'])),
                'temp'        => round($entry['main']['temp'] ?? 0),
                'description' => ucfirst($entry['weather'][0]['description'] ?? ''),
                'rain_chance' => isset($entry['pop']) ? round($entry['pop'] * 100) : null,
            ];
        }
    }
    return $slots;
}

// Default the district box to the farmer's own registered district,
// so they see their own weather without typing anything.
$default_district = '';
if ($user_id) {
    $stmt = $db_conn->prepare("SELECT district FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $default_district = $stmt->fetchColumn() ?: '';
}

$district_query = trim($_GET['district'] ?? $default_district);
$today_weather = null;
$today_forecast = [];
if ($district_query !== '') {
    $today_weather = fetchTodaysWeather($district_query);
    if (!isset($today_weather['error'])) {
        $today_forecast = fetchTodayForecast($district_query);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weather Dashboard - Market Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 640px;">
    <p class="mb-2"><a href="select_role.php">&larr; Main menu</a></p>
    <h2 class="text-success mb-1">🌦️ Weather Dashboard</h2>
    <p>Welcome, <strong><?= htmlspecialchars($user_name) ?></strong> &middot; <?= date('l, j F Y') ?></p>

    <div class="card p-3 shadow">
        <h5>Today's Weather</h5>
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="form-label">District</label>
                <input type="text" name="district" class="form-control" placeholder="e.g. Lilongwe"
                       value="<?= htmlspecialchars($district_query) ?>" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success w-100">Check Weather</button>
            </div>
        </form>

        <?php if ($today_weather): ?>
            <?php if (isset($today_weather['error'])): ?>
                <div class="alert alert-warning mt-3 mb-0"><?= htmlspecialchars($today_weather['error']) ?></div>
            <?php else: ?>
                <div class="alert alert-info mt-3 mb-2">
                    <strong><?= htmlspecialchars($today_weather['district']) ?></strong> right now:
                    <?= htmlspecialchars($today_weather['description']) ?>,
                    <?= htmlspecialchars($today_weather['temp']) ?>&deg;C
                    (feels like <?= htmlspecialchars($today_weather['feels_like']) ?>&deg;C)
                    <br>
                    Humidity <?= htmlspecialchars($today_weather['humidity']) ?>% &middot;
                    Wind <?= htmlspecialchars($today_weather['wind']) ?> m/s &middot;
                    Cloud cover <?= htmlspecialchars($today_weather['clouds']) ?>%
                    <?php if ($today_weather['rain_1h']): ?>
                        &middot; Rain (last hr) <?= htmlspecialchars($today_weather['rain_1h']) ?> mm
                    <?php endif; ?>
                    <?php if ($today_weather['sunrise'] && $today_weather['sunset']): ?>
                        <br>Sunrise <?= htmlspecialchars($today_weather['sunrise']) ?> &middot;
                        Sunset <?= htmlspecialchars($today_weather['sunset']) ?>
                    <?php endif; ?>
                </div>

                <?php if (!empty($today_forecast)): ?>
                    <h6 class="mt-3">Rest of today</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr><th>Time</th><th>Temp</th><th>Conditions</th><th>Rain chance</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($today_forecast as $slot): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($slot['time']) ?></td>
                                        <td><?= htmlspecialchars($slot['temp']) ?>&deg;C</td>
                                        <td><?= htmlspecialchars($slot['description']) ?></td>
                                        <td><?= $slot['rain_chance'] !== null ? htmlspecialchars($slot['rain_chance']) . '%' : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-muted mt-3 mb-0">Enter a district above to check today's weather.</p>
        <?php endif; ?>
    </div>

    <p class="text-muted small mt-3">
        Weather is reported for the district's main town, not house-by-house - that's true of any weather service,
        free or paid, since Malawi doesn't have dense enough weather stations for street-level detail. The forecast
        above gives you the timing detail (morning vs. afternoon rain, etc.) instead.
    </p>
</div>

</body>
</html>
