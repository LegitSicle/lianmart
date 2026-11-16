CREATE TABLE IF NOT EXISTS Users (
    userId INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(255) NOT NULL,
    lastname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATE DEFAULT (CURRENT_DATE),
    phoneNumber VARCHAR(20) NOT NULL,
    birthdate DATE
);

CREATE TABLE IF NOT EXISTS Addresses (
    addressId INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    province VARCHAR(255) NOT NULL,
    city VARCHAR(255) NOT NULL,
    barangay VARCHAR(255) NOT NULL,
    street VARCHAR(255) NOT NULL,
    landmark TEXT NOT NULL,
    houseNumber VARCHAR(255),
    zipCode VARCHAR(255),

    FOREIGN KEY (userId) REFERENCES Users(userId) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Admins (
    userId INT PRIMARY KEY,

    FOREIGN KEY (userId) REFERENCES Users(userId) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS DisabledUsers (
    userId INT PRIMARY KEY,

    FOREIGN KEY (userId) REFERENCES Users(userId) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Categories (
    categoryId INT AUTO_INCREMENT PRIMARY KEY,
    categoryName VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS Products (
    productId INT AUTO_INCREMENT PRIMARY KEY,
    categoryId INT NOT NULL,
    productName VARCHAR(255) NOT NULL,
    productPrice DECIMAL(10, 2) NOT NULL,
    productDetails TEXT NOT NULL,
    stock INT NOT NULL,
    created_at DATE,

    FOREIGN KEY (categoryId) REFERENCES Categories(categoryId)
);

CREATE TABLE IF NOT EXISTS ProductImages (
    productId INT PRIMARY KEY,
    imagePath VARCHAR(255) NOT NULL,

    FOREIGN KEY (productId) REFERENCES Products(productId)
);

CREATE TABLE IF NOT EXISTS CartItems (
    userId INT NOT NULL,
    productId INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity >= 1),

    PRIMARY KEY (userId, productId),

    FOREIGN KEY (userId) REFERENCES Users(userId),
    FOREIGN KEY (productId) REFERENCES Products(productId)
);

CREATE TABLE IF NOT EXISTS Orders (
    orderId INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    orderStatus ENUM('Ordered', 'Processed', 'Shipped', 'Delivered') NOT NULL,
    created_at DATE DEFAULT (CURRENT_DATE),

    FOREIGN KEY (userId) REFERENCES Users(userId)
);

CREATE TABLE IF NOT EXISTS OrderItems (
    orderId INT NOT NULL,
    productId INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity >= 1),
    purchasePrice DECIMAL(10, 2) NOT NULL,
    
    PRIMARY KEY (orderId, productId),
    FOREIGN KEY (orderId) REFERENCES Orders(orderId),
    FOREIGN KEY (productId) REFERENCES Products(productId)
);