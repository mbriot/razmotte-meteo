<?php

require __DIR__ . '/simple_html_dom.php';

$LOGFILE =  __DIR__ . '/error.log';
$RESULT_PATH = __DIR__ . '/result.json';
$SPOT_FILE = __DIR__ . '/spots.json';

function getDateMs(){
    $date = date("Y-m-d H:i:s");
    $microseconds = microtime(true);
    $milliseconds = sprintf("%03d", ($microseconds - floor($microseconds)) * 1000);

    return $date . '.' . $milliseconds;
}

function _log ($level, $message) {
    global $LOGFILE;
    $level = strtoupper($level);
    $date = getDateMs();
    $logMessage = "$level - $date - $message \n"; 
    echo $logMessage;
    error_log($logMessage, 3, $LOGFILE);
}

function getSpots () {
    global $SPOT_FILE;
    _log("info","Enter getSpots function with spotFile = " . $SPOT_FILE);
    try {
        $spotsJson = file_get_contents($SPOT_FILE);
        if ($spotsJson === false) {
            throw new Exception("Erreur lors de la lecture du fichier spots.json avec le path : " . __DIR__ . 'spots.json');
        }
    } catch (Exception $e) {
        _log("error","problème de lecture avec le path " . __DIR__ . 'spots.json');
        _log("error", "Problème sur la lecture du fichier spots.json : " . $e);
        exit(1);
    }
    
    _log("info","spots.json red");
    try {
        $spots = json_decode($spotsJson)->spots;
    } catch(Excption $e){
        _log("error","problème pour décoder le json");
        _log("error", $e);
        exit(1);
    }
    _log("info","spots.json decoded, number of spots : " . count($spots));
    return $spots;
}

function scrapeSpots ($spots) {
    $allSpotsResult = [];
    foreach ($spots as $spot) {
        _log("info","Nom : " . $spot->name . ", url : " . $spot->url);
        
        setlocale(LC_TIME, 'fr_FR.utf8');
        $actualDate = new DateTime();
        $dayNumberToFrench = ["Dim","Lun","Mar","Mer","Jeu","Ven","Sam"];
        $days = [];
        if(property_exists($spot,'needSeaCheck')){
            $tideTable = getTideTable($spot->tideTableUrl);
        }
        for ($i = 1; $i <= 7; $i++) {
            if(spotIsClosed($actualDate, $spot)){
                $result = ["closed" => true, "day"=>$dayNumberToFrench[$actualDate->format('w')]];
            } else {
                $result = parseMeteoblue($spot->url,$i);
                if(property_exists($spot, 'needSeaCheck')){
                    $result['tide'] = $tideTable[$i-1];
                }
            }
            $result['day'] = strftime('%a %e %b', $actualDate->getTimestamp());
            array_push($days,$result);
            $actualDate->modify('+1 day');
        }
        $spotResult = [];
        $spotResult['days'] = $days;
        $spotResult['type'] = $spot->type;
        $spotResult['localisation'] = $spot->localisation;
        $spotResult['url'] = $spot->url;
        $spotResult['minSpeed'] = $spot->minSpeed;
        $spotResult['maxSpeed'] = $spot->maxSpeed;
        $spotResult['goodDirection'] = $spot->goodDirection;
        $spotResult['distance'] = $spot->distance;
        $spotResult['geoloc'] = $spot->geoloc;
        $spotResult['description'] = $spot->description;
        $spotResult['balise'] = $spot->balise;
        $spotResult['ffvl'] = $spot->ffvl;
        $spotResult['youtube'] = $spot->youtube;
        $allSpotsResult["spots"][$spot->name] = $spotResult;
    }
    return $allSpotsResult;
}

function getTideTable($url){
    $html = file_get_html('https://www.horaire-maree.fr/maree/' . $url);
    $tideTable = [];
    $firstCoeff = $html->find('#i_donnesJour table tr',2)->find('td',0)->find('strong',0)->plaintext;
    $firstFullSeaFirst = $html->find('#i_donnesJour table tr',2)->find('td',2)->find('strong',0)->plaintext;
    $firstFullSeaSecond = $html->find('#i_donnesJour table tr',2)->find('td',5)->find('strong',0)->plaintext;

    $tideTable[] = ["coeff"=>$firstCoeff,"first"=>$firstFullSeaFirst,"second"=>$firstFullSeaSecond]; 

    for($i=1; $i<7; $i++){
        $coeff = $html->find('#i_donnesLongue table tr',$i+1)->find('td',1)->find('strong',0)->plaintext;
        $first = $html->find('#i_donnesLongue table tr',$i+1)->find('td',3)->find('strong',0)->plaintext;
        $second = $html->find('#i_donnesLongue table tr',$i+1)->find('td',6)->find('strong',0)->plaintext;
        $tideTable[] = ["coeff"=>$coeff,"first"=>$first,"second"=>$second];
    }
    return $tideTable;
}

