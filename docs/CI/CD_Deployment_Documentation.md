Laravel CI/CD Deployment Documentation

Project: FoodHub
Technology: Laravel, PHP 8.5, MySQL, GitHub Actions, AWS EC2
Deployment Branch: development
EC2 Application Path: /var/www/foodhub

1. Deployment Architecture

Our application is deployed automatically from GitHub to AWS EC2 using GitHub Actions.

Deployment Flow
Developer pushes code to the development branch.
GitHub Actions workflow is triggered.
GitHub Actions connects to EC2 using SSH.
RSYNC uploads application files to EC2.
Laravel directories are prepared.
Composer installs PHP dependencies.
Laravel database migrations execute.
Laravel caches are cleared.
Storage symlink is created if missing.
Laravel configuration and view caches are generated.
Architecture
text
Developer
    |
    | git push origin development
    |
    v
GitHub Repository
    |
    v
GitHub Actions
    |
    | SSH Authentication
    | RSYNC Deployment
    |
    v
AWS EC2 Instance
    |
    v
/var/www/foodhub
    |
    | Composer Install
    | Artisan Migrate
    | Cache Optimization
    |
    v
Laravel Application
    |
    v
MySQL Database

2. GitHub Repository Configuration
Branch

The deployment branch is:

text
development

Workflow File

Location:

text
.github/workflows/deploy.yml


The workflow runs automatically when code is pushed to the development branch.

It can also be triggered manually using workflow_dispatch.

GitHub Actions Configuration
yaml
name: Laravel Development CI/CD

on:
  push:
    branches:
      - development

  workflow_dispatch:

permissions:
  contents: read

concurrency:
  group: laravel-development-deploy
  cancel-in-progress: false

Explanation
Configuration	Purpose
push	Automatically deploy on push
development	Deployment branch
workflow_dispatch	Manual deployment
contents: read	Read repository contents
concurrency	Prevent overlapping deployments
3. GitHub Secrets Configuration

The following secrets are configured in:

GitHub Repository → Settings → Secrets and variables → Actions

Secret	Purpose
EC2_HOST	EC2 Public IPv4 address
EC2_USER	EC2 SSH username (ubuntu)
EC2_SSH_KEY	Private SSH key
EC2_KNOWN_HOSTS	SSH server host key
Important

Database credentials and Laravel APP_KEY are configured directly in the EC2 .env file.

They are not transferred through GitHub Actions.

4. RSYNC Deployment Configuration

The application is uploaded to:

text
/var/www/foodhub/


RSYNC is used to transfer files efficiently.

Excluded Directories and Files
bash
--exclude='.git/' \
--exclude='.github/' \
--exclude='.env' \
--exclude='vendor/' \
--exclude='node_modules/' \
--exclude='storage/' \
--exclude='bootstrap/cache/' \

Why These Are Excluded
File/Directory	Reason
.git/	Git history is not required on EC2
.github/	Workflow configuration is not required on EC2
.env	Preserve production environment configuration
vendor/	Dependencies are installed on EC2
node_modules/	Avoid uploading local Node dependencies
storage/	Preserve production storage and runtime files
bootstrap/cache/	Preserve runtime cache directory

Note: .env.example should not be excluded. Remove any broad .env.* exclusion if it is still present in the actual workflow.

5. EC2 Server Configuration
EC2 Application Directory
text
/var/www/foodhub


The application is deployed inside this directory.

Operating System User

The default EC2 deployment user is:

text
ubuntu


This user is responsible for:

Connecting to EC2 using SSH.
Uploading application files.
Running Composer commands.
Running Laravel Artisan commands.
Deploying application updates.
Web Server User

The web server/PHP-FPM user is:

text
www-data


PHP-FPM runs using this user to execute Laravel application code.

PHP Version

Installed PHP-FPM service:

text
php8.5-fpm


Restart command:

bash
sudo systemctl restart php8.5-fpm

6. Dedicated Laravel Group Configuration

To avoid permission conflicts between the deployment user and PHP-FPM user, we created a dedicated Linux group.

Group Name
text
laravel

Users Added to the Group
text
ubuntu
www-data


Both users belong to the shared laravel group.

Commands Used

Create the group:

bash
sudo groupadd laravel


Add Ubuntu user:

bash
sudo usermod -aG laravel ubuntu


Add PHP-FPM user:

bash
sudo usermod -aG laravel www-data

Verify Group Membership
bash
id ubuntu

bash
id www-data


Expected group membership:

text
ubuntu:
ubuntu, laravel

www-data:
www-data, laravel

Why Did We Create This Group?

Without a shared group:

Ubuntu creates files owned by Ubuntu.
PHP-FPM creates files owned by www-data.
One user may not have sufficient permissions to modify the other user's files.

The shared group provides a common access mechanism for Laravel runtime directories.

7. Laravel Directory Ownership and Permissions

We configured shared group ownership for two important directories.

Directory 1: Storage
text
/var/www/foodhub/storage


This directory contains:

Laravel logs
Compiled Blade views
Application cache
Sessions
Runtime files
Directory 2: Bootstrap Cache
text
/var/www/foodhub/bootstrap/cache


This directory contains Laravel's generated bootstrap and package cache files.

Set Directory Ownership
bash
sudo chown -R ubuntu:laravel /var/www/foodhub/storage

sudo chown -R ubuntu:laravel /var/www/foodhub/bootstrap/cache

Set Directory Permissions
bash
sudo find /var/www/foodhub/storage \
/var/www/foodhub/bootstrap/cache \
-type d -exec chmod 2775 {} \;

