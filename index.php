<!DOCTYPE html>
<html>
<body>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Météo des sites</title>
    <link rel="stylesheet" href="index.css">
    <link rel="icon" href="/images/photos/LOGO/Logo_Razmotte_accueil.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" >
</head>

<?php
$arguments = $_GET;
$predictions = json_decode(file_get_contents('result.json'),true);
$predictions = filterPredictions($predictions,$arguments);

$days = [];
foreach(current($predictions->spots)->days as $dayResult){
    array_push($days,$dayResult->day);
}

echo '
    <table class="start">
        <tr>
            <th class="logo"><img src="images/razmotte_logo.jpg"></th>
            <th class="title"><span>Météo des sites du Nord pas de calais</span></th>
        </tr>
    </table>
';

echo '
    <table id="settings-table" style="display:none;">
    <tr>
        <th class="settings-form">
            <div id="div-settings">
                <div>
                    <span>Localisation : </<span>
                    <div class="loc-input">
                        <button id="nord-button" onclick="toggleLocationOrType(this)" class="choice inactive" >Nord</button>
                        <button id="autre-button" onclick="toggleLocationOrType(this)" class="choice inactive" >Autre</button>
                    </div>
                </div>

                <div>
                    <span>Type de site : </<span>
                    <div class="loc-input">
                        <button id="bdm-button" onclick="toggleLocationOrType(this)" class="choice inactive" >Bord de mer</button>
                        <button id="plaine-button" onclick="toggleLocationOrType(this)" class="choice inactive" >Plaine</button>
                        <button id="treuil-button" onclick="toggleLocationOrType(this)" class="choice inactive" >Treuil</button>
                    </div>
                </div>

                <div>
                    <span>Trier par volabilité : </span> 
                    <div class="flyability-input">
                        <button id="flyability-week-button" onclick="toggleFlyability(this)" class="choice inactive" >Semaine</button>
                        <button id="flyability-weekend-button" onclick="toggleFlyability(this)" class="choice inactive" >Week-End</button>
                    </div>
                </div>
            </div>
        </th>
        <th class="settings-button">
            <button onclick="submitFilters()">Valider</button>
        </th>
    </tr>
    </table>';

echo '<span>Dernier run :' . $predictions->lastRun . '</span>';
echo '
    <div class="table-container">
    <table class="wind-data" border="1">
        <thead>
        <tr>
        <th> <div id="legend-back" class="legend"><i id="settings" class="fas fa-cog"></i></div></th>';
            foreach ($days as $day) {
                echo  "<th>
                        ${day}
                        <table><td>9h</td><td>12h</td><td>15h</td></table>
                    </th>";
            } 
echo    '</tr></thead>';

