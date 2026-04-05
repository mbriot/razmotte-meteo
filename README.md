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

- ajouter de nouveaux sites : drumont
- un script local qui alerte si 3j de suite volable sur une région
- optimiser batch timing avec délais entre appels
- ajouter tests unitaires