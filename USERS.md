# Guide Utilisateur - Razmotte Météo

## Qu'est-ce que Razmotte Météo?

Razmotte Météo est un outil qui vous aide à planifier vos séances de vol libre (parapente, delta) en affichant les conditions météorologiques prévues pour les 7 prochains jours sur tous les sites du club (et autres sites).

L'application met à jour automatiquement les données plusieurs fois par jour pour vous offrir les prévisions les plus fiables.

## Comment accéder au site?

1. Ouvrez votre navigateur
2. Allez à: **http://localhost:8000** (en local) ou l'adresse fournie par le club
3. Vous voyez un tableau avec tous les sites de vol et les conditions par jour

## Les trois sections du site

### 1. En-tête avec logo et titre
Affiche le logo du club et le titre "Météo des sites du Nord pas de calais"

### 2. Heure de la dernière mise à jour
Affiche "Dernier run: 05-04-2026 14:30" pour savoir la fraîcheur des données

### 3. Le grand tableau météo
C'est le cœur de l'application. Chaque ligne = un site, chaque colonne = un jour

## Comment utiliser les filtres?

### Accéder aux filtres
Cliquez sur l'**icône  ⚙️ (engrenage)** en haut à gauche pour ouvrir/fermer le panneau des filtres

### Les 3 types de filtres

#### 1️⃣ Localisation
- **Nord**: Sites du Nord-Pas-de-Calais
- **Autre**: Sites dans d'autres régions

Vous pouvez sélectionner les deux pour voir tous les sites.

#### 2️⃣ Type de site
- **Bord de mer**: Sites côtiers (Equihen, Blériot, etc.)
- **Plaine**: Sites en campagne (Olhain, Auchy-les-Mines, etc.)
- **Treuil**: Sites avec système de treuil

Sélectionnez le ou les types qui vous intéressent. Vous pouvez en combiner plusieurs.

#### 3️⃣ Trier par jour
- **Lun, Mar, Mer, Jeu, Ven, Sam, Dim**: Les 7 jours de la semaine

Cliquez sur les jours pour les sélectionner. L'application trie les sites en montrant ceux avec les meilleures conditions sur les jours choisis.

**Par défaut** (sans filtre), les 3 prochains jours à partir d'aujourd'hui sont automatiquement sélectionnés.

### Appliquer les filtres
Une fois vos sélections faites, cliquez sur le bouton bleu **"Valider"** pour appliquer.

## Comprendre le tableau météo

### La colonne de gauche (En-têtes des sites)
Pour chaque site, vous voyez:
- **Nom du site** (cliquable, lien vers la description)
- **Plage de vent conseillée** (ex: "13 à 23 km/h")
- **Directions de vent favorables** (ex: "O, OSO, SO")
- **Distance du club** (ex: "140km, 2h")

### Les colonnes de jours
Chaque colonne affiche un jour:
- **Jour et date** (ex: "mer 5 avr")
- **Horaires**: 9h, 12h, 15h

### Les cellules de données

Pour chaque créneau horaire, vous voyez **3 lignes**:

**Ligne 1 - Vitesse minimale du vent**
- **Vert** = vitesse correcte ✓
- **Rouge** = vent trop fort ✗
- **Gris** = mauvaise direction

**Ligne 2 - Vitesse maximale du vent**
- Même code couleur que la ligne 1

**Ligne 3 - Direction du vent**
- **Vert** = bonne direction (dans la plage recommandée)
- **Orange** = mauvaise direction

### Les infos additionnelles (sous le tableau des vents)

- **Météo**: Illustration et description textuelle
- **Pluie**: "0mm" (noir) = pas de pluie | "5-10mm" (bleu) = risque de pluie
- **Soleil**: Heures d'ensoleillement avec couleur:
  - Noir = 0-1h
  - Jaune = 2-3h
  - Orange = 4-6h
  - Rouge = 7+ heures
- **Température**: Plage min~max en °C
- **Marées** (sites côtiers seulement): Heures de pleines mers et coefficient

### Cas spéciaux

**"Fermé"** → Le site est fermé ce jour-là (fermeture hivernale, etc.)

## Exemple de lecture

```
Equihen | Lundi 5 avril | Mardi 6 avril
        | 9h  12h  15h  | 9h  12h  15h
```