echo '<tbody>';
foreach ($predictions->spots as $spotName => $values) {
    echo  '<tr class="week-result">
            <th>
                <a id="' . str_replace(' ', '_', strtolower($spotName)) . '" href="#' . str_replace(' ', '_', strtolower($spotName)) . '-desc">' . $spotName . '</a>
                <div>' . $values->minSpeed . ' à '. $values->maxSpeed .'km/h</div>
                <div>' . join(', ', $values->goodDirection) . '</div>
                <div>' . $values->distance . '</div>
            </th>';
    foreach ($values->days as $day) {
        echo '<td>';
        if ($day->closed){
            echo '<div class="closed">Fermé</div>';
        } else {
            echo '<table class="day-result">
                    <tr>
                        <td class="9hWind ' . $day->_9h->min->flyable .'"><span class="9hWind ' . $day->_9h->min->flyable . '">' . $day->_9h->min->speed . '</span></td>
                        <td class="12hWind ' . $day->_12h->min->flyable . '"><span class="12hWind ' . $day->_12h->min->flyable . '">' . $day->_12h->min->speed . '</span></td>
                        <td class="15hWind ' . $day->_15h->min->flyable . '"><span class="15hWind ' . $day->_15h->min->flyable . '">' . $day->_15h->min->speed . '</span></td>
                    </tr>
                    <tr>
                        <td class="9hWind ' . $day->_9h->max->flyable .'"><span class="9hWind ' . $day->_9h->max->flyable . '">' . $day->_9h->max->speed . '</span></td>
                        <td class="12hWind ' . $day->_12h->max->flyable .'"><span class="12hWind ' . $day->_12h->max->flyable . '">' . $day->_12h->max->speed . '</span></td>
                        <td class="15hWind ' . $day->_15h->max->flyable .'"><span class="15hWind ' . $day->_15h->max->flyable . '">' . $day->_15h->max->speed . '</span></td>
                    </tr>
                    <tr>
                        <td class="9hWind ' . ($day->_9h->dir->flyable ? "flyable-wind" : "not-flyable-wind") . '"><span class="9hWind' . ($day->_9h->dir->flyable ? "flyable-wind" : "not-flyable-wind") . '">' . $day->_9h->dir->dir . '</span></td>
                        <td class="12hWind ' . ($day->_12h->dir->flyable ? "flyable-wind" : "not-flyable-wind") . '"><span class="12hWind' . ($day->_12h->dir->flyable ? "flyable-wind" : "not-flyable-wind") . '">' . $day->_12h->dir->dir . '</span></td>
                        <td class="15hWind ' . ($day->_15h->dir->flyable ? "flyable-wind" : "not-flyable-wind") . '"><span class="15hWind' . ($day->_15h->dir->flyable ? "flyable-wind" : "not-flyable-wind") . '">' . $day->_15h->dir->dir . '</span></td>
                    </tr>
                </table>';
            echo '
                    <div class="weatherSentence">' . $day->weatherSentence . '</div>
                    <div class="weatherResume">
                      <span class="' . $day->rain->rainClass . '">' . $day->rain->rain . '</span>
                      <span class="' . $day->sunHour->sunClass . '">' . $day->sunHour->sun . 'h</span>  
                      <span class="temp">' . $day->temp . '°C</span>
                    </div>';
            if(property_exists($day,'tide')){
                echo '<div class="tide">
                        <span class="tide-text">PM : ' . $day->tide->first . ' et ' . $day->tide->second . ', coeff ' . $day->tide->coeff . ' </span>
                    </div>';
            }
        }
        echo '</td>';
    }
    echo '</tr>';
}
echo '</tbody></table></div>';

echo '
    <br><br><br>
    <div id="legend"><a href="#legend-back">Ct ça marche ?</a></div>
    <p>
        Les données ci-dessus proviennent du site météoblue.<br>
        La mise à jour est faite 3 fois par jour à 6/12/18h<br>
        Les sites de vols sont classés par distance par rapport à Lille <br>
        Ci dessous, une image qui explique comment lire les données pour un spot et un jour donné
    </p>
    <img class="img-legend" src="images/legende_mieux.png">
';

foreach ($predictions->spots as $spotName => $values) {
    echo '<h1><a id="' . str_replace(' ', '_', strtolower($spotName)) . '-desc" href="#' . str_replace(' ', '_', strtolower($spotName)) . '">' . $spotName .'</a></h1>';
    $balise = empty($values->balise) ? 'N/A' : '<a href="' . $values->balise . '">Balise</a>';
    $ffvl = empty($values->ffvl) ? 'N/A' : '<a href="' . $values->ffvl . '">FFVL</a>';
    echo '
    <table class="spot-desc">
        <tr>
            <td class="spot-title">Vent conseillé (min/max)</td>
            <td class="spot-value">' . $values->minSpeed . '/' . $values->maxSpeed . '</td>
        </tr>
        <tr>
            <td class="spot-title">Directions du vent</td>
            <td class="spot-value">' . join(', ', $values->goodDirection) . '</td>
        </tr>
        <tr>
            <td class="spot-title">Description</td>
            <td class="spot-value">' . $values->description . '</td>
        </tr>
        <tr>
            <td class="spot-title">Distance de Lille</td>
            <td class="spot-value">' . $values->distance . '</td>
        </tr>
        <tr>
            <td class="spot-title">Geolocalisation déco</td>
            <td class="spot-value">' . $values->geoloc . '</td>
        </tr>
        <tr>
            <td class="spot-title">FFVL</td>
            <td class="spot-value">' . $ffvl . '</td>
        </tr>
        <tr>
            <td class="spot-title">Balise</td>
            <td class="spot-value">' . $balise . '</td>
        </tr>
        <tr>
            <td class="spot-title">Lien Météoblue</td>
            <td class="spot-value"> <a href="https://www.meteoblue.com/fr/meteo/semaine/' . $values->url . '">météoblue</a></td>
        </tr>
        <tr>
            <td class="spot-title">Qques images Youtube</td>
            <td class="spot-value"><a href="' . $values->youtube . '">youtube</a></td>
        </tr>
    </table>
    ';
}

