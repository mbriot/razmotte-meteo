# Documentation Développeurs - Razmotte Météo

## Vue d'ensemble du projet

Razmotte Météo est une application web PHP qui agrège et analyse les données météorologiques des sites de vol libre (parapente, delta) via le service MeteoBlue. Le projet scrape les données, les évalue pour déterminer la volabilité, et les affiche via une interface web interactive.

## Architecture générale

```
razmotte-meteo/
├── index.php              # Interface web principale
├── index.css              # Styles CSS
├── meteoblue-parser.php   # Script de scraping et traitement
├── spots.json             # Configuration des sites
├── result.json            # Résultats du dernier scraping
├── simple_html_dom.php    # Librairie de parsing HTML
└── images/                # Ressources images
```

## Fichiers clés

### `spots.json`
Configuration des sites de vol libre. Chaque site contient:

```json
{
  "name": "Equihen",
  "type": "bord-de-mer",                    // bord-de-mer, plaine, treuil
  "localisation": "nord",                   // nord, autre, etc.
  "url": "%C3%89quihen-plage_france_3019957",  // URL MeteoBlue
  "goodDirection": ["O", "OSO", "SO"],      // Directions de vent en anglais
  "goodDirectionInFrench": ["O", "OSO", "SO"], // Directions en français
  "minSpeed": 13,                           // Vitesse vent min (km/h)
  "maxSpeed": 23,                           // Vitesse vent max (km/h)
  "distance": "140km,2h",                   // Distance du club
  "geoloc": "50.67953007244548, 1.5670255869881216", // Coordonnées GPS
  "needSeaCheck": true,                     // Besoin données marées
  "tideTableUrl": "Equihen-Plage/",         // URL table des marées
  "description": "...",                     // Description longue HTML
  "balise": "https://...",                  // Lien balise météo
  "ffvl": "https://...",                    // Lien FFVL
  "youtube": "https://...",                 // Lien vidéo
  "excludeDays": [0, 6],                    // Jours à exclure (optionnel)
  "monthsToExcludes": [12, 1]               // Mois à exclure (optionnel)
}
```

### `result.json`
Structure des résultats générés après scraping:

```json
{
  "lastRun": "05-04-2026 14:30",
  "spots": {
    "Equihen": {
      "type": "bord-de-mer",
      "localisation": "nord",
      "minSpeed": 13,
      "maxSpeed": 23,
      "days": [
        {
          "day": "mer 5 avr",
          "closed": false,
          "_9h": {
            "min": {"speed": 15, "flyable": "flyable"},
            "max": {"speed": 18, "flyable": "flyable"},
            "dir": {"dir": "O", "flyable": true}
          },
          "_12h": {...},
          "_15h": {...},
          "temp": "12~18",
          "rain": {"rain": "0mm", "rainClass": "rain"},
          "sunHour": {"sun": 6, "sunClass": "sun-orange"},
          "weatherSentence": "Partiellement nuageux",
          "tide": {
            "coeff": "45",
            "first": "09:15",
            "second": "21:45"
          }
        }
      ],
      "lunScore": 750,
      "marScore": 650,
      "merScore": 800,
      "jeuScore": 750,
      "venScore": 600,
      "samScore": 850,
      "dimScore": 900,
      "numberOfGoodDirection": 5,
      "numberOfGoodDirectionWk": 2,
      "weekScore": 4150,
      "weekendScore": 1750,
      "nextThreeDaysScore": 2050
    }
  }
}
```

## Flux de données

### 1. Scraping (`meteoblue-parser.php`)

Le script exécute les étapes suivantes:

#### Étape 1: Chargement des sites
- Lit `spots.json`
- Divise les sites en 5 batches pour respecter les limites API
- Batch 1: spots 0-9
- Batch 2: spots 10-19
- Batch 3: spots 20-29
- Batch 4: spots 30-39
- Batch 5: spots 40+

#### Étape 2: Scraping MeteoBlue
Pour chaque site et chaque jour (7 jours):
```php
$urlBuilded = 'https://www.meteoblue.com/fr/meteo/semaine/' . $spot->url . "?day=" . $day;
```
- Récupère les données HTML
- Parse avec `simple_html_dom.php`:
  - Vitesses de vent à 9h, 12h, 15h
  - Direction du vent
  - Température min/max
  - Données pluie et ensoleillement
  - Description météo

#### Étape 3: Scraping des marées (si `needSeaCheck: true`)
```php
$html = file_get_html('https://www.horaire-maree.fr/maree/' . $url);
```
- Récupère coefficient et heures de pleines mers

#### Étape 4: Évaluation de la volabilité
Pour chaque site et chaque créneau (3h) de chaque jour:

```php
$flyableDir = in_array($slot->dir, $values->goodDirection);
if ($flyableDir) {
    $score = scoreSlot($min, $max, $minSpeed, $maxSpeed);
    // Score = 1000 si vent exact, 500 si légèrement élevé, etc.
}
```

### 2. Calcul des scores

#### Scores par jour
```php
$dayScore = nombre_de_créneaux_volables * points_par_créneau + bonus_direction;
$lunScore, $marScore, $merScore, ... = $dayScore
```

#### Scores agrégés
```php
weekScore = lun + mar + mer + jeu + ven + 25*numberOfGoodDirection
weekendScore = sam + dim + 25*numberOfGoodDirectionWk
nextThreeDaysScore = today + tomorrow + day+2
```

