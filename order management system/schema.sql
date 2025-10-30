CREATE TABLE `users` (
  `user_id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL, 
  `role` ENUM('SuperAdmin', 'Admin') NOT NULL DEFAULT 'Admin',
  `is_suspended` BOOLEAN NOT NULL DEFAULT 0, 
  `date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE `products` (
  `product_id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `image_path` VARCHAR(255) NULL, 
  `added_by_user_id` INT(11) NOT NULL,
  `date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`added_by_user_id`) REFERENCES `users`(`user_id`)
);

CREATE TABLE `orders` (
  `order_id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `cash_tendered` DECIMAL(10, 2) NOT NULL,
  `change_given` DECIMAL(10, 2) NOT NULL,
  `processed_by_user_id` INT(11) NOT NULL, 
  `date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
  FOREIGN KEY (`processed_by_user_id`) REFERENCES `users`(`user_id`)
);


CREATE TABLE `order_items` (
  `order_item_id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL,
  `item_price_at_time_of_order` DECIMAL(10, 2) NOT NULL, 
  `date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`)
);