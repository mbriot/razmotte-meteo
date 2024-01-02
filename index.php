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
                        <input type="checkbox" id="nord" name="localisation" value="nord"><label for="nord">Nord</label>
                        <input type="checkbox" id="autre" name="localisation" value="autre"><label for="autre">Autre</label>
                    </div>
                </div>
            
                <div>
                    <span>Type de site : </<span> 
                    <div class="type-input">
                        <input type="checkbox" id="bdm" name="bdm" value="bdm"><label for="bdm">Bord de mer</label>
                        <input type="checkbox" id="plaine" name="plaine" value="plaine"><label for="plaine">Plaine</label>
                        <input type="checkbox" id="treuil" name="treuil" value="treuil"><label for="treuil">Treuil</label>
                    </div>
                        </div>
            
                <div>
                    <span>Orientation correct : </<span> 
                    <div class="sort-input">
                        <input type="checkbox" id="vent-semaine" name="vent-semaine" value="vent-semaine"><label for="vent-semaine">Toute la semaine</label>
                        <input type="checkbox" id="vent-wk" name="vent-wk" value="vent-wk"><label for="vent-wk">Le week-end</label>
                    </div>
                </div>
            </div>
        </th>
        <th class="settings-button">
            <button onclick="valider()">Valider</button>
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
            echo '<table>
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

    function valider() {
        var nordCheckbox = document.getElementById('nord');
        var autreCheckbox = document.getElementById('autre');
        var bdmCheckbox = document.getElementById('bdm');
        var plaineCheckbox = document.getElementById('plaine');
        var treuilCheckbox = document.getElementById('treuil');
        var ventCheckbox = document.getElementById('vent-semaine');
        var ventWkCheckbox = document.getElementById('vent-wk');

        var url = window.location.href.split('?')[0]; // URL de base sans les paramètres
        url = url.split('#')[0];
        var params = [];

        if (nordCheckbox.checked && autreCheckbox.checked){params.push('localisation=nord,autre');}
        else if (nordCheckbox.checked) {params.push('localisation=nord');}
        else if (autreCheckbox.checked) {params.push('localisation=autre');}

        if(bdmCheckbox.checked && plaineCheckbox.checked && treuilCheckbox.checked){params.push('type=bord-de-mer,treuil,plaine');}
        else if(bdmCheckbox.checked && treuilCheckbox.checked){params.push('type=bord-de-mer,treuil');}
        else if(bdmCheckbox.checked && plaineCheckbox.checked){params.push('type=bord-de-mer,plaine');}
        else if(plaineCheckbox.checked && treuilCheckbox.checked){params.push('type=plaine,treuil');}
        else if(plaineCheckbox.checked){params.push('type=plaine');}
        else if(treuilCheckbox.checked){params.push('type=treuil');}
        else if(bdmCheckbox.checked){params.push('type=bord-de-mer');}

        if (ventWkCheckbox.checked) {params.push('sortByGoodDirectionWk=true');}
        else if (ventCheckbox.checked) {params.push('sortByGoodDirection=true');}

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

function compareByNumberOfGoodDirection($a, $b) {
    return $b['numberOfGoodDirection'] - $a['numberOfGoodDirection'];
}

function sortByGoodDirection($predictions){
    uasort($predictions['spots'], 'compareByNumberOfGoodDirection');
    return $predictions;
}

function compareByNumberOfGoodDirectionWk($a, $b) {
    return $b['numberOfGoodDirectionWk'] - $a['numberOfGoodDirectionWk'];
}

function sortByGoodDirectionWk($predictions){
    uasort($predictions['spots'], 'compareByNumberOfGoodDirectionWk');
    return $predictions;
}

function filterPredictions($predictions, $arguments){

    if(isset($arguments['type'])){
        $predictions = filterByType($predictions, $arguments['type']);
    }

    if(isset($arguments['localisation'])){
        $predictions = filterByLocalisation($predictions, $arguments['localisation']);
    }

    if(isset($arguments['sortByGoodDirectionWk'])){
        $predictions = sortByGoodDirectionWk($predictions);
    } else if(isset($arguments['sortByGoodDirection'])){
        $predictions = sortByGoodDirection($predictions);
    }

    return json_decode(json_encode($predictions));
}

?>