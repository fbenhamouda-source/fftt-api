<?php
header('Content-Type: application/json; charset=utf-8');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

if (count($segments) >= 4 && $segments[1] === 'joueur' && $segments[3] === 'parties') {
    $licence = $segments[2];
    // Correction du paramètre : numlic au lieu de licence
    $url = "https://apiv2.fftt.com/mobile/pxml/xml_partie.php?numlic=" . $licence;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $parties = [];
    if ($response) {
        $xml = @simplexml_load_string($response);
        if ($xml) {
            // Lecture des balises resultat ou partie renvoyées par l'API
            $nodes = isset($xml->resultat) ? $xml->resultat : ($xml->partie ?? []);
            foreach ($nodes as $p) {
                $parties[] = [
                    "nom" => (string)($p->nom ?? $p->advnom ?? ''),
                    "prenom" => (string)($p->prenom ?? $p->advprenom ?? ''),
                    "classement" => (string)($p->classement ?? $p->advclassement ?? '500'),
                    "vd" => (string)($p->vd ?? ''),
                    "date" => (string)($p->date ?? '')
                ];
            }
        }
    }
    
    echo json_encode($parties);
    exit;
}

echo json_encode([]);
