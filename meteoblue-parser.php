<?php

require __DIR__ . '/simple_html_dom.php';

$LOGFILE =  __DIR__ . '/error.log';
$RESULT_PATH = __DIR__ . '/result.json';

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
    $spotsJson = file_get_contents('spots.json');
    $spots = json_decode($spotsJson)->spots;
    return $spots;
}

function scrapeSpots ($spots) {
    $allSpotsResult = [];
    foreach ($spots as $spot) {
        _log("info","Nom : " . $spot->name . ", url : " . $spot->url);
        
        $days = [];
        for ($i = 1; $i <= 7; $i++) {
            $result = parseMeteoblue($spot->url,$i);
            array_push($days,$result);
        }
        $spotResult = [];
        $spotResult['days'] = $days;
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

function parseMeteoblue($url, $day){
    $urlBuilded = 'https://www.meteoblue.com/fr/meteo/semaine/' . $url . "?day=" . $day;
    $html = file_get_html($urlBuilded);
    _log("info","Will parse with url " . $urlBuilded);
    $maxTemp = preg_replace('/\s+/', ' ',preg_replace('/[^0-9]/', '', $html->find('div[id=day'.$day.'] div.tab-content div.temps div.tab-temp-max',0)->plaintext));
    $minTemp = preg_replace('/\s+/', ' ',preg_replace('/[^0-9]/', '', $html->find('div[id=day'.$day.'] div.tab-content div.temps div.tab-temp-min',0)->plaintext));
    $rain = preg_replace('/\s+/', ' ', $html->find('div[id=day'.$day.'] div.data div.tab-precip',0)->plaintext);
    $sunHour = preg_replace('/\s+/', ' ', $html->find('div[id=day'.$day.'] div.data div.tab-sun',0)->plaintext);
    $sentenceWeather = preg_replace('/\s+/', ' ', $html->find('div[id=day'.$day.'] div.tab-content div.weather.day img',0)->getAttribute('title'));
    $pression = $html->find('div.misc span',1)->plaintext;

    _log("info",$maxTemp . "~" . $minTemp . " " . $rain . " " . $sunHour . " " . $sentenceWeather . " " . $pression . "\n");

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
    $result['pression'] = $pression;
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
    $predictions = json_decode(file_get_contents('result.json'));
    foreach ($predictions->spots as $spotName => $values) {
        foreach ($values->days as $day) {
            foreach([$day->_9h,$day->_12h,$day->_15h] as $slot){
                $flyableMin = $slot->min >= $values->minSpeed && $slot->min <= $values->maxSpeed; 
                $slot->min = ["speed" => $slot->min, "flyable" => $flyableMin];
                $flyableMax = $slot->max <= $values->maxSpeed && $slot->max >= $values->minSpeed; 
                $slot->max = ["speed" => $slot->max, "flyable" => $flyableMax];
                $flyableDir = in_array($slot->dir, $values->goodDirection);
                $slot->dir = ["dir" => $slot->dir, "flyable" => $flyableDir];
            }
        }
    }
    return $predictions;
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

deleteLogFile();
_log("info","Starting new parsing");
$spots = getSpots();
$results = scrapeSpots($spots);
file_put_contents($RESULT_PATH, json_encode($results));
$results = evaluateResults();
$results->lastRun = date("d-m-Y H:i");
_log("info",json_encode($results));
file_put_contents($RESULT_PATH, json_encode($results));
?>