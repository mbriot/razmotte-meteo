<?php
/**
 * API Endpoint for Razmotte Météo Results
 * 
 * Usage:
 *   GET /result.php                                    - Retourne tous les résultats
 *   GET /result.php?localisation=nord                  - Filtre par localisation
 *   GET /result.php?type=bord-de-mer                   - Filtre par type de site
 *   GET /result.php?days=lun,mar,ven                   - Filtre par jours
 *   GET /result.php?localisation=nord&type=plaine      - Combine plusieurs filtres
 *   GET /result.php?format=pretty                      - Sortie formatée (readability)
 * 
 * Response:
 *   Content-Type: application/json
 *   HTTP 200: Données valides
 *   HTTP 404: Fichier result.json non trouvé
 *   HTTP 400: Paramètres invalides
 * 
 * Examples:
 *   curl http://localhost:8000/result.php
 *   curl http://localhost:8000/result.php?localisation=nord
 *   curl "http://localhost:8000/result.php?type=bord-de-mer,plaine"
 */

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if result.json exists
$resultPath = __DIR__ . '/result.json';
if (!file_exists($resultPath)) {
    http_response_code(404);
    echo json_encode([
        'error' => 'result.json not found',
        'message' => 'Please run meteoblue-parser.php first',
        'path' => $resultPath
    ]);
    exit;
}

// Load result.json
try {
    $content = file_get_contents($resultPath);
    if ($content === false) {
        throw new Exception('Cannot read result.json');
    }
    $results = json_decode($content, true);
    if ($results === null) {
        throw new Exception('Invalid JSON in result.json: ' . json_last_error_msg());
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to load results',
        'message' => $e->getMessage()
    ]);
    exit;
}

// Apply filters
try {
    if (isset($_GET['localisation'])) {
        $results = filterByLocalisation($results, $_GET['localisation']);
    }
    
    if (isset($_GET['type'])) {
        $results = filterByType($results, $_GET['type']);
    }
    
    if (isset($_GET['days'])) {
        $results = filterByDays($results, $_GET['days']);
    }
    
    if (isset($_GET['spot'])) {
        $results = filterBySpot($results, $_GET['spot']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid filter parameters',
        'message' => $e->getMessage()
    ]);
    exit;
}

// Format output
$flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
if (isset($_GET['format']) && $_GET['format'] === 'pretty') {
    $flags |= JSON_PRETTY_PRINT;
}

http_response_code(200);
echo json_encode($results, $flags);
exit;

/**
 * Filter results by localisation
 */
function filterByLocalisation($results, $localisations) {
    $multiLoc = explode(',', $localisations);
    $multiLoc = array_map('trim', $multiLoc);
    
    // Validate localizations
    $validLocs = ['nord', 'autre', 'normandie', 'ardenne', 'belgique', 'vosges'];
    foreach ($multiLoc as $loc) {
        if (!in_array(strtolower($loc), $validLocs)) {
            throw new Exception("Invalid localisation: $loc");
        }
    }
    
    $filtered = [];
    foreach ($results['spots'] as $spotName => $spotData) {
        if (isset($spotData['localisation']) && in_array($spotData['localisation'], $multiLoc)) {
            $filtered[$spotName] = $spotData;
        }
    }
    
    $results['spots'] = $filtered;
    $results['filtered_by_localisation'] = $multiLoc;
    return $results;
}

/**
 * Filter results by type
 */
function filterByType($results, $types) {
    $multiType = explode(',', $types);
    $multiType = array_map('trim', $multiType);
    
    // Validate types
    $validTypes = ['bord-de-mer', 'plaine', 'treuil', 'cross'];
    foreach ($multiType as $type) {
        if (!in_array(strtolower($type), $validTypes)) {
            throw new Exception("Invalid type: $type");
        }
    }
    
    $filtered = [];
    foreach ($results['spots'] as $spotName => $spotData) {
        if (isset($spotData['type']) && in_array($spotData['type'], $multiType)) {
            $filtered[$spotName] = $spotData;
        }
    }
    
    $results['spots'] = $filtered;
    $results['filtered_by_type'] = $multiType;
    return $results;
}

/**
 * Filter by specific days and return sorted results
 */
function filterByDays($results, $days) {
    $daysList = explode(',', $days);
    $daysList = array_map(function($day) { return strtolower(trim($day)); }, $daysList);
    
    // Validate days
    $validDays = ['lun', 'mar', 'mer', 'jeu', 'ven', 'sam', 'dim'];
    foreach ($daysList as $day) {
        if (!in_array($day, $validDays)) {
            throw new Exception("Invalid day: $day");
        }
    }
    
    // Sort by combined score of selected days
    $spots = $results['spots'];
    uasort($spots, function($a, $b) use ($daysList) {
        $scoreA = 0;
        $scoreB = 0;
        
        foreach ($daysList as $day) {
            $scoreField = $day . 'Score';
            if (isset($a[$scoreField])) {
                $scoreA += $a[$scoreField];
            }
            if (isset($b[$scoreField])) {
                $scoreB += $b[$scoreField];
            }
        }
        
        return $scoreB - $scoreA;
    });
    
    $results['spots'] = $spots;
    $results['sorted_by_days'] = $daysList;
    return $results;
}

/**
 * Filter by specific spot name (exact match or contains)
 */
function filterBySpot($results, $spotName) {
    $spotName = trim($spotName);
    
    $filtered = [];
    foreach ($results['spots'] as $name => $spotData) {
        if (strtolower($name) === strtolower($spotName) || 
            stripos($name, $spotName) !== false) {
            $filtered[$name] = $spotData;
        }
    }
    
    if (empty($filtered)) {
        throw new Exception("No spot found matching: $spotName");
    }
    
    $results['spots'] = $filtered;
    $results['filtered_by_spot'] = $spotName;
    return $results;
}

?>
