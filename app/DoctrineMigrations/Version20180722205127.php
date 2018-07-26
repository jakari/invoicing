<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180722205127 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE invoice ADD remarking_time INT NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD hesitation_cost_of_interest INT NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD customer_reference VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD delivery VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD conditions_of_payment VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE invoice DROP remarking_time');
        $this->addSql('ALTER TABLE invoice DROP hesitation_cost_of_interest');
        $this->addSql('ALTER TABLE invoice DROP customer_reference');
        $this->addSql('ALTER TABLE invoice DROP delivery');
        $this->addSql('ALTER TABLE invoice DROP conditions_of_payment');
    }
}
