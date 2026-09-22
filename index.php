<?php
header('Content-Type: application/json; charset=utf-8');

// Forcer le fuseau horaire de Paris pour que l'horodatage FFTT soit exact
date_default_timezone_set('Europe/Paris');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

if (count($segments) >= 4 && $segments[1] === 'joueur' && $segments[3] === 'parties') {
    $licence = $segments[2];
    
    $app_id = "132715";
    $password = "hello";
    $tm = date('YmdHis');
    $cle = md5($tm . md5($password));
    
    $url = "https://apiv2.fftt.com/mobile/pxml/xml_partie.php?numlic=" . $licence . "&serie=000000&id=" . $app_id . "&tm=" . $tm . "&cle=" . $cle;
    
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