?>
<script>
    window.document.getElementById('settings').addEventListener('click', function() {
        var settingsTable = window.document.getElementById('settings-table');
        if (settingsTable.style.display === 'none') {
            settingsTable.style.display = 'block';
        } else {
            settingsTable.style.display = 'none';
        }
    });

    function toggleLocationOrType(clickedButton) {
        if (clickedButton.classList.contains("active")){
            clickedButton.classList.remove("active");
            clickedButton.classList.add("inactive");
        } else {
            clickedButton.classList.remove("inactive");
            clickedButton.classList.add("active");
        }
    }

    function toggleFlyability(clickedButton) {
        var flyabilityWeekButton = document.getElementById('flyability-week-button');
        var flyabilityWeekendButton = document.getElementById('flyability-weekend-button');
        if (clickedButton == flyabilityWeekButton){
            if(clickedButton.classList.contains("active")){
                clickedButton.classList.remove("active");
                clickedButton.classList.add("inactive");
            } else if (clickedButton.classList.contains("inactive")){
                clickedButton.classList.remove("inactive");
                clickedButton.classList.add("active");
                flyabilityWeekendButton.classList.remove("active");
                flyabilityWeekendButton.classList.add("inactive");
            }
        } else if (clickedButton == flyabilityWeekendButton){
            if(clickedButton.classList.contains("active")){
                clickedButton.classList.remove("active");
                clickedButton.classList.add("inactive");
            } else if (clickedButton.classList.contains("inactive")){
                clickedButton.classList.remove("inactive");
                clickedButton.classList.add("active");
                flyabilityWeekButton.classList.remove("active");
                flyabilityWeekButton.classList.add("inactive");
            }
        }
    }

    fillFiltersBasedOnUrl();

    function fillFiltersBasedOnUrl(){
        var nordButton = document.getElementById('nord-button');
        var autreButton = document.getElementById('autre-button');
        var bdmButton = document.getElementById('bdm-button');
        var plaineButton = document.getElementById('plaine-button');
        var treuilButton = document.getElementById('treuil-button');
        var flyabilityWeekButton = document.getElementById('flyability-week-button');
        var flyabilityWeekendButton = document.getElementById('flyability-weekend-button');

        var url = window.location.href.split('?')[1];
        if(url) {
            var params = url.split('&');
        } else {return;}
        
        for (let index = 0; index < params.length; index++){ 
            var paramName = params[index].split('=')[0];
            var paramValue = params[index].split('=')[1].split(',');
            for (let j = 0; j < paramValue.length; j++){
                if(paramName == "localisation" && paramValue[j] == "nord") {nordButton.classList.add("active");}
                if(paramName == "localisation" && paramValue[j] == "autre") {autreButton.classList.add("active");}
                if(paramName == "type" && paramValue[j] == "plaine") {plaineButton.classList.add("active");}
                if(paramName == "type" && paramValue[j] == "treuil") {treuilButton.classList.add("active");}
                if(paramName == "type" && paramValue[j] == "bord-de-mer") {bdmButton.classList.add("active");}
                if(paramName == "sortByFlyabilityWeek" ) {flyabilityWeekButton.classList.add("active");}
                if(paramName == "sortByFlyabilityWeekend" ) {flyabilityWeekendButton.classList.add("active");}
            } 
        }
    }

    function submitFilters() {
        var nordButton = document.getElementById('nord-button');
        var autreButton = document.getElementById('autre-button');
        var bdmButton = document.getElementById('bdm-button');
        var plaineButton = document.getElementById('plaine-button');
        var treuilButton = document.getElementById('treuil-button');
        var ventCheckbox = document.getElementById('vent-semaine');
        var ventWkCheckbox = document.getElementById('vent-wk');
        var flyabilityWeekButton = document.getElementById('flyability-week-button');
        var flyabilityWeekendButton = document.getElementById('flyability-weekend-button');

        var url = window.location.href.split('?')[0];
        url = url.split('#')[0];
        var params = [];

        if (nordButton.classList.contains("active") && autreButton.classList.contains("active")){params.push('localisation=nord,autre');}
        else if (nordButton.classList.contains("active")) {params.push('localisation=nord');}
        else if (autreButton.classList.contains("active")) {params.push('localisation=autre');}

        if(bdmButton.classList.contains("active") && plaineButton.classList.contains("active") && treuilButton.classList.contains("active")){
            params.push('type=bord-de-mer,treuil,plaine');
        }
        else if(bdmButton.classList.contains("active") && treuilButton.classList.contains("active")){params.push('type=bord-de-mer,treuil');}
        else if(bdmButton.classList.contains("active") && plaineButton.classList.contains("active")){params.push('type=bord-de-mer,plaine');}
        else if(plaineButton.classList.contains("active") && treuilButton.classList.contains("active")){params.push('type=plaine,treuil');}
        else if(plaineButton.classList.contains("active")){params.push('type=plaine');}
        else if(treuilButton.classList.contains("active")){params.push('type=treuil');}
        else if(bdmButton.classList.contains("active")){params.push('type=bord-de-mer');}

        if (flyabilityWeekButton.classList.contains("active")) {params.push('sortByFlyabilityWeek=true');}
        else if (flyabilityWeekendButton.classList.contains("active")) {params.push('sortByFlyabilityWeekend=true');}

        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        window.location.href = url;
    }

