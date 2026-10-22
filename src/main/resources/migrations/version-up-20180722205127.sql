-- Doctrine migration Version20180722205127
ALTER TABLE invoice ADD remarking_time INT NOT NULL;
ALTER TABLE invoice ADD hesitation_cost_of_interest INT NOT NULL;
ALTER TABLE invoice ADD customer_reference VARCHAR(255) DEFAULT NULL;
ALTER TABLE invoice ADD delivery VARCHAR(255) DEFAULT NULL;
ALTER TABLE invoice ADD conditions_of_payment VARCHAR(255) DEFAULT NULL;
ALTER TABLE invoice ADD template VARCHAR(255) DEFAULT NULL;
