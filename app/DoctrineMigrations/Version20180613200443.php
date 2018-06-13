<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180613200443 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TYPE invoice_status AS ENUM (\'PENDING\', \'PAID\')');
        $this->addSql('CREATE SEQUENCE customer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE invoice_invoice_number_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE invoice_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE customer (id INT NOT NULL, name VARCHAR(255) NOT NULL, street_name VARCHAR(255) NOT NULL, post_code VARCHAR(10) NOT NULL, city VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE invoice (invoice_number INT NOT NULL, customer INT NOT NULL, reference_number INT DEFAULT NULL, created DATE NOT NULL, due DATE NOT NULL, status invoice_status NOT NULL, PRIMARY KEY(invoice_number))');
        $this->addSql('CREATE INDEX IDX_9065174481398E09 ON invoice (customer)');
        $this->addSql('COMMENT ON COLUMN invoice.status IS \'(DC2Type:invoice_status)\'');
        $this->addSql('CREATE TABLE invoice_item (id INT NOT NULL, invoice INT NOT NULL, description VARCHAR(255) NOT NULL, amount INT NOT NULL, tax INT NOT NULL, price INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1DDE477B90651744 ON invoice_item (invoice)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174481398E09 FOREIGN KEY (customer) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice_item ADD CONSTRAINT FK_1DDE477B90651744 FOREIGN KEY (invoice) REFERENCES invoice (invoice_number) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_9065174481398E09');
        $this->addSql('ALTER TABLE invoice_item DROP CONSTRAINT FK_1DDE477B90651744');
        $this->addSql('DROP SEQUENCE customer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE invoice_invoice_number_seq CASCADE');
        $this->addSql('DROP SEQUENCE invoice_item_id_seq CASCADE');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_item');
        $this->addSql('DROP TYPE invoice_status');
    }
}
