<?php
// Configuration API FFTT (Clés publiques de l'application officielle Smartping)
$appId = "aP1PInG"; 
$appKey = "8x9c4V#2p$"; // Ou la clé utilisée par le wrapper Smartping

$licence = $_GET['licence'] ?? '';

if (empty($licence)) {
    http_response_code(400);
    echo json_encode(["error" => "Licence manquante"]);
    exit;
}

// 1. Génération du timestamp et de la clé de sécurité (tm & tmc)
$tm = date("YmdHis") . substr(microtime(), 2, 3);
$tmc = hash_hmac("sha1", $tm, md5($appKey));

// 2. Construction de l'URL d'appel vers les serveurs FFTT
$url = "http://www.smartping.fr/actif/xml_partie.php?" . http_build_query([
    'serie' => '1000000', // Serial généré ou fixe
    'tm' => $tm,
    'tmc' => $tmc,
    'id' => $appId,
    'licence' => $licence
]);

// 3. Exécution de la requête HTTP
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Smartping/2.0');
$response = curl_exec($ch);
curl_close($ch);

// 4. Conversion du XML FFTT vers le format JSON attendu par ton appli Swift
$xml = simplexml_load_string($response);
$parties = [];

if ($xml && isset($xml->partie)) {
    foreach ($xml->partie as $item) {
        $parties[] = [
            'nom' => (string)$item->nom,
            'prenom' => (string)$item->prenom,
            'classement' => (string)$item->classement,
            'vd' => (string)$item->vd, // 'V' ou 'D'
            'date' => (string)$item->date
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($parties);