function spotIsClosed($date, $spot){
    $weekDayNumber = $date->format('w');
    $monthNumber = $date->format('n');
    if(!property_exists($spot, 'excludeDays')){
        return false;
    }
    if(in_array($weekDayNumber,$spot->excludeDays) && in_array($monthNumber,$spot->monthsToExcludes)){
        return true;
    }
    return false;
}

function parseMeteoblue($url, $day){
    $urlBuilded = 'https://www.meteoblue.com/fr/meteo/semaine/' . $url . "?day=" . $day;
    $html = file_get_html($urlBuilded);
    _log("info","Will parse with url " . $urlBuilded);
    $maxTemp = preg_replace('/\s+/', ' ',preg_replace('/[^0-9]/', '', $html->find('div[id=day'.$day.'] div.tab-content div.temps div.tab-temp-max',0)->plaintext));
    $minTemp = preg_replace('/\s+/', ' ',preg_replace('/[^0-9]/', '', $html->find('div[id=day'.$day.'] div.tab-content div.temps div.tab-temp-min',0)->plaintext));
    $rain = preg_replace('/\s+/', ' ', $html->find('div[id=day'.$day.'] div.data div.tab-precip',0)->plaintext);
    $rain = ($rain == " - ") ? "0mm" : $rain;
    $sunHour = intval(strstr(preg_replace('/\s+/', ' ', $html->find('div[id=day'.$day.'] div.data div.tab-sun',0)->plaintext), 'h', true));
    $sentenceWeather = preg_replace('/\s+/', ' ', $html->find('div[id=day'.$day.'] div.tab-content div.weather.day img',0)->getAttribute('title'));

    _log("info",$maxTemp . "~" . $minTemp . " " . $rain . " " . $sunHour . " " . $sentenceWeather . "\n");

    $nineHourWindDir = $html->find('div.tab-detail.active table tr',4)->find('td',2)->plaintext;
    $twelveHourWindDir = $html->find('div.tab-detail.active table tr',4)->find('td',3)->plaintext;
    $fifteenHourWindDir = $html->find('div.tab-detail.active table tr',4)->find('td',4)->plaintext;
    _log("info","windDir = " . $nineHourWindDir . "/" . $twelveHourWindDir . "/" . $fifteenHourWindDir . "\n");

    $nineHourWind = preg_replace('/\s+/', '',$html->find('div.tab-detail.active table tr',5)->find('td',2)->find('div.cell.no-mobile',0)->plaintext);
    $twelveHourWind = preg_replace('/\s+/', '',$html->find('div.tab-detail.active table tr',5)->find('td',3)->find('div.cell.no-mobile',0)->plaintext);
    $fifteenHourWind = preg_replace('/\s+/', '',$html->find('div.tab-detail.active table tr',5)->find('td',4)->find('div.cell.no-mobile',0)->plaintext);
    $minWind = explode('-',$nineHourWind)[0] . '-' . explode('-',$twelveHourWind)[0] . '-' . explode('-',$fifteenHourWind)[0];
    $maxWind = explode('-',$nineHourWind)[1] . '-' . explode('-',$twelveHourWind)[1] . '-' . explode('-',$fifteenHourWind)[1];
    _log("info","minWind 9-13-15  = " . $minWind . "\n");
    _log("info","maxWind 9-13-15  = " . $maxWind . "\n");

    $dayName = preg_replace('/\s+/', '',$html->find('div[id=day'.$day.'] div.tab-day-short',0)->plaintext);
    _log("info",'day : ' . $dayName . " \n");

    $result = [];
    $result["day"] = $dayName;
    $result["rain"] = $rain;
    $result['sunHour'] = $sunHour;
    $result['temp'] = $minTemp . '~' . $maxTemp;
    $result["weatherSentence"] = $sentenceWeather;
    $result["_9h"] = ["min" => explode('-',$nineHourWind)[0], "max"=> explode('-',$nineHourWind)[1], "dir"=> $nineHourWindDir];
    $result["_12h"] = ["min" => explode('-',$twelveHourWind)[0], "max"=> explode('-',$twelveHourWind)[1], "dir"=> $twelveHourWindDir];
    $result["_15h"] = ["min" => explode('-',$fifteenHourWind)[0], "max"=> explode('-',$fifteenHourWind)[1], "dir"=> $fifteenHourWindDir];
    return $result;

}

