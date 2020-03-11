<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200310134819 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE invoice RENAME COLUMN invoice_number TO id');
        $this->addSql('ALTER SEQUENCE invoice_invoice_number_seq RENAME TO invoice_id_seq');
        $this->addSql('ALTER TABLE invoice ADD invoice_number INT NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER reference_number SET NOT NULL');
        $this->addSql('CREATE TABLE company (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE parameters (id SERIAL NOT NULL, company INT NOT NULL, key VARCHAR(255) NOT NULL, value TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_69348FE4FBF094F ON parameters (company)');
        $this->addSql('ALTER TABLE parameters ADD CONSTRAINT FK_69348FE4FBF094F FOREIGN KEY (company) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice ADD company INT NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517444FBF094F FOREIGN KEY (company) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_906517444FBF094F ON invoice (company)');
        $this->addSql('ALTER TABLE customer ADD company INT NOT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E094FBF094F FOREIGN KEY (company) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_81398E094FBF094F ON customer (company)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER SEQUENCE invoice_id_seq RENAME TO invoice_invoice_number_seq');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT FK_81398E094FBF094F');
        $this->addSql('DROP INDEX IDX_81398E094FBF094F');
        $this->addSql('ALTER TABLE customer DROP company');
        $this->addSql('ALTER TABLE parameters DROP CONSTRAINT FK_69348FE4FBF094F');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_906517444FBF094F');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE parameters');
        $this->addSql('DROP INDEX IDX_906517444FBF094F');
        $this->addSql('ALTER TABLE invoice DROP company');
        $this->addSql('ALTER TABLE invoice DROP invoice_number');
        $this->addSql('ALTER TABLE invoice ALTER reference_number DROP NOT NULL');
        $this->addSql('ALTER TABLE invoice RENAME COLUMN id TO invoice_number');
    }
}
