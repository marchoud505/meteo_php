<?php
// Autoriser un délai plus long pour les appels API (max 5 minutes)
set_time_limit(300);

// Clé API OpenWeatherMap
$apiKey = '5e611d4c7e6779351247eed784e77ac4'; // Remplacez par votre clé

// Liste des villes
$villes = [ 'Abidjan','Accra','Ahmedabad','Alger','Amsterdam','Ankara','Athènes','Atlanta','Auckland','Baghdad',
'Bangalore','Bangkok','Barcelona','Beijing','Beirut','Belgrade','Berlin','Bogotá','Boston','Brasília',
'Bratislava','Brisbane','Bruxelles','Bucarest','Budapest','Buenos Aires','Cairo','Calgary','Cape Town',
'Cardiff','Caracas','Chennai','Chicago','Cologne','Colombo','Copenhague','Dallas','Damascus','Dakar',
'Dar es Salaam','Delhi','Denver','Dhaka','Doha','Dortmund','Dubai','Dublin','Durban','Edinburgh',
'Frankfurt','Fukuoka','Geneva','Glasgow','Guangzhou','Hambourg','Hanoï','Harare','Helsinki','Ho Chi Minh-Ville',
'Hong Kong','Honolulu','Houston','Hyderabad','Istanbul','Jacksonville','Jakarta','Johannesburg','Karachi','Kigali',
'Kinshasa','Kuala Lumpur','Kuwait City','Kyoto','Lagos','Lahore','Lima','Lisbonne','Liverpool','Londres',
'Los Angeles','Lyon','Madrid','Manille','Marseille','Melbourne','Mexico','Miami','Milan','Montréal',
'Moscou','Mumbai','Munich','Nairobi','Nagoya','Naples','New Delhi','New York','Nice','Osaka',
'Oslo','Ottawa','Panama','Paris','Perth','Philadelphie','Phoenix','Porto','Port-au-Prince','Porto Alegre',
'Prague','Quito','Reykjavik','Riyadh','Rome','San Diego','San Francisco','San José','San Juan','São Paulo',
'Santiago','Santo Domingo','Seattle','Seoul','Shanghai','Shenzhen','Singapore','Stockholm','Stuttgart','Sydney',
'Taipei','Tallinn','Tanger','Téhéran','Tel Aviv','Tianjin','Tokyo','Toronto','Valence','Valparaíso',
'Vancouver','Venise','Vienne','Vilnius','Warsaw','Washington D.C.','Wellington','Zagreb','Addis Abeba','Almaty',
'Antalya','Antananarivo','Austin','Baku','Baltimore','Birmingham','Bordeaux','Busan','Casablanca','Córdoba',
'Curitiba','Detroit','Dijon','Donetsk','Douala','Edmonton','Fortaleza','Gdańsk','Guadalajara','Guayaquil',
'Hangzhou','Hiroshima','Innsbruck','Izmir','Jaipur','Jacksonville','Kharkiv','Kiev','Kobe','Kolkata',
'Kraków','Kunming','Leeds','Leipzig','León','Lille','Linz','Lodz','Madras','Manchester',
'Medellín','Minsk','Montpellier','Multan','Münster','Nashville','Nîmes','Novosibirsk','Odessa','Oklahoma City'
];
sort($villes);

// Filtrage avec la recherche
$search = $_GET['search'] ?? '';
$displayVilles = array_filter($villes, fn($v) => stripos($v, $search) !== false);

// Limite à 20 villes si aucune recherche
if (!$search) {
    $displayVilles = array_slice($displayVilles, 0, 20);
}

// Fonction API
function fetchWeather($city, $key) {
    $url = "https://api.openweathermap.org/data/2.5/weather?q="
         . urlencode($city) . "&units=metric&lang=fr&appid={$key}";
    $json = @file_get_contents($url);
    if (!$json) return null;
    $data = json_decode($json, true);
    if (!isset($data['main'])) return null;
    return [
        'temp' => round($data['main']['temp']),
        'icon' => $data['weather'][0]['icon'],
        'desc' => ucfirst($data['weather'][0]['description'])
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Météo – 200 Villes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bulma + FontAwesome -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
  <style>
    body {
      background: #121212; color: #eee;
      display: flex; flex-direction: column; min-height: 100vh;
    }
    .hero.is-dark { background: #1f1f1f; }
    .box { background: #1e1e1e; border: none; }
    .weather-icon { animation: float 3s ease-in-out infinite; }
    @keyframes float {
      0%,100% { transform: translateY(0); }
      50%     { transform: translateY(-10px); }
    }
    .field.has-addons .control:first-child input {
      border-top-right-radius: 0; border-bottom-right-radius: 0;
    }
    .field.has-addons .control:last-child .button {
      border-top-left-radius: 0; border-bottom-left-radius: 0;
    }
  </style>
</head>
<body>

  <!-- En-tête -->
  <section class="hero is-dark">
    <div class="hero-body">
      <div class="container">
        <h1 class="title has-text-white">🌐 Dashboard Météo</h1>
        <p class="subtitle has-text-grey-light">200 villes à portée de main</p>
      </div>
    </div>
  </section>

  <!-- Recherche -->
  <section class="section">
    <div class="container">
      <form method="get">
        <div class="field has-addons is-centered">
          <div class="control">
            <input class="input" type="text" name="search" placeholder="Filtrer par nom de ville…"
                   value="<?= htmlspecialchars($search) ?>">
          </div>
          <div class="control">
            <button class="button is-info">
              <span class="icon"><i class="fas fa-search"></i></span>
              <span>Rechercher</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </section>

  <!-- Cartes météo -->
  <section class="section is-flex-grow-1">
    <div class="container">
      <div class="columns is-multiline">
        <?php foreach ($displayVilles as $ville):
          $w = fetchWeather($ville, $apiKey);
        ?>
          <div class="column is-3">
            <div class="box has-text-centered">
              <h2 class="title is-5"><?= htmlspecialchars($ville) ?></h2>
              <?php if ($w): ?>
                <img src="https://openweathermap.org/img/wn/<?= $w['icon'] ?>@2x.png"
                     alt="<?= htmlspecialchars($w['desc']) ?>" class="weather-icon">
                <p class="is-size-3 has-text-info"><strong><?= $w['temp'] ?>°C</strong></p>
                <p class="has-text-grey-light"><?= htmlspecialchars($w['desc']) ?></p>
              <?php else: ?>
                <p class="has-text-danger">Indisponible</p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if (empty($displayVilles)): ?>
          <div class="column">
            <p class="has-text-centered has-text-grey-light">Aucune ville trouvée.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer has-background-dark has-text-grey-light">
    <div class="content has-text-centered">
      &copy; <?= date('Y') ?> Mon Dashboard Météo
    </div>
  </footer>

  <script defer src="https://use.fontawesome.com/releases/v6.4.0/js/all.js"></script>
</body>
</html>
