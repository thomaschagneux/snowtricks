# SnowTricks

SnowTricks est une plateforme communautaire dédiée aux passionnés de snowboard. Ce site permet aux utilisateurs de partager, découvrir et discuter des figures (tricks) de snowboard.

## Fonctionnalités

- Consultation des figures de snowboard avec descriptions, images et vidéos
- Création de compte utilisateur
- Ajout, modification et suppression de figures (pour les utilisateurs connectés)
- Commentaires sur les figures (pour les utilisateurs connectés)
- Gestion des médias (images et vidéos) pour illustrer les figures
- Catégorisation des figures par groupes

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- Symfony CLI
- MySQL ou MariaDB

## Installation

1. Cloner le dépôt
   ```bash
   https://github.com/thomaschagneux/snowtricks.git
   cd snowtricks
   ```

2. Configurer les variables d'environnement
   ```bash
   cp .env .env.local
   ```
   Puis modifiez le fichier `.env.local` pour configurer votre base de données et autres paramètres.

3. Installer les dépendances et configurer le projet
   ```bash
   make install
   ```
   Cette commande va:
   - Installer les dépendances PHP avec Composer
   - Créer la base de données si elle n'existe pas
   - Exécuter les migrations
   - Charger les fixtures (données de démonstration)

## Utilisation

### Démarrer le serveur

```bash
make start
```

Le site sera accessible à l'adresse: `https://localhost:8000`

### Arrêter le serveur

```bash
make stop
```

### Réinitialiser la base de données

```bash
make reset
```

### Nettoyer le cache

```bash
make clean
```

## Commandes utiles pour le développement

### Vérification de la qualité du code

```bash
make quality
```

Cette commande exécute:
- PHP-CS-Fixer pour corriger les problèmes de style de code
- PHPStan pour l'analyse statique du code

### Ajouter les modifications à Git après vérification de qualité

```bash
make add
```

## Technologies utilisées

- **Symfony 7.2**: Framework PHP
- **Doctrine ORM**: Couche d'abstraction de base de données
- **Twig**: Moteur de templates
- **Symfony Security**: Gestion de l'authentification et des autorisations
- **Symfony Forms**: Gestion des formulaires
- **Symfony Mailer**: Envoi d'emails
- **SymfonyCasts Reset Password Bundle**: Gestion de la réinitialisation des mots de passe

## Structure du projet

- `src/Controller/`: Contrôleurs de l'application
- `src/Entity/`: Entités Doctrine (modèles de données)
- `src/Form/`: Types de formulaires
- `src/Repository/`: Repositories Doctrine pour l'accès aux données
- `src/Service/`: Services métier
- `templates/`: Templates Twig
- `public/`: Fichiers publics (CSS, JS, images)
- `migrations/`: Migrations de base de données

## Contribution

Les contributions sont les bienvenues! N'hésitez pas à ouvrir une issue ou une pull request.

## Licence

Ce projet est sous licence propriétaire.
