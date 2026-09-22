<?php
header('Content-Type: application/json; charset=utf-8');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

if (count($segments) >= 4 && $segments[1] === 'joueur' && $segments[3] === 'parties') {
    $licence = $segments[2];
    
    // Ajout des paramètres obligatoires exigés par l'API mobile FFTT
    $url = "https://apiv2.fftt.com/mobile/pxml/xml_partie.php?numlic=" . $licence . "&serie=000000&id=000000";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // Simulation de l'application officielle Smartping pour que la FFTT accepte la requête
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
