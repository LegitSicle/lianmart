INSERT INTO Categories (categoryName) 
SELECT 'Food and Snacks'
WHERE NOT EXISTS (
    SELECT 1 FROM Categories
    WHERE categoryName = 'Food and Snacks'
);

INSERT INTO Categories (categoryName) 
SELECT 'Drinks and Beverages'
WHERE NOT EXISTS (
    SELECT 1 FROM Categories
    WHERE categoryName = 'Drinks and Beverages'
);

INSERT INTO Categories (categoryName) 
SELECT 'Canned Goods'
WHERE NOT EXISTS (
    SELECT 1 FROM Categories
    WHERE categoryName = 'Canned Goods'
);

INSERT INTO Categories (categoryName) 
SELECT 'School Supplies'
WHERE NOT EXISTS (
    SELECT 1 FROM Categories
    WHERE categoryName = 'School Supplies'
);

INSERT INTO Categories (categoryName) 
SELECT 'Bath and Body'
WHERE NOT EXISTS (
    SELECT 1 FROM Categories
    WHERE categoryName = 'Bath and Body'
);