<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160328193951 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE item(
            id SERIAL NOT NULL PRIMARY KEY,
            invoice INT NOT NULL,
            description VARCHAR(255) NOT NULL,
            amount INT NOT NULL,
            tax INT NOT NULL,
            price INT NOT NULL
        )');

        $this->addSql('ALTER TABLE item ADD CONSTRAINT invoice_items FOREIGN KEY (invoice) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE item DROP CONSTRAINT invoice_items');
        $this->addSql('DROP TABLE item');
    }
}
