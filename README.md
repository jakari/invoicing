# invoicing

Invoicing  is a invoicing system that's easy to use and simple. 

It's intended for small companies and it doesn't have many special features but it should be quite good and simple for that since as an small business you'll would more likely use your time to something more valuable than invoicing bullshit.

## System requirements

- A web server (Nginx or apache for example)
- PHP 5.6
- PostgreSQL 9.4

## Development

The Invoicing is built using Symfony3 and React, but all of this might change in the future.
 
### Basic structure

Frontend code is in the `client` folder.

Symfony is built with the Standard edition structure.

## Usage

To use the application, first install it according to the guide below, and add a user to be able to login. You should also add a company into the database to be able to use it.

## Installation

1. Download sources from Github.
2. Create a database in PostgreSQL
3. Run `composer install` and answer to the questions the installation script asks. This creates automatically the required configuration to run the application
4. Run database migrations using the command `php bin/console doctrine:migrations:migrate`.
5. Point your webserver to the `web/` folder (look at the example nginx configuration `doc/nginx.example.conf`)
6. Copy `src/Invoicing/Bundle/AppBundle/Resources/invoice/default-settings.example.yml` to `src/Invoicing/Bundle/AppBundle/Resources/invoice/default-settings.yml` and modify settings accordingly.
7. Copy `src/Invoicing/Bundle/AppBundle/Resources/views/invoice/invoice-template-example.html` to `src/Invoicing/Bundle/AppBundle/Resources/views/invoice/invoice-template.html` and modify according to your needs. 
8. Initialize the frontend by running `npm install` and `npm start` in the `client` folder.
9. Add a user using the command below.
10. Add a company to the database: `INSERT INTO company(name) VALUES('Yritys Oy');`
11. Application is ready to use.

### Adding a user

To add a user to the application, run `php bin/console user:create`. The guide will ask for username and password. After user creation you may log in.

### Deployment in dokku

Run `git push [environment]` to push to the desired environment. 
Applications are ran as `herokuishuser`, so if you need to run migration or other commands on the server do the following:

1. SSH to server
2. Run `dokku enter [applicationname]`
3. Run `su -u herokuishuser` to become herokuishuser
4. Run your desired commands (for example `php bin/console doctrine:migrations:migrate`)

#### Postgres management

In order to manage the postgres instance, do the following:

1. `dokku postgres:enter [applicationname]`
2. `psql -U postgres`

