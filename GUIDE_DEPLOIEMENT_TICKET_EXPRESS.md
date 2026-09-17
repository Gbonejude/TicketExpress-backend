# 🚀 Guide Complet : Déploiement Continu GitHub Actions — Ticket Express

Ce guide pas à pas détaille tout le processus pour configurer un déploiement continu sécurisé via **GitHub Actions** pour le projet **Ticket Express**, avec une clé SSH dédiée, les vérifications préalables, les variables d'environnement (.env), et le workflow de déploiement.

---

## 📋 Table des matières

1. [Génération de la clé SSH dédiée (`ticket_express_deploy`)](#1-génération-de-la-clé-ssh-dédiée-ticket_express_deploy)
2. [Installation de la clé publique sur le serveur](#2-installation-de-la-clé-publique-sur-le-serveur)
3. [Vérification préalable de la connexion SSH (Étape obligatoire)](#3-vérification-préalable-de-la-connexion-ssh-étape-obligatoire)
4. [Configuration des Secrets dans GitHub](#4-configuration-des-secrets-dans-github)
5. [Variables du fichier `.env` sur le serveur (Laravel)](#5-variables-du-fichier-env-sur-le-serveur-laravel)
6. [Fichier de déploiement GitHub Actions (`deploy.yml`)](#6-fichier-de-déploiement-github-actions-deployyml)
7. [Dépannage des erreurs fréquentes](#7-dépannage-des-erreurs-fréquentes)

---

## 🔑 1. Génération de la clé SSH dédiée (`ticket_express_deploy`)

La clé doit être générée sur la machine de votre ami (ou la vôtre). Elle portera le nom spécifique **`ticket_express_deploy`**.

> **Important** : Pour un déploiement automatisé (CI/CD), la clé **ne doit pas avoir de mot de passe (passphrase)**, sinon le script bloquera en attendant une saisie humaine.

### Option A : Sur Windows (PowerShell ou Invite de commandes)

```powershell
# 1. Créer le dossier .ssh s'il n'existe pas et s'y déplacer
if (!(Test-Path "$HOME\.ssh")) { New-Item -ItemType Directory -Path "$HOME\.ssh" }
cd "$HOME\.ssh"

# 2. Générer la paire de clés (Algorithme ed25519 moderne et sécurisé)
ssh-keygen -t ed25519 -C "deploy-ticket-express" -f "ticket_express_deploy"
```

Lors de l'exécution :
- **Enter passphrase (empty for no passphrase):** Appuyez sur **ENTRÉE** (laissez vide).
- **Enter same passphrase again:** Appuyez sur **ENTRÉE** (laissez vide).

### Option B : Sur Linux / macOS

```bash
mkdir -p ~/.ssh
cd ~/.ssh
ssh-keygen -t ed25519 -C "deploy-ticket-express" -f "ticket_express_deploy"
# Appuyez sur ENTRÉE deux fois (sans mot de passe)
```

### Résultat obtenu
Deux fichiers sont créés dans le dossier `.ssh` :
- `ticket_express_deploy` : **Clé privée** (À GARDER STRICTEMENT SECRÈTE — ira dans les Secrets GitHub).
- `ticket_express_deploy.pub` : **Clé publique** (À copier sur le serveur distant).

---

## 📤 2. Installation de la clé publique sur le serveur

### Étape 2.1 : Afficher et copier la clé publique

**Sur Windows (PowerShell) :**
```powershell
Get-Content "$HOME\.ssh\ticket_express_deploy.pub"
```

**Sur Linux / Mac :**
```bash
cat ~/.ssh/ticket_express_deploy.pub
```

Le contenu ressemble à :
```text
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAI... deploy-ticket-express
```
👉 **Copiez l'intégralité de cette ligne.**

---

### Étape 2.2 : Se connecter au serveur et ajouter la clé

Connectez-vous au serveur (avec mot de passe cette fois) :

```bash
ssh -p 6543 sunofamap@209.142.65.178
```

Une fois connecté sur le serveur, exécutez ces commandes :

```bash
# 1. Créer le dossier .ssh s'il n'existe pas encore
mkdir -p ~/.ssh

# 2. Ajuster les permissions du dossier .ssh (OBLIGATOIRE : chmod 700)
chmod 700 ~/.ssh

# 3. Ajouter la clé publique au fichier authorized_keys
# (Remplacez la ligne ci-dessous par VOTRE vraie clé publique copiée)
echo "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAI... deploy-ticket-express" >> ~/.ssh/authorized_keys

# 4. Ajuster les permissions du fichier authorized_keys (OBLIGATOIRE : chmod 600)
chmod 600 ~/.ssh/authorized_keys

# 5. Vérifier que la clé est bien ajoutée
tail -n 1 ~/.ssh/authorized_keys
```

---

### Étape 2.3 : Préparer le dossier du projet sur le serveur

Toujours sur le serveur, assurez-vous que le dossier de Ticket Express existe :

```bash
cd /home/sunofamap

# Si le projet n'est pas encore cloné sur le serveur :
git clone https://github.com/VOTRE_ORGANISATION/VOTRE_REPO.git ticket-express
cd ticket-express
git checkout develop

# Ou si le dossier existe déjà :
cd ticket-express
git status
```

Tapez ensuite `exit` pour vous déconnecter du serveur.

---

## 🧪 3. Vérification préalable de la connexion SSH (Étape obligatoire)

Avant de toucher à GitHub, **vérifiez que la connexion fonctionne avec la nouvelle clé depuis votre machine locale** :

```bash
# Commande de test (sur Windows PowerShell ou Linux/Mac) :
ssh -i ~/.ssh/ticket_express_deploy -p 6543 sunofamap@209.142.65.178 "echo 'Connexion SSH Ticket Express OK !'"
```

*(Sous Windows si le chemin `~/.ssh` n'est pas reconnu : utiliser `$HOME\.ssh\ticket_express_deploy`)*

#### ✅ Résultat attendu :
Le terminal affiche :
```text
Connexion SSH Ticket Express OK !
```
**Sans vous demander de mot de passe.**

> **En cas de refus :**
> - Vérifiez que les permissions sur le serveur sont bien respectées (`chmod 700 ~/.ssh` et `chmod 600 ~/.ssh/authorized_keys`).
> - Vérifiez que la clé dans `authorized_keys` est sur une seule ligne continue.

---

## ⚙️ 4. Configuration des Secrets dans GitHub

Dans le dépôt GitHub du projet **Ticket Express** :
1. Allez dans l'onglet **Settings** (Paramètres du projet).
2. Dans le menu de gauche : **Secrets and variables** > **Actions**.
3. Cliquez sur le bouton vert **New repository secret**.

Ajoutez les secrets suivants :

| Nom du Secret | Valeur | Description |
| :--- | :--- | :--- |
| `SSH_PRIVATE_KEY` | Contenu intégral de `ticket_express_deploy` | Clé privée SSH (du `BEGIN` au `END`) |
| `SSH_HOST` | `209.142.65.178` | Adresse IP du serveur |
| `SSH_USER` | `sunofamap` | Utilisateur SSH |
| `SSH_PORT` | `6543` | Port SSH personnalisé |
| `DEPLOY_PATH` | `ticket-express` *(ou nom exact du dossier)* | Chemin ou nom du dossier sur le serveur |

### Pour afficher la clé privée à copier :
- **Windows (PowerShell) :** `Get-Content "$HOME\.ssh\ticket_express_deploy" -Raw`
- **Linux/Mac :** `cat ~/.ssh/ticket_express_deploy`

---

## 📄 5. Variables du fichier `.env` sur le serveur (Laravel)

> ⚠️ **Sécurité** : Le fichier `.env` **ne doit jamais être envoyé sur GitHub**. Il doit être créé directement sur le serveur dans le dossier `/home/sunofamap/ticket-express/.env`.

### Créer le fichier sur le serveur :
```bash
ssh -p 6543 sunofamap@209.142.65.178
cd /home/sunofamap/ticket-express
nano .env
```

### Contenu recommandé du fichier `.env` pour Ticket Express :

```dotenv
# ==============================================================================
# CONFIGURATION GÉNÉRALE DE L'APPLICATION
# ==============================================================================
APP_NAME="Ticket Express"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://ticket.canaanapp.com
APP_TIMEZONE=UTC
APP_LOCALE=fr
APP_FALLBACK_LOCALE=en

# ==============================================================================
# LOGS
# ==============================================================================
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# ==============================================================================
# BASE DE DONNÉES (MySQL)
# ==============================================================================
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nom_de_votre_base_ticket_express
DB_USERNAME=utilisateur_base
DB_PASSWORD=mot_de_passe_securise

# ==============================================================================
# SESSIONS, CACHE ET QUEUES
# ==============================================================================
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# ==============================================================================
# CONFIGURATION EMAIL (SMTP)
# ==============================================================================
MAIL_MAILER=smtp
MAIL_HOST=mail.canaanapp.com
MAIL_PORT=465
MAIL_USERNAME=noreply@canaanapp.com
MAIL_PASSWORD=mot_de_passe_mail
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="noreply@canaanapp.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Initialiser la clé Laravel et les permissions :
Exécutez ces commandes une seule fois sur le serveur dans le dossier du projet :
```bash
# Générer la clé d'application sécurisée
scl enable php82 -- php artisan key:generate

# Donner les permissions d'écriture indispensables à Laravel
chmod -R 775 storage bootstrap/cache
```

---

## ⚙️ 6. Fichier de déploiement GitHub Actions (`deploy.yml`)

Créez ce fichier dans votre dépôt local :
📁 `.github/workflows/deploy.yml`

```yaml
name: Déploiement Continu (Ticket Express)

on:
  push:
    branches:
      - develop

concurrency:
  group: deploy-ticket-express
  cancel-in-progress: false

jobs:
  deploy:
    name: Déploiement vers Production
    runs-on: ubuntu-latest

    steps:
      - name: 📥 Récupération du code
        uses: actions/checkout@v4

      - name: 🔑 Configuration de la clé SSH
        env:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
          SSH_HOST: ${{ secrets.SSH_HOST }}
          SSH_PORT: ${{ secrets.SSH_PORT }}
        run: |
          mkdir -p ~/.ssh
          chmod 700 ~/.ssh

          # Écriture de la clé privée avec le nom personnalisé ticket_express_deploy
          echo "$SSH_PRIVATE_KEY" > ~/.ssh/ticket_express_deploy
          chmod 600 ~/.ssh/ticket_express_deploy

          # Ajout du serveur aux hôtes connus pour éviter l'invite interactive
          ssh-keyscan -p "$SSH_PORT" "$SSH_HOST" >> ~/.ssh/known_hosts
          chmod 644 ~/.ssh/known_hosts

      - name: 🚀 Exécution des commandes de déploiement
        env:
          SSH_HOST: ${{ secrets.SSH_HOST }}
          SSH_USER: ${{ secrets.SSH_USER }}
          SSH_PORT: ${{ secrets.SSH_PORT }}
          DEPLOY_PATH: ${{ secrets.DEPLOY_PATH }}
        run: |
          ssh -i ~/.ssh/ticket_express_deploy -p "$SSH_PORT" "$SSH_USER@$SSH_HOST" "
            set -e

            echo '=== 1. Accès au dossier du projet ==='
            cd $DEPLOY_PATH

            echo '=== 2. Nettoyage et mise à jour Git ==='
            git reset --hard HEAD
            # Protéger .env et .htaccess du nettoyage
            git clean -df -e .htaccess -e public/.htaccess -e .env
            rm -f bootstrap/cache/*.php
            git pull origin develop

            echo '=== 3. Installation des dépendances Composer ==='
            scl enable php82 -- composer install --no-dev --optimize-autoloader --no-interaction

            echo '=== 4. Migration de la base de données ==='
            scl enable php82 -- php artisan migrate --force

            echo '=== 5. Optimisation du cache Laravel ==='
            scl enable php82 -- php artisan cache:clear
            scl enable php82 -- php artisan config:clear
            scl enable php82 -- php artisan view:clear
            scl enable php82 -- php artisan config:cache
            scl enable php82 -- php artisan route:cache
            scl enable php82 -- php artisan view:cache

            echo '=== 6. Droits sur les dossiers ==='
            chmod -R 775 storage bootstrap/cache

            echo '✅ Déploiement de Ticket Express terminé avec succès !'
          "

      - name: 🧹 Nettoyage de la clé
        if: always()
        run: |
          rm -f ~/.ssh/ticket_express_deploy
```

---

## 🚨 7. Dépannage des erreurs fréquentes

| Erreur | Cause probable | Solution |
| :--- | :--- | :--- |
| `Permission denied (publickey)` | La clé publique n'est pas dans `authorized_keys` ou mauvais droits | Vérifier avec `tail ~/.ssh/authorized_keys` et faire `chmod 700 ~/.ssh && chmod 600 ~/.ssh/authorized_keys` |
| `Host key verification failed` | Le port ou l'IP ne correspond pas dans `ssh-keyscan` | Vérifier que le port `6543` est bien spécifié : `ssh-keyscan -p 6543 209.142.65.178` |
| `scl: command not found` | Le serveur n'utilise pas Software Collections (SCL) | Remplacer `scl enable php82 -- ...` directement par `php ...` |
| `Your local changes to the following files would be overwritten` | Fichiers locaux modifiés sur le serveur | La commande `git clean -df -e .env -e .htaccess` et `git reset --hard HEAD` nettoie l'arbre de travail sans effacer votre `.env` |
