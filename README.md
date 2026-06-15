# 🦷 Cabinet Dentaire Management System

Application web professionnelle pour la gestion de cabinets dentaires développée avec Symfony 6.4.

## 📋 Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Stack Technique](#stack-technique)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Tests de paiement](#tests-de-paiement)
- [Configuration des emails](#configuration-des-emails)
- [Accès](#accès)
- [Licence](#licence)

## ✨ Fonctionnalités

- **Gestion des patients** : Inscription, profils, historique médical
- **Prise de rendez-vous** : Calendrier en ligne avec confirmations
- **Administration** : Tableau de bord, facturation, rapports
- **Paiements** : Intégration Stripe sécurisée
- **Documents** : Génération PDF pour prescriptions et factures
- **Sécurité** : Protection des données patients (RGPD)

## 🛠️ Stack Technique

- **Backend** : Symfony 6.4, Doctrine ORM, MySQL
- **Frontend** : Twig
- **Services** : Stripe (paiements), DomPDF (PDF), Mailer (emails)

## 📦 Prérequis

- PHP 8.2 ou supérieur
- Composer
- MySQL 8.0 ou supérieur
- XAMPP (pour Windows) ou équivalent

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone https://github.com/maissahamdi047-sys/cabinet-dentaire.git
cd cabinet-dentaire
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer l'environnement

```bash
cp .env .env.local
```

Éditer `.env.local` avec vos credentials (voir section [Configuration](#configuration)).

### 4. Créer la base de données

```bash
# Créer la base de données MySQL
mysql -u root -e "CREATE DATABASE IF NOT EXISTS cabinet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Créer le schema
php bin/console doctrine:schema:create

# Ou mettre à jour le schema
php bin/console doctrine:schema:update --force
```

### 5. Démarrer le serveur

```bash
php -S localhost:8000 -t public 
symfony server:start  
```

L'application sera accessible sur `http://localhost:8000`

## ⚙️ Configuration

Variables d'environnement requises dans `.env.local` :

```env
# Base de données
DATABASE_URL="mysql://root:@127.0.0.1:3306/cabinet?serverVersion=8.0.32&charset=utf8mb4"

# Stripe (optionnel, pour les paiements)
STRIPE_PUBLIC_KEY="pk_test_votre_cle_publique_stripe"
STRIPE_SECRET_KEY="sk_test_votre_cle_secrete_stripe"

# Mailer (optionnel, pour les emails)
MAILER_DSN="null://null"
# Pour utiliser Mailpit : MAILER_DSN="smtp://localhost:1025"
```



## 💳 Tests de paiement

Pour tester les paiements en développement avec Stripe :

**Carte de test (succès) :**
- Numéro : `4242 4242 4242 4242`
- Type : Visa
- CVV : `123`
- Date d'expiration : `12/25`

**Pour obtenir des clés Stripe :**
1. Créez un compte sur [Stripe](https://dashboard.stripe.com/register)
2. Allez dans [API Keys](https://dashboard.stripe.com/apikeys)
3. Copiez les clés de test (pk_test_... et sk_test_...)
4. Ajoutez-les dans votre fichier `.env.local`

## 📧 Configuration des emails

### Option 1 : Désactivé (développement)

```env
MAILER_DSN="null://null"
```

Les emails ne sont pas envoyés (configuration par défaut).

### Option 2 : Avec Mailpit (recommandé pour le développement)

1. **Télécharger Mailpit** : https://github.com/axllent/mailpit/releases
2. **Extraire le fichier ZIP** dans un dossier (ex: `C:\mailpit`)
3. **Démarrer Mailpit** :
   - Ouvrir l'invite de commandes
   - Naviguer vers le dossier : `cd C:\mailpit`
   - Exécuter : `mailpit.exe`
4. **Accéder à l'interface** :
   - Mailpit affichera un lien (généralement http://127.0.0.1:8025)
   - Cliquer sur ce lien pour ouvrir l'interface web
5. **Configurer le mailer** :
   ```env
   MAILER_DSN="smtp://localhost:1025"
   ```

Tous les emails envoyés par l'application apparaîtront dans l'interface Mailpit.

## 🌐 Accès

- **Site public** : `http://localhost:8000`
- **Administration** : `http://localhost:8000/admin`

## 📄 Licence

Propriétaire - Tous droits réservés

---

*Développé pour les professionnels de santé* 🏥