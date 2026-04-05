# razmotte-meteo
crawling of meteo data for the razmotte paragliding club


### Launch localy

- php 8+
- php meteoblue-parser.py #parse meteo-blue and build a result.json file with all the data
- php -S localhost:8000 # open http://localhost:8000 to see the index.php


# TODO
- ajouter filtre region : ardenne, nord, belgique, normandie, vosges
- ajouter le drumont, Haulme, des sites de normandie
- generer un nouveau Readme qui explique le fonctionnement du projet, pour utilisateur et developpeur
- un endpoint /result qui permet de récupérer les resultats
- un script local qui alerte si 3j de suite volable sur une région