Set File Permissions
bash
sudo find /var/www/foodhub/storage \
/var/www/foodhub/bootstrap/cache \
-type f -exec chmod 664 {} \;

Permission Explanation
Permission	Purpose
2	Setgid bit on directories
7	Owner read/write/execute
7	Group read/write/execute
5	Others read/execute
664	Owner and group read/write; others read-only
Why Setgid?

We enabled setgid on directories so newly created files and directories inherit the parent directory's group.

This helps maintain shared group ownership between Ubuntu and PHP-FPM.

Expected Ownership
text
Owner: ubuntu
Group: laravel

Expected Directory Permission
text
drwxrwsr-x

8. PHP-FPM Group Membership Refresh

After adding www-data to the laravel group, we restarted PHP-FPM.

Command:

bash
sudo systemctl restart php8.5-fpm


Why?

PHP-FPM processes need to be restarted to pick up the updated supplementary group membership.

The Ubuntu SSH session also needs to be refreshed after changing its group membership.

Logout:

bash
exit


Reconnect to EC2 and verify:

bash
id ubuntu

9. Permission Testing

We tested whether PHP-FPM's user could create files inside Laravel's views directory.

Command:

bash
sudo -u www-data touch \
/var/www/foodhub/storage/framework/views/test.txt


Verify:

bash
ls -l /var/www/foodhub/storage/framework/views/test.txt


Delete the test file:

bash
sudo rm \
/var/www/foodhub/storage/framework/views/test.txt


This confirmed that www-data could create files in the directory.

10. Laravel Environment Configuration

The production .env file is maintained directly on EC2.

Location:

text
/var/www/foodhub/.env


Create the environment file:

bash
cd /var/www/foodhub

cp .env.example .env


Edit:

bash
nano .env

Database Configuration
env
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=food_hub
DB_USERNAME=your_username
DB_PASSWORD=your_password

Generate Laravel Application Key
bash
php artisan key:generate


This generates the Laravel APP_KEY and writes it to .env.

Important: Never regenerate APP_KEY during normal deployments. Preserve the existing production key.

Environment File Permissions
bash
sudo chown ubuntu:www-data .env

sudo chmod 640 .env


The .env file is excluded from RSYNC to protect production configuration.

11. GitHub Actions Deployment Steps

The workflow performs the following operations.

Step 1: Checkout Code
yaml
- name: Checkout Code
  uses: actions/checkout@v4


Downloads repository code.

Step 2: Install Dependencies
bash
sudo apt-get update
sudo apt-get install -y rsync openssh-client


Installs RSYNC and SSH client on the GitHub runner.

Step 3: Configure SSH

Creates:

text
~/.ssh/id_ed25519
~/.ssh/known_hosts


Uses GitHub Secrets for SSH authentication.

Step 4: Test EC2 Connection

Tests SSH connectivity before deployment.

Step 5: Upload Laravel Code

Uses RSYNC to upload the latest code to:

text
/var/www/foodhub/

Step 6: Prepare Laravel Directories

Creates missing runtime directories:

bash
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache


We do not recursively change ownership or permissions during every deployment because ownership and group permissions are configured separately on EC2.

Step 7: Composer Install
bash
composer install \
--no-interaction \
--prefer-dist \
--no-dev \
--optimize-autoloader


Installs production PHP dependencies.

Step 8: Database Migrations
bash
php artisan migrate --force


Runs pending Laravel database migrations against the MySQL database configured in .env.

Step 9: Clear Laravel Cache
bash
php artisan optimize:clear


Clears Laravel's optimized caches.

Step 10: Create Storage Link
bash
php artisan storage:link


Creates the public storage symlink if it does not already exist.

Step 11: Optimize Laravel
bash
php artisan config:cache
php artisan view:cache


Rebuilds Laravel configuration and Blade view caches.

Step 12: Deployment Success

Prints a success message after all preceding steps complete.

12. Important Deployment Commands
Check PHP Version
bash
php -v

Check PHP-FPM
bash
sudo systemctl status php8.5-fpm

Check MySQL
bash
sudo systemctl status mysql

Check Laravel Logs
bash
cd /var/www/foodhub

tail -n 50 storage/logs/laravel.log

Clear Laravel Caches
bash
php artisan optimize:clear

Rebuild Laravel Caches
bash
php artisan config:cache
php artisan view:cache

Check Directory Permissions
bash
ls -ld storage
ls -ld storage/framework
ls -ld storage/framework/views
ls -ld bootstrap/cache

Check Group Membership
bash
id ubuntu
id www-data

13. Important Security Notes
Never commit the production .env file to GitHub.
Never expose SSH private keys or database passwords.
Never use chmod 777 on Laravel application directories.
Keep APP_DEBUG=false in production.
Preserve the production APP_KEY.
Keep write permissions restricted to storage and bootstrap/cache where possible.
Take database backups before risky production migrations.
Avoid changing ownership of the entire application directory during every deployment.
Keep EC2 SSH access restricted to trusted IP addresses where practical.
14. Current Deployment Summary
Component	Configuration
Application	FoodHub
Framework	Laravel
PHP	8.5
Database	MySQL
Hosting	AWS EC2
Deployment	GitHub Actions
Deployment tool	RSYNC
Deployment branch	development
Deployment user	ubuntu
PHP-FPM user	www-data
Shared group	laravel
Application directory	/var/www/foodhub
Runtime directories	storage, bootstrap/cache
Environment file	/var/www/foodhub/.env
PHP-FPM service	php8.5-fpm

Deployment Status: GitHub Actions deployment, EC2 SSH connection, Composer installation, database migrations and Laravel application access have been configured and tested.

End of Documentation
