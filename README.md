# invoicing

Invoicing  is a invoicing system that's easy to use and simple. 

It's intended for small companies and it doesn't have many special features but it should be quite good and simple for that since as an small business you'll would more likely use your time to something more valuable than invoicing bullshit.

## System requirements

- A web server (Nginx or apache for example)
- PHP 5.6
- PostgreSQL 9.4

## Development

The Invoicing is built using Symfony3 and Angular2, but all of this might change in the future.
 
### Basic structure

System is created using Angular 2 and Symfony 3 in the backend.

Frontend code is in the `frontend` folder.

Symfony is built with the Standard edition structure.

## Installation

1. Download sources from Github.
2. Create a database in PostgreSQL
3. Run `composer install` and answer to the questions the installation script asks. This creates automatically the required configuration to run the application
4. Run database migrations using the command `php bin/console doctrine:migrations:migrate`.
5. Point your webserver to the `web/` folder (look at the example nginx configuration `doc/nginx.example.conf`)
6. Copy `src/Invoicing/Bundle/AppBundle/Resources/invoice/default-settings.example.yml` to `src/Invoicing/Bundle/AppBundle/Resources/invoice/default-settings.yml` and modify settings accordingly.
7. Copy `src/Invoicing/Bundle/AppBundle/Resources/views/invoice/invoice-template-example.html` to `src/Invoicing/Bundle/AppBundle/Resources/views/invoice/invoice-template.html` and modify according to your needs. 
8. Initialize the frontend by running `npm install` and `npm start`
9. Application is ready to use.
