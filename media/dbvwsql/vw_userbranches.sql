SELECT
  `users`.`id`             AS `id`,
  `users`.`idname`         AS `idname`,
  `users`.`username`       AS `username`,
  `users`.`fullname`       AS `fullname`,
  `users`.`email`          AS `email`,
  `users`.`enabled`        AS `enabled`,
  `users`.`expiry_date`    AS `expiry_date`,
  `users`.`branch_id`      AS `branch_id`,
  `users`.`department_id`  AS `department_id`,
  `branches`.`description` AS `description`,
  `branches`.`location`    AS `location`,
  `branches`.`region_id`   AS `region_id`,
  `branches`.`active`      AS `active`
FROM (`users`
   JOIN `branches`
     ON (`users`.`branch_id` = `branches`.`branch_id`))
