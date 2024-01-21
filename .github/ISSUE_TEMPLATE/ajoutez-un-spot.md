---
name: Ajoutez un spot
about: Ajouter, modifier ou supprimer un spot en remplissant les champs
title: Ajout d'un spot
labels: addSpot
assignees: ''

---

# Opération

Cocher une et une seule des 3 cases obligatoirement en mettant un "x" entre les crochets ou à la souris.

Dans le cas d'une suppression, seul le champ **name** est pris en compte.

Dans le cas d'une modification, le champ **name** et tous les champs à mettre à jour sont nécessaires. Les autres resteront inchangés.

Pour une création, l'ensemble des champs sont requis.

- [x] Ajout
- [ ] Modification
- [ ] Suppression

# Spot Template a remplir

\# *obligatoire quelque soit l'opération, set d'identifiant et doit être exactement identique en cas de modification, suppression*
name: La Creche

\# *au choix : plaine, treuil, bord-de-mer*
type: bord-de-mer

\# *au choix : nord, autre*
localisation: nord

\# *URL météoblue pour le spot*
url: https://www.meteoblue.com/fr/meteo/semaine/terlincthun_france_3295326

\# *les directions correctes, séparé par des espaces, pas de virgule*
goodDirection: OSO ONO O

\# *vitesse minimum requise pour le spot, doit être un entier*
minSpeed: 13

\# *vitesse maximum requise pour le spot, doit être un entier*
maxSpeed: 23

distance: 132km 1h50mn

geoloc: 50.75035246960507 1.594958234383776

\# *URL pour les marées si le spot est de type bord-de-mer. Attention au slash a la fin qui est souvent présent. Ligne a retirer si le spot n'est pas de type bord-de-mer* 
tideTableUrl: https://www.horaire-maree.fr/maree/Wimereux/

\# *liste des jours interdits, 0 = dimanche, 1 = lundi... S'il n'y a pas d'interdiction, supprimer la ligne.*
excludeDays: 0 1

\# *liste des mois durant lesquels l'interdiction à lieu, 1 = janvier, 12 = décembre. S'il n'y a pas d'interdiction, supprimer la ligne.*
monthsToExcludes: 8 9 10

description: Fermeture de janvier a fin aout

balise: https://balisemeteo.com/balise.php?idBalise=5030

ffvl: https://federation.ffvl.fr/terrain/740

youtube: https://www.youtube.com/watch?v=L3AfTOjkCpU

# Spot Template a remplir
