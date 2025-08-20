ALTER TABLE `applicant_finder`.`users`   
	DROP COLUMN `name`;

ALTER TABLE `applicant_finder`.`jobs`  
  ADD FOREIGN KEY (`industry_id`) REFERENCES `applicant_finder`.`industries`(`id`);

ALTER TABLE `applicant_finder`.`jobs`   
	CHANGE `status` `status` ENUM('draft','active','closed') CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active' NOT NULL;
