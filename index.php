<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 1. Configuration des identifiants Smartping
$appId = "aP1PInG";
$appKey = "8x9c4V#2p$";
$serial = "1000000"; // Numéro de série virtuel

// 2. Récupération de la licence
$licence = $_GET['licence'] ?? '';
if (empty($licence) && preg_match('/\/joueur\/([0-9]+)/', $_SERVER['REQUEST_URI'], $matches)) {
    $licence = $matches[1];
}

if (empty($licence)) {
    http_response_code(400);
    echo json_encode(["error" => "Licence manquante"]);
    exit;
}

// Helper pour générer la signature HMAC FFTT
function generateAuthParams($appId, $appKey, $serial) {
    $tm = date("YmdHis") . substr(microtime(), 2, 3);
    $tmc = hash_hmac("sha1", $tm, md5($appKey));
    return [
        'serie' => $serial,
        'tm' => $tm,
        'tmc' => $tmc,
        'id' => $appId
    ];
}

// 3. ÉTAPE 1 : Initialisation de la session auprès de la FFTT (Obligatoire)
$initParams = generateAuthParams($appId, $appKey, $serial);
$initUrl = "http://www.smartping.fr/actif/xml_initialisation.php?" . http_build_query($initParams);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $initUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Smartping/2.0');
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$initResponse = curl_exec($ch);

// 4. ÉTAPE 2 : Récupération des parties du joueur
$partieParams = generateAuthParams($appId, $appKey, $serial);
$partieParams['licence'] = $licence;

// URL pour les matchs enregistrés dans SPID
$partieUrl = "http://www.smartping.fr/actif/xml_partie.php?" . http_build_query($partieParams);

curl_setopt($ch, CURLOPT_URL, $partieUrl);
$partieResponse = curl_exec($ch);
curl_close($ch);

// 5. ÉTAPE 3 : Parsing du XML reçu
$parties = [];

if ($partieResponse) {
    $xml = @simplexml_load_string($partieResponse);
    
    if ($xml) {
        // La FFTT renvoie parfois les nœuds sous <partie> ou <PARTIE> ou <resultat>
        $nodes = [];
        if (isset($xml->partie)) $nodes = $xml->partie;
        elseif (isset($xml->PARTIE)) $nodes = $xml->PARTIE;
        elseif (isset($xml->resultat)) $nodes = $xml->resultat;
        
        foreach ($nodes as $item) {
            $nom = (string)($item->nom ?? $item->NOM ?? '');
            $prenom = (string)($item->prenom ?? $item->PRENOM ?? '');
            $classement = (string)($item->classement ?? $item->CLASSEMENT ?? '500');
            $vd = (string)($item->vd ?? $item->VD ?? 'D');
            $date = (string)($item->date ?? $item->DATE ?? '');
            
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

// 6. Si aucun match individuel n'est trouvé, tentative fallback sur l'historique global
if (empty($parties)) {
    // Si xml_partie est vide, on tente xml_joueur pour vérifier la validité de la licence
    $joueurParams = generateAuthParams($appId, $appKey, $serial);
    $joueurParams['licence'] = $licence;
    $joueurUrl = "http://www.smartping.fr/actif/xml_joueur.php?" . http_build_query($joueurParams);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $joueurUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Smartping/2.0');
    $joueurResponse = curl_exec($ch);
    curl_close($ch);
}

echo json_encode($parties, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
