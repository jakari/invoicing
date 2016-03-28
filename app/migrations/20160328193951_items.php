<?php

use Phinx\Migration\AbstractMigration;

class Items extends AbstractMigration
{
    public function up()
    {
        $this->execute('CREATE TABLE item(
            id SERIAL NOT NULL PRIMARY KEY,
            invoice INT NOT NULL,
            description VARCHAR(255) NOT NULL,
            amount INT NOT NULL,
            tax INT NOT NULL,
            price INT NOT NULL
        )');

        $this->execute('ALTER TABLE item ADD CONSTRAINT invoice_items FOREIGN KEY (invoice) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down()
    {
        $this->execute('ALTER TABLE item DROP CONSTRAINT invoice_items');
        $this->execute('DROP TABLE item');
    }
}