**Lundi 5 avril à 12h:**
- Min: 15 km/h (vert) ✓
- Max: 18 km/h (vert) ✓ 
- Dir: O (vert) ✓
→ **CONDITIONS IDÉALES pour Equihen à 12h lundi**

**Lundi 5 avril à 15h:**
- Min: 17 km/h (vert) ✓
- Max: 22 km/h (vert) ✓
- Dir: OSO (orange) - mauvaise direction
→ **Vent correct mais pas la bonne direction**

## Liens utiles pour chaque site

### Cliquer sur un site pour voir sa fiche complète

En bas du site, vous trouverez:
- **Vent conseillé**: La plage optimale
- **Directions du vent**: Les bonnes directions détaillées
- **Description**: Consignes de sécurité et règles spécifiques
- **Distance**: Distance depuis le club
- **Géolocalisation**: Coordonnées GPS (pour GPS/appli)
- **Links utiles**:
  - **FFVL**: Lien vers la base FFVL officielle
  - **Balise**: Lien vers les données de balise météo
  - **Météblue**: Lien vers la prévision complète MeteoBlue
  - **YouTube**: Vidéos/tutoriels du site

## Comment choisir où aller voler?

### Stratégie 1: Chercher le meilleur jour
1. Ouvrez les filtres (⚙️)
2. Sélectionnez le jour qui vous intéresse (ex: "Sam")
3. Validez
4. Les sites sont classés du meilleur au moins bon pour ce jour

### Stratégie 2: Chercher le meilleur week-end
1. Sélectionnez "Sam" et "Dim"
2. Validez
3. Les sites avec les meilleures conditions combinées ce week-end s'affichent en priorité

### Stratégie 3: Chercher des sites spécifiques près de chez vous
1. Sélectionnez votre région (Nord/Autre)
2. Sélectionnez le type que vous préférez (Bord de mer/Plaine/Treuil)
3. Sélectionnez le jour
4. Validez

### Stratégie 4: Accès direct par URL
Vous pouvez mettre les paramètres directement dans l'adresse:

```
?localisation=nord&type=bord-de-mer&days=sam,dim
?localisation=autre&type=plaine&days=ven
?type=treuil&days=lun
```

## Interprétation des données

### Vert = Volable ✓
La vitesse et direction du vent sont dans les paramètres recommandés pour ce site.

### Rouge = Trop de vent ✗
La vitesse maximum dépasse celle recommandée. **Trop dangereux?** Attendre un jour mieux.

### Orange / Gris = Mauvaise direction ⚠️
Le vent souffle d'une direction qui n'est pas idéale pour ce site. Risque de turbulences ou décollage difficile.

## Conseil de sécurité ⚠️

**Attention**: Ces données sont des prévisions. Avant de voler:
1. Consultez la balise météo du jour (lien dans la fiche site)
2. Regardez les vidéos YouTube du site (voir comment c'est)
3. Consultez un local qui connaît bien le site
4. Vérifiez les conditions réelles sur place
5. Respectez les règles de sécurité du site

## FAQ

**Q: Pourquoi certains sites affichent "Fermé"?**
A: Quelques sites sont fermés hivernalement (novembre-janvier) ou pour maintenance.

**Q: Comment je peux filtrer par plusieurs régions à la fois?**
A: Sélectionnez "Nord" ET "Autre", puis validez.

**Q: Puis-je savoir le score/classement exact d'un site?**
A: L'ordre de présentation dans le tableau est le classement. Le premier est le meilleur pour les jours sélectionnés.

**Q: Pourquoi les données ne s'actualisent pas?**
A: L'application se met à jour plusieurs fois par jour. Si vous avez une vieille version en cache, rafraîchissez (F5 ou Ctrl+R).

**Q: Comment interpréter les marées?**
A: Pour les sites côtiers, vérifiez l'impact de la marée - certains sites demandent marée basse/haute selon la configuration.

**Q: Je peux partager une URL filtrée?**
A: Oui! L'URL contient tous vos filtres. Copiez-la et partagez avec vos amis du club.

## Besoin d'aide?

Contactez l'administrateur du site ou consultez la documentation développeur pour des questions techniques.

Bon vol! 🪂
