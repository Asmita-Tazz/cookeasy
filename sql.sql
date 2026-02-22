-- 1. Create and Select Database
-- ============================
CREATE DATABASE IF NOT EXISTS cookeasy_db;
USE cookeasy_db;

-- 2. Admin Table
-- ============================
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50)  
);

-- 3. Users Table
-- ============================

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    reset_otp VARCHAR(10) DEFAULT NULL,   
    otp_expiry DATETIME DEFAULT NULL,
    status ENUM('active', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Category Table
-- ============================
CREATE TABLE category (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255) NULL
);

-- 5. Recipe Table
-- ============================

CREATE TABLE recipe (
    
    recipe_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NULL, -- Filled if an Admin creates it
    user_id INT NULL,  -- Filled if a User creates it
     user_role ENUM('admin', 'user') DEFAULT 'user',
    name VARCHAR(255) NOT NULL,
    description TEXT,
    instructions TEXT,
    prep_time INT, 
    image VARCHAR(255),
    servings INT DEFAULT 1,
    download_count INT DEFAULT 0,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE nutrition (
    recipe_id INT PRIMARY KEY,
    calories INT,
    protein_g INT,
    carbs_g INT,
    fat_g INT,
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE
);


-- 6. Ingredient Table
-- ============================
CREATE TABLE ingredient (
    ingredient_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    image VARCHAR(255) NULL -- Optional: If NULL, use a generic icon in code
);



CREATE TABLE ingredient_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    ingredient_name VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 7. Uses (Recipe - Ingredient Mapping)
-- ============================
CREATE TABLE uses (
    recipe_id INT,
    ingredient_id INT,
    quantity VARCHAR(50),
    unit VARCHAR(50),
    PRIMARY KEY (recipe_id, ingredient_id),
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredient(ingredient_id) ON DELETE CASCADE
);

-- 8. Categorized_in (Linking Recipes and Categories)
-- ============================
CREATE TABLE categorized_in (
    recipe_id INT,
    category_id INT,
    PRIMARY KEY (recipe_id, category_id),
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES category(category_id) ON DELETE CASCADE
);

-- 9. Favourite Table
-- ============================
CREATE TABLE favourites (
    fav_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    recipe_id INT,
    status VARCHAR(50) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_fav (user_id, recipe_id)
);

-- 10. Feedback Table
-- ============================
CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    recipe_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comments TEXT NOT NULL ,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE
);

-- 11. Shopping List Table
-- ============================
CREATE TABLE shopping_list (
    list_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    list_name VARCHAR(255) NOT NULL, -- No default, must be named by user or code
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


-- 12. isIncludedIn (Linking Shopping List and Ingredients)
-- ============================
CREATE TABLE isIncludedIn (
    list_id INT,
    ingredient_id INT,
    quantity VARCHAR(50),
    unit VARCHAR(50),
    is_bought TINYINT(1) DEFAULT 0, -- Move the "checked" status here!
    PRIMARY KEY (list_id, ingredient_id),
    FOREIGN KEY (list_id) REFERENCES shopping_list(list_id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredient(ingredient_id) ON DELETE CASCADE
);







CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,            -- The Receiver ID
    user_role ENUM('admin', 'user'), -- The Receiver Role (Where the alert shows up)
    sender_id INT NOT NULL,          -- The Person who triggered the alert
    sender_role ENUM('admin', 'user'),-- Role of the person who sent it
    message TEXT NOT NULL,           -- The alert message
    type VARCHAR(50) DEFAULT 'info', -- 'feedback', 'approval', 'request', 'system'
    target_id INT DEFAULT NULL,      -- The Recipe/Ingredient ID to link to
    is_read TINYINT(1) DEFAULT 0,    -- 0 = Unread, 1 = Read
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



