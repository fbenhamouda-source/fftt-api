<?php
// Fichier de routage automatique pour l'API FFTT
header('Content-Type: application/json; charset=utf-8');

// Gestion des erreurs propres
ini_set('display_errors', 0);
error_reporting(0);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

// Récupération des identifiants depuis les variables d'environnement Render
$id = getenv('FFTT_ID') ?: '';
$password = getenv('FFTT_PASSWORD') ?: '';

// Route pour les parties d'un joueur : /api/joueur/{licence}/parties
if (count($segments) >= 4 && $segments[1] === 'joueur' && $segments[3] === 'parties') {
    $licence = $segments[2];
    
    // URL directe vers l'API officielle de la FFTT pour contourner la bibliothèque si besoin
    $url = "https://apiv2.fftt.com/mobile/pxml/xml_partie.php?licence=" . $licence;
    
    // Appel cURL vers la FFTT
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        // Conversion XML en JSON pour que ton application Swift puisse le lire facilement
        $xml = simplexml_load_string($response);
        echo json_encode($xml);
        exit;
    }
}

// Réponse par défaut si la route n'est pas trouvée
http_response_code(404);
echo json_encode(["erreur" => "Route introuvable ou paramètre invalide"]);
