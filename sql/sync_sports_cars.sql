-- ModelCars Pro - safe product synchronization for an existing model_cars_db
-- Run this in phpMyAdmin AFTER selecting model_cars_db.
-- This does not drop tables or delete users/orders.

USE `model_cars_db`;

UPDATE `products` SET
  `name`='Mazda RX-7 FD',
  `slug`='mazda-rx-7-fd',
  `image`='Mazda RX-7 FD.jpeg'
WHERE `id`=7;

UPDATE `products` SET
  `name`='Toyota GR Supra',
  `slug`='toyota-gr-supra',
  `image`='Toyota GR Supra.jpeg'
WHERE `id`=8;

UPDATE `products` SET
  `name`='McLaren 720S',
  `slug`='mclaren-720s',
  `image`='McLaren 720S.jpeg'
WHERE `id`=9;

UPDATE `products` SET
  `name`='Chevrolet Corvette Z06',
  `slug`='chevrolet-corvette-z06',
  `image`='Chevrolet Corvette C8 Z06.jpg'
WHERE `id`=10;

UPDATE `products` SET
  `name`='Porsche 911 GT3 RS',
  `slug`='porsche-911-gt3-rs',
  `image`='Porsche 911 GT3 RS.jpeg'
WHERE `id`=11;

INSERT INTO `products`
(`id`,`category_id`,`brand`,`name`,`slug`,`scale`,`description`,`price`,`old_price`,`stock_quantity`,`rating`,`reviews_count`,`badge`,`image`,`is_featured`)
VALUES
(12,1,'Nissan','Nissan Skyline GT-R (R34)','nissan-skyline-gt-r-r34','1:64',
'Iconic Nissan Skyline GT-R R34 performance model with aggressive aero styling, signature rear wing, detailed wheels, and classic R34 proportions.',
749.00,899.00,12,4.9,30,'New','Nissan Skyline GT-R R34.jpg',1)
ON DUPLICATE KEY UPDATE
`category_id`=VALUES(`category_id`),
`brand`=VALUES(`brand`),
`name`=VALUES(`name`),
`scale`=VALUES(`scale`),
`description`=VALUES(`description`),
`price`=VALUES(`price`),
`old_price`=VALUES(`old_price`),
`stock_quantity`=VALUES(`stock_quantity`),
`rating`=VALUES(`rating`),
`reviews_count`=VALUES(`reviews_count`),
`badge`=VALUES(`badge`),
`image`=VALUES(`image`),
`is_featured`=VALUES(`is_featured`);
