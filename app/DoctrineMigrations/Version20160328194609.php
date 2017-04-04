<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160328194609 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE invoice ADD COLUMN customer INT NOT NULL');
        $this->addSql('CREATE TABLE customer(
            id SERIAL NOT NULL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            street_name VARCHAR(255) NOT NULL,
            post_code VARCHAR(10) NOT NULL,
            city VARCHAR(255) NOT NULL,
            email VARCHAR(255) DEFAULT NULL
        )');

        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT invoice_customers FOREIGN KEY (customer) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT invoice_customers');
        $this->addSql('DROP TABLE customer');
    }
}
