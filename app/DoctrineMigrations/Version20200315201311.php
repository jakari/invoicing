<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200315201311 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE customer ADD vat VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD phone VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD customer_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD customer_street_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD customer_post_code VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD customer_city VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD customer_vat VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD customer_email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD customer_phone VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE customer DROP vat');
        $this->addSql('ALTER TABLE customer DROP phone');
        $this->addSql('ALTER TABLE invoice DROP customer_name');
        $this->addSql('ALTER TABLE invoice DROP customer_street_name');
        $this->addSql('ALTER TABLE invoice DROP customer_post_code');
        $this->addSql('ALTER TABLE invoice DROP customer_city');
        $this->addSql('ALTER TABLE invoice DROP customer_vat');
        $this->addSql('ALTER TABLE invoice DROP customer_email');
        $this->addSql('ALTER TABLE invoice DROP customer_phone');
    }
}
