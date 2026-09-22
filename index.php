<?php
// Passerelle direct sans identifiants pour l'application SwiftUI
header('Content-Type: application/json; charset=utf-8');

// Récupérer l'URL demandée
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

// Si l'application demande les parties d'un joueur : /api/joueur/{licence}/parties
if (count($segments) >= 4 && $segments[1] === 'joueur' && $segments[3] === 'parties') {
    $licence = $segments[2];
    
    // URL officielle publique de l'API FFTT (Smartping)
    $url = "https://apiv2.fftt.com/mobile/pxml/xml_partie.php?licence=" . $licence;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        // Transformation du XML de la FFTT en JSON pour Xcode
        $xml = @simplexml_load_string($response);
        if ($xml !== false) {
            echo json_encode($xml);
            exit;
        }
    }
}

// Si la route échoue
http_response_code(200);
echo json_encode([["advlic" => "Erreur", "nom" => "Impossible de charger les parties"]]);