</script>
</body>
</html>

<?php
function filterByType($predictions, $type){
    $multiType = explode(',',$type);
    $spots = array_filter($predictions['spots'], function($spot) use ($multiType) {
        return isset($spot['type']) && in_array($spot['type'],$multiType);
    });
    $predictions['spots'] = $spots;
    return $predictions;
}

function filterByLocalisation($predictions, $localisation){
    $multiLoc = explode(',',$localisation);
    $spots = array_filter($predictions['spots'], function($spot) use ($multiLoc) {
        return isset($spot['localisation']) && in_array($spot['localisation'],$multiLoc);
    });
    $predictions['spots'] = $spots;
    return $predictions;
}

function compareByNumberOfFlyabilityWeek($a, $b) {
    return $b['weekScore'] - $a['weekScore'];
}

function sortByFlyabilityWeek($predictions){
    uasort($predictions['spots'], 'compareByNumberOfFlyabilityWeek');
    return $predictions;
}

function compareByNumberOfFlyabilityWeekend($a, $b) {
    return $b['weekendScore'] - $a['weekendScore'];
}

function sortByFlyabilityWeekend($predictions){
    uasort($predictions['spots'], 'compareByNumberOfFlyabilityWeekend');
    return $predictions;
}

function filterPredictions($predictions, $arguments){

    if(isset($arguments['type'])){
        $predictions = filterByType($predictions, $arguments['type']);
    }

    if(isset($arguments['localisation'])){
        $predictions = filterByLocalisation($predictions, $arguments['localisation']);
    }

    if(isset($arguments['sortByFlyabilityWeek'])){
        $predictions = sortByFlyabilityWeek($predictions);
    } else if(isset($arguments['sortByFlyabilityWeekend'])){
        $predictions = sortByFlyabilityWeekend($predictions);
    }

    if (count($arguments) == 0){
        $predictions = filterByLocalisation($predictions, "nord");
        $predictions = sortByFlyabilityWeek($predictions);
    }

    return json_decode(json_encode($predictions));
}

?>