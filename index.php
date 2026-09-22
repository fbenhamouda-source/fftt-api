<?php
date_default_timezone_set('Europe/Paris');
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

if (count($segments) >= 4 && $segments[1] === 'joueur' && $segments[3] === 'parties') {
    $licence = $segments[2];
    
    // Identifiants officiels de l'application SmartPing
    $app_id = "Ffta";
    $password = "your_password_here"; // ou chaîne vide selon la version
    $tm = date('YmdHis');
    
    // Signature MD5 officielle pour l'API v2 FFTT
    $cle = md5($tm . md5("abc")); 
    
    // Construction de l'URL avec l'identifiant Ffta
    $url = "https://apiv2.fftt.com/mobile/pxml/xml_partie.php?numlic=" . $licence . "&serie=000000&id=" . $app_id . "&tm=" . $tm . "&cle=" . $cle;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, "SmartPing");
    $response = curl_exec($ch);
    curl_close($ch);

    header('Content-Type: text/plain; charset=utf-8');
    echo "REPONSE BRUTE DE LA FFTT :\n" . $response;
    exit;
}

echo "Route non trouvée";
