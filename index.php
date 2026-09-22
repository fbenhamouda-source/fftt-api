<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

if (count($segments) >= 4 && $segments[1] === 'joueur' && $segments[3] === 'parties') {
    $licence = $segments[2];
    $url = "https://apiv2.fftt.com/mobile/pxml/xml_partie.php?numlic=" . $licence;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    // On affiche directement la réponse brute reçue de la FFTT en texte
    header('Content-Type: text/plain; charset=utf-8');
    echo "REPONSE BRUTE FFTT :\n" . $response;
    exit;
}

echo "Route non trouvée";
