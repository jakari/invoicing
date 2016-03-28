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
            created TIMESTAMP NOT NULL,
            due TIMESTAMP NOT NULL,
            status invoice_status NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_address VARCHAR(255) NOT NULL
        )');
    }

    public function down()
    {
        $this->execute('DROP TABLE invoice');
        $this->execute("DROP TYPE invoice_status");
    }
}
