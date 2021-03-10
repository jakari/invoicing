<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210212085350 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE customer ADD additional_name VARCHAR(255) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE customer ADD contact_person VARCHAR(255) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE invoice ADD customer_additional_name VARCHAR(255) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE invoice ADD customer_contact_person VARCHAR(255) NOT NULL DEFAULT \'\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE customer DROP additional_name');
        $this->addSql('ALTER TABLE customer DROP contact_person');
        $this->addSql('ALTER TABLE invoice DROP customer_additional_name');
        $this->addSql('ALTER TABLE invoice DROP customer_contact_person');
    }
}
