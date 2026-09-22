<?php
date_default_timezone_set('Europe/Paris');
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

if (count($segments) >= 4 && $segments[1] === 'joueur' && $segments[3] === 'parties') {
    $licence = $segments[2];
    
    $app_id = "132715";
    $password = "hello";
    $tm = date('YmdHis');
    $cle = md5($tm . md5($password));
    $passe_md5 = md5($password);
    
    // On combine TOUTES les variantes de paramètres acceptées par les différents scripts de la FFTT
    $url = "https://apiv2.fftt.com/mobile/pxml/xml_partie.php?numlic=" . $licence . 
           "&serie=000000&id=" . $app_id . 
           "&tm=" . $tm . "&cle=" . $cle . 
           "&passe=" . $passe_md5;
    
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
