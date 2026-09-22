<?php
date_default_timezone_set('Europe/Paris');
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

if (count($segments) >= 4 && $segments[1] === 'joueur' && $segments[3] === 'parties') {
    $licence = $segments[2];
    
    $app_id = "132715";
    $password = "hello";
    $serie = "123456";
    $tm = date('YmdHis');
    $cle = md5($tm . md5($password));
    
    // Utilisation de 'licence' au lieu de 'numlic'
    $url = "https://apiv2.fftt.com/mobile/pxml/xml_partie.php?licence=" . $licence . 
           "&serie=" . $serie . 
           "&id=" . $app_id . 
           "&tm=" . $tm . 
           "&cle=" . $cle;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, "SmartPing iOS");
    $response = curl_exec($ch);
    curl_close($ch);

    header('Content-Type: text/plain; charset=utf-8');
    echo "REPONSE BRUTE DE LA FFTT :\n" . $response;
    exit;
}

echo "Route non trouvée";
