<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Invoice numbers are unique per company. Fails if duplicates already exist, check first with:
 * SELECT company, invoice_number, count(*) FROM invoice GROUP BY 1, 2 HAVING count(*) > 1
 */
final class Version20261022120000 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE UNIQUE INDEX invoice_company_invoice_number ON invoice (company, invoice_number)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX invoice_company_invoice_number');
    }
}
