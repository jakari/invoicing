<?php

use Phinx\Migration\AbstractMigration;

class Customers extends AbstractMigration
{
    public function up()
    {
        $this->execute('ALTER TABLE invoice ADD COLUMN customer INT NOT NULL');
        $this->execute('CREATE TABLE customer(
            id SERIAL NOT NULL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            street_name VARCHAR(255) NOT NULL,
            post_code VARCHAR(10) NOT NULL,
            city VARCHAR(255) NOT NULL,
            email VARCHAR(255) DEFAULT NULL
        )');

        $this->execute('ALTER TABLE invoice ADD CONSTRAINT invoice_customers FOREIGN KEY (customer) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down()
    {
        $this->execute('ALTER TABLE invoice DROP CONSTRAINT invoice_customers');
        $this->execute('DROP TABLE customer');
    }
}
