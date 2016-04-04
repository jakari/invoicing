<?php

use Phinx\Migration\AbstractMigration;

class InvoiceTable extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE TYPE invoice_status AS ENUM ('PENDING', 'PAID');");

        $this->execute('CREATE TABLE invoice(
            id SERIAL NOT NULL PRIMARY KEY,
            invoice_number SERIAL NOT NULL,
            reference_number INT NOT NULL,
            created DATE NOT NULL,
            due DATE NOT NULL,
            status invoice_status NOT NULL
        )');
    }

    public function down()
    {
        $this->execute('DROP TABLE invoice');
        $this->execute("DROP TYPE invoice_status");
    }
}
