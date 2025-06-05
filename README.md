# stripe-apple-pay
Integrate Apple Pay on the Web using Stripe.js 2025 – A Step-by-Step Guide

Install stripe php sdk 
composer require stripe/stripe-php

create a sample database 

-- payments definition

CREATE TABLE `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `payment_intent_id` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `amount` int NOT NULL,
  `currency` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `status` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `customer_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `customer_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

Please follow me on Linked In https://www.linkedin.com/in/ravijordan/