### 3. Affichage (`index.php`)

#### Filtrage
```php
filterByType($predictions, $type)        // Filtre par type de site
filterByLocalisation($predictions, $loc) // Filtre par localisation
filterPredictions($predictions, $args)   // Applique tous les filtres
```

#### Tri
```php
sortByMultipleDays($predictions, ['lun', 'mar', 'mer'])  // Tri par jours sélectionnés
// Additionne les scores des jours sélectionnés
```

#### Front-end (JavaScript)
- `toggleDay()` - Active/désactive les boutons de jours
- `getDefaultDays()` - Retourne les 3 prochains jours
- `fillFiltersBasedOnUrl()` - Restaure les filtres depuis URL
- `submitFilters()` - Construit l'URL avec les filtres

## Variables d'environnement et configuration

### Exécution du batch
```bash
# Auto-détecte le batch selon l'heure
php meteoblue-parser.php

# Ou force un batch spécifique
php meteoblue-parser.php batchNumber=1

# Ou utilise un fichier spots personnalisé
php meteoblue-parser.php spotFile=/chemin/vers/spots.json batchNumber=2
```

### Batches automatiques par heure (UTC)
```
6h, 12h, 18h, 21h → Batch 1
5h, 11h, 17h, 22h → Batch 2
4h, 16h → Batch 3
3h, 15h → Batch 4
2h, 14h → Batch 5
```

## Traductions et mappings

### Traduction des directions de vent
```php
$windDirTranslation = [
    "N" => "N",
    "NE" => "NE",
    "E" => "E",
    "SE" => "SE",
    "S" => "S",
    "SW" => "SO",
    "W" => "O",
    "NW" => "NO",
    // ... 16 directions au total
];
```

### Classes CSS de volabilité
```php
"flyable" => vert (vent dans la bonne plage de vitesse et direction)
"not-flyable" => rouge (vent trop fort)
"not-flyable-wrong-dir" => gris (mauvaise direction)
```

### Classes CSS les conditions météo
```
Pluie: "0mm" → noir | "X-Ymm" → bleu
Soleil: 0-1h → noir | 2-3h → jaune | 4-6h → orange | 7+ → rouge
Température: noir sur blanc
```

## Ajouter un nouveau site

1. **Trouver l'URL MeteoBlue**:
   - Visiter https://www.meteoblue.com/fr/
   - Chercher la zone
   - Noter l'URL dernière partie après `/semaine/`

2. **Ajouter à `spots.json`**:
```json
{
  "name": "Mon Site",
  "type": "plaine",
  "localisation": "nord",
  "url": "monsitecode_france_12345",
  "goodDirection": ["NE", "E", "SE"],
  "goodDirectionInFrench": ["NE", "E", "SE"],
  "minSpeed": 10,
  "maxSpeed": 20,
  "distance": "50km,1h",
  "geoloc": "50.5,2.5",
  "description": "Description du site",
  "balise": "https://...",
  "ffvl": "https://...",
  "youtube": "https://..."
}
```

3. **Relancer le scraping**:
```bash
php meteoblue-parser.php batchNumber=1
```

## Ajouter une nouvelle région

1. **Ajouter les sites avec la nouvelle `localisation`**:
```json
{
  "localisation": "normandie",
  ...
}
```

2. **Passer les jours à affichage en l'état**, les filtres suppriment automatiquement les autres régions

## Structure des URL de paramètres

```
?localisation=nord           // Sites du Nord
?localisation=autre          // Autres sites
?type=bord-de-mer           // Sites côtiers
?type=plaine,treuil         // Plaines OU treuils
?days=lun,mar,mer           // Trier par lun+mar+mer
?days=sam,dim               // Ou par week-end
?localisation=nord&type=plaine&days=ven
```

## Logging

Les erreurs et info sont loggées dans `error.log`:
```
INFO - 2026-04-05 14:30:15.234 - Starting new parsing 
INFO - 2026-04-05 14:30:15.340 - Enter getSpots function with spotFile = ...
ERROR - 2026-04-05 14:30:20.456 - Failed to fetch HTML from ...
```

Le fichier est auto-nettoyé quand il dépasse 500 KB.

## Performance

- **Scraping**: ~1-2 secondes par site (avec délai de 1-3s entre les requêtes)
- **Parsing**: ~0.1s par jour par site
- **Affichage**: Immédiat (JSON pré-généré)
- **Batch complet**: ~10-15 minutes pour 40+ sites

## Points d'amélioration

- Paralléliser le scraping avec cURL multi
- Cacher les résultats MeteoBlue en base de données
- Ajouter une API JSON dédiée
- Tests unitaires pour la logique d'évaluation
- Alertes automatiques (email, SMS)

## Dépannage

### Aucun résultat affiché
1. Vérifier que `result.json` existe
2. Vérifier que `spots.json` est valide JSON
3. Vérifier les logs dans `error.log`

### Scraping échoue
1. Vérifier que MeteoBlue.com est accessible
2. Vérifier que `simple_html_dom.php` est présent
3. Réduire la taille du batch (moins de 10 sites)
4. Augmenter le délai entre les requêtes

### Mauvaises données météo
1. Vérifier l'URL du site dans MeteoBlue
2. Vérifier que les sélecteurs CSS n'ont pas changé
3. Vérifier le formatage des données dans les logs
