ALTER TABLE invoice ALTER template SET NOT NULL;
ALTER TABLE invoice RENAME COLUMN hesitation_cost_of_interest TO interest_on_arrears;