function evaluateResults(){
    _log("info","start evaluate spots slots");
    $predictions = json_decode(file_get_contents(__DIR__ . '/result.json'));
    foreach ($predictions->spots as $spotName => $values) {
        $numberOfGoodDirectionSlot = 0;
        $numberOfGoodDirectionSlotWk = 0;
        foreach ($values->days as $day) {

            if (property_exists($day, 'closed')){
                continue;
            }

            foreach([$day->_9h,$day->_12h,$day->_15h] as $slot){
                $flyableDir = in_array($slot->dir, $values->goodDirection);
                if($flyableDir){
                    $slot->min = evaluateWind($slot->min, $values->minSpeed, $values->maxSpeed);
                    $slot->max = evaluateWind($slot->max, $values->minSpeed, $values->maxSpeed);
                    $numberOfGoodDirectionSlot++;
                    if(strpos($day->day, "sam") === 0 || strpos($day->day, "dim") === 0){
                        $numberOfGoodDirectionSlotWk++;
                    }
                } else {
                    $slot->min = ["speed" => $slot->min, "flyable" => "not-flyable-wrong-dir"];
                    $slot->max = ["speed" => $slot->max, "flyable" => "not-flyable-wrong-dir"];

                }
                $slot->dir = ["dir" => $slot->dir, "flyable" => $flyableDir];
            }

            $day->sunHour = evaluateSun($day->sunHour);
            $day->rain = evaluateRain($day->rain);
            $day->closed = false;
        }
        $values->numberOfGoodDirection = $numberOfGoodDirectionSlot;
        $values->numberOfGoodDirectionWk = $numberOfGoodDirectionSlotWk;
    }
    return $predictions;
}

function evaluateSun($sun){
    if (in_array($sun,[0,1])){
        return ["sun" => $sun, "sunClass" => "sun-black"];
    } else if (in_array($sun,[2,3])){
        return ["sun" => $sun, "sunClass" => "sun-yellow"];
    } else if (in_array($sun,[4,5,6])){
        return ["sun" => $sun, "sunClass" => "sun-orange"];
    } else {
        return ["sun" => $sun, "sunClass" => "sun-red"];
    }
}

function evaluateRain($rain){
    if ($rain == "0mm" || substr($rain, 0, 2) == "0-"){
        return ["rain" => $rain, "rainClass" => "rain"];
    } else {
        return ["rain" => $rain, "rainClass" => "rain-blue"];
    }
}

function evaluateWind($speedWind,$minSpeed,$maxSpeed){
    $flyable = "flyable";
    $speedWind = intval($speedWind);
    $minSpeed = intval($minSpeed);
    $maxSpeed = intval($maxSpeed);
    if ($speedWind < $minSpeed) {
        $flyable = "not-flyable-low";
    } else if ($speedWind >= $minSpeed && $speedWind <= $maxSpeed){
        $flyable = "flyable";
    } else if ($speedWind <= $maxSpeed + 5){
        $flyable = "not-flyable-medium";
    } else if ($speedWind <= $maxSpeed + 10){
        $flyable = "not-flyable";
    } else if ($speedWind > $maxSpeed + 10){
        $flyable = "not-flyable-strong";
    }
    return ["speed" => $speedWind, "flyable" => $flyable];
}

function deleteLogFile(){
    global $LOGFILE;
    if (file_exists($LOGFILE)) {
        if (unlink($LOGFILE)) {
            _log("info",'Le fichier de log a été supprimé avec succès.');
        } else {
            _log("info",'Erreur lors de la suppression du fichier de log.');
        }
    } else {
        _log("info",'Le fichier de log n\'existe pas.');
    }
}

function getParameters(&$SPOT_FILE){
    global $argv;
    foreach ($argv as $argument) {
        if (strpos($argument, '=') !== false) {
            list($name, $value) = explode('=', $argument, 2);
            if ($name == "spotFile"){
                $SPOT_FILE = $value;
            }
        }
    }
}

deleteLogFile();
getParameters($SPOT_FILE);
_log("info","Starting new parsing");
$spots = getSpots();
$results = scrapeSpots($spots);
file_put_contents($RESULT_PATH, json_encode($results));
$results = evaluateResults();
$results->lastRun = date("d-m-Y H:i");
_log("info",json_encode($results));
file_put_contents($RESULT_PATH, json_encode($results));

?>