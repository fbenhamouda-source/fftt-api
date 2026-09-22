<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 1. Récupération de la licence
$licence = $_GET['licence'] ?? '';
if (empty($licence) && preg_match('/\/joueur\/([0-9]+)/', $_SERVER['REQUEST_URI'], $matches)) {
    $licence = $matches[1];
}

if (empty($licence)) {
    http_response_code(400);
    echo json_encode(["error" => "Licence manquante"]);
    exit;
}

// 2. Scraping de la page joueur sur le site officiel FFTT / Ping
$url = "https://www.fftt.com/site/personne/" . urlencode($licence);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
// On simule un vrai navigateur Chrome sur Mac pour ne pas se faire bloquer
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
$html = curl_exec($ch);
curl_close($ch);

$parties = [];

if ($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // Extraction des lignes du tableau de matchs dans le HTML
    $rows = $xpath->query("//table[contains(@class, 'tableau')]//tr");

    foreach ($rows as $row) {
        $cols = $row->getElementsByTagName('td');
        if ($cols->length >= 4) {
            $nomPrenom = trim($cols->item(0)->nodeValue);
            $classement = trim($cols->item(1)->nodeValue);
            $vd = trim($cols->item(2)->nodeValue);
            $date = trim($cols->item(3)->nodeValue);

            // Extraction nom et prénom
            $parts = explode(' ', $nomPrenom, 2);
            $nom = $parts[0] ?? '';
            $prenom = $parts[1] ?? '';

            // Nettoyage du classement (ne garder que les chiffres)
            preg_match('/[0-9]+/', $classement, $ptsMatches);
            $pts = $ptsMatches[0] ?? '500';

            // Normalisation Victoire / Défaite
            $isVictoire = (strpos(strtoupper($vd), 'V') !== false) ? 'V' : 'D';

            if (!empty($nom)) {
                $parties[] = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'classement' => $pts,
                    'vd' => $isVictoire,
                    'date' => $date
                ];
            }
        }
    }
}

// 3. Si FFTT bloque le scraping HTML brut, secours via l'API Web Espace Licencié FFTT
if (empty($parties)) {
    $urlAlt = "https://fftt.dafou.fr/api/joueur/" . urlencode($licence) . "/parties";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlAlt);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $jsonAlt = curl_exec($ch);
    curl_close($ch);

    if ($jsonAlt) {
        $data = json_decode($jsonAlt, true);
        if (is_array($data)) {
            $parties = $data;
        }
    }
}

echo json_encode($parties, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
