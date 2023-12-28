<html>
<body>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Météo des sites</title>
    <link rel="stylesheet" href="index.css">
    <link rel="icon" href="/images/photos/LOGO/Logo_Razmotte_accueil.jpg" type="image/x-icon">
</head>
<?php

$predictions = json_decode(file_get_contents('result.json'));

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
echo '<span>Dernier run :' . $predictions->lastRun . '</span>';
echo '<table border="1">
        <tr>
        <th> <div id="legend-back" class="legend"><a href="#legend">Ct ça marche ?</a></div></th>';
            foreach ($days as $day) {
                echo  "<th>${day}</th>";
            } 
echo    '</tr>';

foreach ($predictions->spots as $spotName => $values) {
    echo  '<tr>
            <th>
                <a id="' . str_replace(' ', '_', strtolower($spotName)) . '" href="#' . str_replace(' ', '_', strtolower($spotName)) . '-desc">' . $spotName . '</a>
                <div>' . $values->minSpeed . ' à '. $values->maxSpeed .'km/h</div>
                <div>' . join(', ', $values->goodDirection) . '</div>
                <div>' . $values->distance . '</div>
            </th>';
    foreach ($values->days as $day) {
        echo '<td>';
            echo '<table>
                    <tr>
                        <td class="9hWind ' . ($day->_9h->min->flyable ? "flyable" : "not-flyable") .'"><span class="9hWind ' . ($day->_9h->min->flyable ? "flyable" : "not-flyable") . '">' . $day->_9h->min->speed . '</span></td>
                        <td class="12hWind ' . ($day->_12h->min->flyable ? "flyable" : "not-flyable") . '"><span class="12hWind ' . ($day->_12h->min->flyable ? "flyable" : "not-flyable") . '">' . $day->_12h->min->speed . '</span></td>
                        <td class="15hWind ' . ($day->_15h->min->flyable ? "flyable" : "not-flyable") . '"><span class="15hWind ' . ($day->_15h->min->flyable ? "flyable" : "not-flyable") . '">' . $day->_15h->min->speed . '</span></td>
                    </tr>
                    <tr>
                        <td class="9hWind ' . ($day->_9h->max->flyable ? "flyable" : "not-flyable") .'"><span class="9hWind ' . ($day->_9h->max->flyable ? "flyable" : "not-flyable") . '">' . $day->_9h->max->speed . '</span></td>
                        <td class="12hWind ' . ($day->_12h->max->flyable ? "flyable" : "not-flyable") .'"><span class="12hWind ' . ($day->_12h->max->flyable ? "flyable" : "not-flyable") . '">' . $day->_12h->max->speed . '</span></td>
                        <td class="15hWind ' . ($day->_15h->max->flyable ? "flyable" : "not-flyable") .'"><span class="15hWind ' . ($day->_15h->max->flyable ? "flyable" : "not-flyable") . '">' . $day->_15h->max->speed . '</span></td>
                    </tr>
                    <tr>
                        <td class="9hWind ' . ($day->_9h->dir->flyable ? "flyable" : "not-flyable") . '"><span class="9hWind' . ($day->_9h->dir->flyable ? "flyable" : "not-flyable") . '">' . $day->_9h->dir->dir . '</span></td>
                        <td class="12hWind ' . ($day->_12h->dir->flyable ? "flyable" : "not-flyable") . '"><span class="12hWind' . ($day->_12h->dir->flyable ? "flyable" : "not-flyable") . '">' . $day->_12h->dir->dir . '</span></td>
                        <td class="15hWind ' . ($day->_15h->dir->flyable ? "flyable" : "not-flyable") . '"><span class="15hWind' . ($day->_15h->dir->flyable ? "flyable" : "not-flyable") . '">' . $day->_15h->dir->dir . '</span></td>
                    </tr>
                </table>';
            echo '
                    <div class="weatherSentence">' . $day->weatherSentence . '</div>
                    <div class="weatherResume">' . $day->rain . ' / ' . $day->pression . ' / ' . $day->sunHour . ' / ' . $day->temp . '°C </div>';
        echo '</td>';
    }
    echo '</tr>';
}

echo '</table>';

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
</body>
</html>