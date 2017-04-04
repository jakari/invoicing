<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160327082801 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("CREATE TYPE invoice_status AS ENUM ('PENDING', 'PAID');");

        $this->addSql('CREATE TABLE invoice(
            id SERIAL NOT NULL PRIMARY KEY,
            invoice_number SERIAL NOT NULL,
            reference_number INT NOT NULL,
            created DATE NOT NULL,
            due DATE NOT NULL,
            status invoice_status NOT NULL
        )');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE invoice');
        $this->addSql("DROP TYPE invoice_status");
    }
}
