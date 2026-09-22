<?php
// Autorisateur CORS pour permettre à ton appli iOS/Swift de consommer l'API
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 1. Clés d'accès Smartping / FFTT
$appId = "aP1PInG";
$appKey = "8x9c4V#2p$";

// 2. Extraction de la licence depuis l'URL ou les paramètres GET
$licence = $_GET['licence'] ?? '';

if (empty($licence)) {
    // Tente d'extraire la licence depuis une URL du type /api/joueur/0213164/parties
    if (preg_match('/\/joueur\/([0-9]+)/', $_SERVER['REQUEST_URI'], $matches)) {
        $licence = $matches[1];
    }
}

// Si aucune licence n'est fournie
if (empty($licence)) {
    http_response_code(400);
    echo json_encode(["error" => "Licence manquante"]);
    exit;
}

// 3. Génération du timestamp et du hash HMAC demandés par la FFTT
$tm = date("YmdHis") . substr(microtime(), 2, 3);
$tmc = hash_hmac("sha1", $tm, md5($appKey));

// 4. Appel HTTP vers l'API officielle Smartping
$url = "http://www.smartping.fr/actif/xml_partie.php?" . http_build_query([
    'serie' => '1000000',
    'tm' => $tm,
    'tmc' => $tmc,
    'id' => $appId,
    'licence' => $licence
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_USERAGENT, 'Smartping/2.0');
$response = curl_exec($ch);
curl_close($ch);

// 5. Conversion du flux XML reçu vers du JSON propre pour Swift
$parties = [];

if ($response) {
    $xml = @simplexml_load_string($response);
    
    if ($xml) {
        // Détection de la structure XML (PARTIE ou partie selon la version du serveur)
        $items = isset($xml->PARTIE) ? $xml->PARTIE : (isset($xml->partie) ? $xml->partie : []);
        
        foreach ($items as $item) {
            $nom = (string)($item->nom ?? $item->NOM ?? '');
            $prenom = (string)($item->prenom ?? $item->PRENOM ?? '');
            $classement = (string)($item->classement ?? $item->CLASSEMENT ?? '500');
            $vd = (string)($item->vd ?? $item->VD ?? 'D');
            $date = (string)($item->date ?? $item->DATE ?? '');
            
            // Ne conserve que les entrées exploitables
            if (!empty($nom) || !empty($prenom)) {
                $parties[] = [
                    'nom' => trim($nom),
                    'prenom' => trim($prenom),
                    'classement' => trim($classement),
                    'vd' => strtoupper(trim($vd)),
                    'date' => trim($date)
                ];
            }
        }
    }
}

// 6. Envoi de la réponse JSON à l'application Swift
echo json_encode($parties, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
