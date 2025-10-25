SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS item_attributes;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS locations;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE locations (
  location_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  street VARCHAR(150),
  city VARCHAR(80),
  state_code CHAR(2),
  zip_code VARCHAR(10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menu_items (
  item_id INT AUTO_INCREMENT PRIMARY KEY,
  location_id INT NOT NULL,
  category_id INT NOT NULL,
  item_name VARCHAR(120) NOT NULL,
  price DECIMAL(6,2) NOT NULL CHECK (price >= 0),
  description VARCHAR(500),
  CONSTRAINT fk_menu_items_location FOREIGN KEY (location_id) REFERENCES locations(location_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_menu_items_category FOREIGN KEY (category_id) REFERENCES categories(category_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_menu_items_loc (location_id),
  INDEX idx_menu_items_cat (category_id),
  INDEX idx_menu_items_name (item_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE item_attributes (
  attribute_id INT AUTO_INCREMENT PRIMARY KEY,
  item_id INT NOT NULL,
  attribute VARCHAR(60) NOT NULL,
  CONSTRAINT fk_item_attributes_item FOREIGN KEY (item_id) REFERENCES menu_items(item_id) ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_attr_item (item_id),
  INDEX idx_attr_name (attribute)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO locations (name, street, city, state_code, zip_code) VALUES
  ('Runner''s Fuel Café - Kirkwood', '430 Kirkwood Ave', 'Bloomington', 'IN', '47408'),
  ('Runner''s Fuel Café - College Mall', '2894 E 3rd St', 'Bloomington', 'IN', '47401');

INSERT INTO categories (category_name) VALUES
  ('Breakfast'),
  ('Bowls'),
  ('Drinks'),
  ('Dessert');

INSERT INTO menu_items (location_id, category_id, item_name, price, description) VALUES
  (1, 1, 'Protein Oat Pancakes', 8.99, 'Fluffy oats, whey protein, maple drizzle.'),
  (1, 1, 'Avocado Power Toast', 7.49, 'Multigrain toast, smashed avo, chili flakes.'),
  (1, 2, 'Southwest Quinoa Bowl', 11.49, 'Quinoa, roasted corn, black beans, salsa.'),
  (1, 3, 'Iced Nitro Cold Brew', 4.49, 'Smooth, nitrogen-infused cold brew coffee.'),
  (2, 2, 'Teriyaki Salmon Bowl', 13.99, 'Brown rice, edamame, sesame, scallions.'),
  (2, 3, 'Berry Beet Smoothie', 6.49, 'Strawberry, blueberry, beet, Greek yogurt.'),
  (2, 4, 'Dark Chocolate Chia Pudding', 5.99, 'Cacao, almond milk, chia, toasted coconut.'),
  (2, 1, 'Egg White Spinach Wrap', 7.99, 'Egg whites, spinach, feta, wheat wrap.');

INSERT INTO item_attributes (item_id, attribute) VALUES
  (1, 'High Protein'),
  (1, 'Vegetarian'),
  (2, 'Vegetarian'),
  (2, 'Whole Grain'),
  (3, 'Vegan'),
  (3, 'Gluten-Free'),
  (4, 'Iced'),
  (4, 'Seasonal'),
  (5, 'Omega-3'),
  (6, 'Antioxidants'),
  (6, 'No Added Sugar'),
  (7, 'Gluten-Free'),
  (7, 'Dairy-Free'),
  (8, 'Low Calorie');

SELECT 'build.sql completed successfully' AS status;
