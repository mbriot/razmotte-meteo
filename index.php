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
                    <span>Localisation : </span>
                    <div class="dropdown" id="localisation-dropdown">
                        <button class="dropdown-toggle" onclick="toggleDropdown(this)" id="localisation-button">
                            <span id="selected-regions-display">Sélectionner régions</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu" id="localisation-menu">
                            <div class="dropdown-header">
                                <button class="dropdown-action-btn" onclick="selectAllRegions()"><i class="fas fa-check"></i> Tout</button>
                                <button class="dropdown-action-btn" onclick="clearAllRegions()"><i class="fas fa-times"></i> Rien</button>
                            </div>
                            <div class="dropdown-content">
                                <label><input type="checkbox" class="region-checkbox" value="nord" onchange="updateRegionDisplay()"> Nord</label>
                                <label><input type="checkbox" class="region-checkbox" value="picardie" onchange="updateRegionDisplay()"> Picardie</label>
                                <label><input type="checkbox" class="region-checkbox" value="normandie" onchange="updateRegionDisplay()"> Normandie</label>
                                <label><input type="checkbox" class="region-checkbox" value="champagne" onchange="updateRegionDisplay()"> Champagne</label>
                                <label><input type="checkbox" class="region-checkbox" value="ardennes" onchange="updateRegionDisplay()"> Ardennes</label>
                                <label><input type="checkbox" class="region-checkbox" value="belgique" onchange="updateRegionDisplay()"> Belgique</label>
                                <label><input type="checkbox" class="region-checkbox" value="hollande" onchange="updateRegionDisplay()"> Hollande</label>
                                <label><input type="checkbox" class="region-checkbox" value="vosges" onchange="updateRegionDisplay()"> Vosges</label>
                            </div>
                        </div>
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
                    <span>Trier par jour : </span> 
                    <div class="flyability-input">
                        <button id="flyability-lun" onclick="toggleDay(this)" class="choice-day inactive" >Lun</button>
                        <button id="flyability-mar" onclick="toggleDay(this)" class="choice-day inactive" >Mar</button>
                        <button id="flyability-mer" onclick="toggleDay(this)" class="choice-day inactive" >Mer</button>
                        <button id="flyability-jeu" onclick="toggleDay(this)" class="choice-day inactive" >Jeu</button>
                        <button id="flyability-ven" onclick="toggleDay(this)" class="choice-day inactive" >Ven</button>
                        <button id="flyability-sam" onclick="toggleDay(this)" class="choice-day inactive" >Sam</button>
                        <button id="flyability-dim" onclick="toggleDay(this)" class="choice-day inactive" >Dim</button>
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
                echo  "<th style='cursor: pointer;' onclick=\"filterByDay(this, '${day}')\">
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
                <div>' . join(', ', $values->goodDirectionInFrench) . '</div>
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
            <td class="spot-value">' . join(', ', $values->goodDirectionInFrench) . '</td>
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

    function updateRegionDisplay() {
        var checked = [];
        document.querySelectorAll('input.region-checkbox:checked').forEach(function(checkbox) {
            var label = checkbox.nextSibling.nodeValue;
            checked.push(checkbox.value.charAt(0).toUpperCase() + checkbox.value.slice(1));
        });
        
        var display = document.getElementById('selected-regions-display');
        if (checked.length === 0) {
            display.textContent = 'Sélectionner régions';
        } else if (checked.length === 8) {
            display.textContent = 'Toutes les régions';
        } else {
            display.textContent = checked.join(', ');
        }
    }

    function selectAllRegions() {
        document.querySelectorAll('input.region-checkbox').forEach(function(checkbox) {
            checkbox.checked = true;
        });
        updateRegionDisplay();
    }

    function clearAllRegions() {
        document.querySelectorAll('input.region-checkbox').forEach(function(checkbox) {
            checkbox.checked = false;
        });
        updateRegionDisplay();
    }

    function toggleDropdown(button) {
        var menu = button.nextElementSibling;
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }

    function toggleLocationOrType(clickedButton) {
        if (clickedButton.classList.contains("active")){
            clickedButton.classList.remove("active");
            clickedButton.classList.add("inactive");
        } else {
            clickedButton.classList.remove("inactive");
            clickedButton.classList.add("active");
        }
    }

    document.addEventListener('click', function(event) {
        var dropdown = document.getElementById('localisation-dropdown');
        if (dropdown && !dropdown.contains(event.target)) {
            var menu = dropdown.querySelector('.dropdown-menu');
            if (menu) menu.style.display = 'none';
        }
    });

    function filterByDay(headerCell, dayText) {
        // Extract day abbreviation from text like "lun.  6 avril"
        var dayAbbrev = dayText.split('.')[0].toLowerCase();
        
        // Get current filters (regions and types)
        var checkedRegions = [];
        document.querySelectorAll('input.region-checkbox:checked').forEach(function(checkbox) {
            checkedRegions.push(checkbox.value);
        });
        
        var bdmButton = document.getElementById('bdm-button');
        var plaineButton = document.getElementById('plaine-button');
        var treuilButton = document.getElementById('treuil-button');
        
        var selectedTypes = [];
        if (bdmButton.classList.contains('active')) selectedTypes.push('bord-de-mer');
        if (plaineButton.classList.contains('active')) selectedTypes.push('plaine');
        if (treuilButton.classList.contains('active')) selectedTypes.push('treuil');
        
        // Build URL
        var url = window.location.href.split('?')[0];
        url = url.split('#')[0];
        var params = [];
        
        if (checkedRegions.length > 0) {
            params.push('localisation=' + checkedRegions.join(','));
        }
        
        if (selectedTypes.length > 0) {
            params.push('type=' + selectedTypes.join(','));
        }
        
        // Add only the clicked day
        params.push('days=' + dayAbbrev);
        
        // Reload page with new filters
        if (params.length > 0) {
            window.location.href = url + '?' + params.join('&');
        }
    }

    function toggleDay(clickedButton) {
        if(clickedButton.classList.contains("active")){
            clickedButton.classList.remove("active");
            clickedButton.classList.add("inactive");
        } else if (clickedButton.classList.contains("inactive")){
            clickedButton.classList.remove("inactive");
            clickedButton.classList.add("active");
        }
    }

    fillFiltersBasedOnUrl();
    updateRegionDisplay();

    function getDefaultDays() {
        var days = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
        var today = new Date();
        var todayIndex = today.getDay();
        if (todayIndex === 0) todayIndex = 7; // Sunday is 0 in JS, map to 7
        todayIndex = todayIndex - 1; // Convert to 0-6 range
        
        var defaultDays = [];
        for (var i = 0; i < 3; i++) {
            var dayIndex = (todayIndex + i) % 7;
            defaultDays.push(days[dayIndex]);
        }
        return defaultDays;
    }

    function fillFiltersBasedOnUrl(){
        var url = window.location.href.split('?')[1];
        var defaultDays = getDefaultDays();
        
        if(url) {
            var params = url.split('&');
        } else {
            document.querySelector('input[value="nord"]').checked = true;
            defaultDays.forEach(function(day) {
                var btn = document.getElementById('flyability-' + day.toLowerCase());
                if (btn) {
                    btn.classList.remove('inactive');
                    btn.classList.add('active');
                }
            });
            return;
        }
        
        for (let index = 0; index < params.length; index++){
            var paramName = params[index].split('=')[0];
            var paramValue = params[index].split('=')[1].split(',');
            for (let j = 0; j < paramValue.length; j++){
                if(paramName == "localisation") {
                    var checkbox = document.querySelector('input[value="' + paramValue[j] + '"]');
                    if (checkbox) checkbox.checked = true;
                }
                if(paramName == "type" && paramValue[j] == "plaine") {document.getElementById('plaine-button').classList.add("active");}
                if(paramName == "type" && paramValue[j] == "treuil") {document.getElementById('treuil-button').classList.add("active");}
                if(paramName == "type" && paramValue[j] == "bord-de-mer") {document.getElementById('bdm-button').classList.add("active");}
                if(paramName == "days" ) {
                    var dayId = 'flyability-' + paramValue[j].toLowerCase();
                    var btn = document.getElementById(dayId);
                    if (btn) {
                        btn.classList.remove('inactive');
                        btn.classList.add('active');
                    }
                }
            }
        }
    }

    function submitFilters() {
        var bdmButton = document.getElementById('bdm-button');
        var plaineButton = document.getElementById('plaine-button');
        var treuilButton = document.getElementById('treuil-button');
        var daysButtons = document.querySelectorAll('.choice-day.active');

        var url = window.location.href.split('?')[0];
        url = url.split('#')[0];
        var params = [];

        var checkedRegions = [];
        document.querySelectorAll('input.region-checkbox:checked').forEach(function(checkbox) {
            checkedRegions.push(checkbox.value);
        });
        if (checkedRegions.length > 0) {
            params.push('localisation=' + checkedRegions.join(','));
        }

        if(bdmButton.classList.contains("active") && plaineButton.classList.contains("active") && treuilButton.classList.contains("active")){
            params.push('type=bord-de-mer,treuil,plaine');
        }
        else if(bdmButton.classList.contains("active") && treuilButton.classList.contains("active")){params.push('type=bord-de-mer,treuil');}
        else if(bdmButton.classList.contains("active") && plaineButton.classList.contains("active")){params.push('type=bord-de-mer,plaine');}
        else if(plaineButton.classList.contains("active") && treuilButton.classList.contains("active")){params.push('type=plaine,treuil');}
        else if(plaineButton.classList.contains("active")){params.push('type=plaine');}
        else if(treuilButton.classList.contains("active")){params.push('type=treuil');}
        else if(bdmButton.classList.contains("active")){params.push('type=bord-de-mer');}

        if (daysButtons.length > 0) {
            var selectedDays = [];
            daysButtons.forEach(function(btn) {
                var dayText = btn.textContent.toLowerCase();
                selectedDays.push(dayText);
            });
            params.push('days=' + selectedDays.join(','));
        }

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

function compareByNumberOfFlyability($a, $b, $type) {
    return $b[$type] - $a[$type];
}

function sortByFlyability($predictions, $type){
    $comparisonFunction = function ($a, $b) use ($type) {
        return compareByNumberOfFlyability($a, $b, $type);
    };
    uasort($predictions['spots'], $comparisonFunction);
    return $predictions;
}


function getDefaultSelectedDays() {
    $days = array('Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim');
    $today = intval(date('N')); // 1=Monday to 7=Sunday
    $todayIndex = $today - 1;
    
    $defaultDays = array();
    for ($i = 0; $i < 3; $i++) {
        $dayIndex = ($todayIndex + $i) % 7;
        $defaultDays[] = strtolower($days[$dayIndex]);
    }
    return $defaultDays;
}

function sortByMultipleDays($predictions, $daysList) {
    $comparisonFunction = function ($a, $b) use ($daysList) {
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
    };
    uasort($predictions['spots'], $comparisonFunction);
    return $predictions;
}

function filterPredictions($predictions, $arguments){

    if(isset($arguments['type'])){
        $predictions = filterByType($predictions, $arguments['type']);
    }

    if(isset($arguments['localisation'])){
        $predictions = filterByLocalisation($predictions, $arguments['localisation']);
    }

    if(isset($arguments['days'])){
        $daysList = explode(',', $arguments['days']);
        $predictions = sortByMultipleDays($predictions, $daysList);
    } else {
        $predictions = filterByLocalisation($predictions, "nord");
        $defaultDays = getDefaultSelectedDays();
        $predictions = sortByMultipleDays($predictions, $defaultDays);
    }

    if (count($arguments) == 0){
        $predictions = filterByLocalisation($predictions, "nord");
        $defaultDays = getDefaultSelectedDays();
        $predictions = sortByMultipleDays($predictions, $defaultDays);
    }

    return json_decode(json_encode($predictions));
}

?>