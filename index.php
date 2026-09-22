<?php
header('Content-Type: application/json; charset=utf-8');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

if (count($segments) >= 4 && $segments[1] === 'joueur' && $segments[3] === 'parties') {
    $licence = $segments[2];
    
    // Identifiants publics de l'application SmartPing et paramètres requis
    $app_id = "132715"; // ID d'application standard
    $app_passe = md5("132715"); // Mot de passe hashé
    
    $url = "https://apiv2.fftt.com/mobile/pxml/xml_partie.php?numlic=" . $licence . "&id=" . $app_id . "&passe=" . $app_passe;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, "SmartPing");
    $response = curl_exec($ch);
    curl_close($ch);

    $parties = [];
    if ($response) {
        $xml = @simplexml_load_string($response);
        if ($xml) {
            $nodes = [];
            if (isset($xml->resultat)) {
                $nodes = $xml->resultat;
            } elseif (isset($xml->partie)) {
                $nodes = $xml->partie;
            } elseif (isset($xml->ligne)) {
                $nodes = $xml->ligne;
            }

            foreach ($nodes as $p) {
                $parties[] = [
                    "nom" => (string)($p->nom ?? $p->advnom ?? 'Adversaire'),
                    "prenom" => (string)($p->prenom ?? $p->advprenom ?? ''),
                    "classement" => (string)($p->classement ?? $p->advclassement ?? '500'),
                    "vd" => (string)($p->vd ?? $p->victoire ?? ''),
                    "date" => (string)($p->date ?? '')
                ];
            }
        }
    }
    
    echo json_encode($parties);
    exit;
}

echo json_encode([]);
