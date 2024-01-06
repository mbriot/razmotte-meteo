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

$previousResult = json_decode(file_get_contents('result.json'),true);
$spot = ["name" => "equihen", "url" => "Équihen-plage_france_3019957"];
$result = parseMeteoblue($spot['url'],2);
if($result == $previousResult){
    _log("info","parsing Ok, result not changed");
} else {
    _log("info","parsing Ok, result CHANGED");
    _log("info",json_encode($result, JSON_PRETTY_PRINT));
}
file_put_contents($RESULT_PATH, json_encode($result));


function parseMeteoblue($url, $day){
    $urlBuilded = 'https://www.meteoblue.com/fr/meteo/semaine/' . $url . "?day=" . $day;
    $html = file_get_html($urlBuilded);
   
    $nineHourWindDir = $html->find('div.tab-detail.active table tr',4)->find('td',2)->plaintext;
    $twelveHourWindDir = $html->find('div.tab-detail.active table tr',4)->find('td',3)->plaintext;
    $fifteenHourWindDir = $html->find('div.tab-detail.active table tr',4)->find('td',4)->plaintext;
    
    $nineHourWind = preg_replace('/\s+/', '',$html->find('div.tab-detail.active table tr',5)->find('td',2)->find('div.cell.no-mobile',0)->plaintext);
    $twelveHourWind = preg_replace('/\s+/', '',$html->find('div.tab-detail.active table tr',5)->find('td',3)->find('div.cell.no-mobile',0)->plaintext);
    $fifteenHourWind = preg_replace('/\s+/', '',$html->find('div.tab-detail.active table tr',5)->find('td',4)->find('div.cell.no-mobile',0)->plaintext);
   
    $result = [];
    $result["_9h"] = ["min" => explode('-',$nineHourWind)[0], "max"=> explode('-',$nineHourWind)[1], "dir"=> $nineHourWindDir];
    $result["_12h"] = ["min" => explode('-',$twelveHourWind)[0], "max"=> explode('-',$twelveHourWind)[1], "dir"=> $twelveHourWindDir];
    $result["_15h"] = ["min" => explode('-',$fifteenHourWind)[0], "max"=> explode('-',$fifteenHourWind)[1], "dir"=> $fifteenHourWindDir];
    return $result;
}
?>