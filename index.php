<?php
date_default_timezone_set('Europe/Paris');
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

if (count($segments) >= 4 && $segments[1] === 'joueur' && $segments[3] === 'parties') {
    $licence = $segments[2];
    
    $app_id = "132715";
    $password = "hello";
    $serie = "123456789012345";
    $mac = "00:11:22:33:44:55";
    $tm = date('YmdHis');
    
    // Génération de la clé de hachage officielle
    $cle = md5($tm . md5($password));
    
    // URL avec l'ensemble des paramètres requis pour valider la vérification (verification > 0)
    $url = "https://apiv2.fftt.com/mobile/pxml/xml_partie.php?numlic=" . $licence . 
           "&id=" . $app_id . 
           "&serie=" . $serie . 
           "&mac=" . $mac . 
           "&tm=" . $tm . 
           "&cle=" . $cle;
    
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
