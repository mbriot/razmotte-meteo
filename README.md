# razmotte-meteo
crawling of meteo data for the razmotte paragliding club


### Launch localy

- php 8+
- `php meteoblue-parser.php` - parse meteo-blue and build a result.json file with all the data
- `php -S localhost:8000` - open http://localhost:8000 to see the index.php

## Documentation

- [USERS.md](USERS.md) - Guide utilisateur: comment utiliser le site
- [DEVELOPERS.md](DEVELOPERS.md) - Documentation technique: API endpoint, architecture, flux de données


# TODO

- alerte si 3j de suite volable sur une région : normandie, vosges, ardennes
- site de gonflage bondue et l'autre et parc des iles - ajouter type=gonflage
- racourci clavier avec point interrogation, l = lundi, m = mardi, m fois deux = mercredi... 
 s pour semain, mm pour mercredi -> smm ou alors smj -> semaine mardi et jeudi
 l pour localisation, nor,ard

Je veux un nouveau script php, qui lit le fichier result.json
Pour l'ensemble des sites avec localisation=normandie. Si j'ai un score > 25 pour un des sites et à la fois pour vendredi,samedi,dimanche. Ou bien samedi,dimanche,lundi alors ecrire avec un echo "alerte 3j volable : localistion, j1,j2,j3