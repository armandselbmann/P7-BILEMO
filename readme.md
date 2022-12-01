# Projet 7 OpenClassrooms - Créez un web service exposant une API

### Parcours Développeur d'application - PHP / Symfony
**Armand Selbmann**

---
### Code quality
The code has benn verified by Codacy.<br>
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/1a297405438d4830914247f92a0dd6ca)](https://www.codacy.com/gh/armandselbmann/P7-BILEMO/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=armandselbmann/P7-BILEMO&amp;utm_campaign=Badge_Grade)
---
## Online demo

---
## Description
BileMo API is webservice exposing an API. </br>

Access is restricted to customers, employees and administrators of Bilemo, by Json Web Token authentication.

Here is the overall use case diagram of the API which lists the different possibilities of use.
![](diagrams/Global_UseCase_EN.jpg)

Here is a document presenting the different endpoints and possible requests via the API.
![](public/doc/EndPoints_documentation.png)

After setup and configuration of the project, you will be able to access online documentation containing all the routes while also providing you with a tool to test them.

---
## Setup project
### Clone the Repository
SSH :
```
git clone git@github.com:armandselbmann/P7-BILEMO.git
```
HTTPS :
```
git clone https://github.com/armandselbmann/P7-BILEMO.git
```

### Technical requirements
PHP 8.1.7 or above </br>
Composer </br>
[Symfony CLI](https://symfony.com/download) </br>

### Download Composer dependencies
Make sure you have [Composer installed](https://getcomposer.org/download/)
and then run:

```
composer install
```

You can run this command to check wether you are missing any extensions or not.
```
symfony check:requirements
```
Proceed to the next step if your system is ready to run Symfony projects.

## Database setup
### Configuring Database access
At the root of the project, you need to update the .env file to configure the access to the Database.</br>
You must override this following variable :
```
DATABASE_URL="mysql://root:root@127.0.0.1:3306/BILEMOP7?serverVersion=8&charset=utf8mb4"
``` 

### Creating Database and insertion of the data
Creating the database :
```
php bin/console doctrine:database:create
```
Creating the Database Tables/Schema :
```
php bin/console doctrine:migrations:migrate
```
Loading initial data :
```
php bin/console doctrine:fixtures:load
```

## Implementation of the authentication system via Json web token
Create jwt folder in config
```
mkdir -p config/jwt
```
Generate the private and public SSL keys
```
php bin/console lexik:jwt:generate-keypair
```
Configure the SSL keys path and passphrase in your .env
```
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=
```
If you encounter a problem, do not hesitate to refer to the [documentation](https://symfony.com/bundles/LexikJWTAuthenticationBundle/current/index.html#configuration).

---
## Launch the WebServer
```
symfony serve
```
---
## Documentation
The documentation is available at `/api/doc`.

---
## Default account credentials

Here are the roles assigned to the different users and their credentials in order to test the API : </br>

Customer (Client) : CLIENT </br>
Employee (Employé) : ADMIN </br>
Bilemo's Administrator (Administrateur) : SUPER_ADMIN

```
Client :
username : customer1@gmail.com
password : password1
------------------------------------
Employee :
username : employee@bilemo.com
password : password
------------------------------------
Bilemo's Administrator :
username : bilemo@bilemo.com
password : bilemo
```