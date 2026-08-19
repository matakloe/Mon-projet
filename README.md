# Hôpital Central — Système de gestion hospitalière

Application web en **PHP + MySQL (PDO) + HTML/CSS**, avec gestion des patients,
médecins, rendez-vous et départements, incluant l'upload et l'affichage de photos.

## Fonctionnalités

- Connexion sécurisée du personnel (mot de passe hashé avec `password_hash`)
- **Patients** : liste, recherche, filtre par statut, fiche détaillée, ajout/modification
  avec **photo**, suppression, historique des rendez-vous
- **Médecins** : liste en cartes avec photo, ajout/modification/suppression, rattachement à un département
- **Rendez-vous** : planification patient/médecin/date/heure, liste, suppression
- **Départements** : création, liste, suppression
- Tableau de bord avec statistiques et derniers ajouts
- Requêtes préparées (PDO) contre les injections SQL, échappement HTML contre le XSS
- Upload de photos limité aux images (jpg/png/webp, 3 Mo max), dossier uploads protégé
  contre l'exécution de scripts (`.htaccess`)

## Installation (avec XAMPP )

1. Copiez le dossier `hopital/` dans le répertoire web de votre serveur
   (ex : `htdocs/hopital` pour XAMPP, `www/hopital` pour WAMP).
2. Démarrez Apache et MySQL depuis votre panneau de contrôle.
3. Ouvrez **phpMyAdmin**, créez la base en important directement le fichier
   `sql/hopital.sql` (il crée la base `hopital_db`, les tables et des données de démonstration) :
   - Onglet **Importer** → sélectionnez `sql/hopital.sql` → **Exécuter**.
   - Ou en ligne de commande : `mysql -u root -p < sql/hopital.sql`
4. Vérifiez les identifiants de connexion dans `config/database.php` :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'hopital_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
   Adaptez `DB_USER` / `DB_PASS` selon votre configuration MySQL.
5. Rendez les dossiers `uploads/patients` et `uploads/doctors` accessibles en écriture
   par le serveur web (sous Linux/Mac : `chmod -R 775 uploads`).
6. Ouvrez `http://localhost/hopital/` dans votre navigateur.



## Structure du projet

```
hopital/
├── config/
│   └── database.php        # Connexion PDO à MySQL
├── includes/
│   ├── auth.php             # Session, fonctions utilitaires, upload de photos
│   ├── header.php           # En-tête + barre latérale commune
│   └── footer.php
├── css/
│   └── style.css            # Charte graphique
├── sql/
│   └── hopital.sql          # Schéma + données de démonstration
├── uploads/
│   ├── patients/             # Photos des patients (généré à l'usage)
│   └── doctors/               # Photos des médecins (généré à l'usage)
├── login.php / logout.php
├── index.php                 # Tableau de bord
├── patients.php / patient_add.php / patient_edit.php / patient_view.php / patient_delete.php
├── doctors.php / doctor_add.php / doctor_edit.php / doctor_delete.php
├── appointments.php / appointment_delete.php
└── departments.php
```

## Notes de sécurité pour une mise en production

- Changez immédiatement le mot de passe administrateur par défaut.
- Servez le site en HTTPS.
- Limitez les droits du compte MySQL utilisé par l'application (évitez `root` en production).
- Sauvegardez régulièrement la base de données et le dossier `uploads/`.
