ALTER TABLE Orders
MODIFY COLUMN orderStatus ENUM("Ordered", "Completed", "Processing", "Cancelled");

UPDATE Orders
SET orderStatus = "Processing"
WHERE orderStatus = "Ordered";

ALTER TABLE Orders
MODIFY COLUMN orderStatus ENUM("Completed", "Processing", "Cancelled") NOT NULL;