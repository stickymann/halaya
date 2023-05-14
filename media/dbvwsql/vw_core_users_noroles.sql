SELECT
  `users`.`idname`   AS `idname`,
  `users`.`fullname` AS `fullname`
FROM `users`
WHERE (`users`.`idname` IN(SELECT
                             `userroles`.`idname` AS `idname`
                           FROM `userroles`)IS FALSE
       AND (`users`.`branch_id` <> '_SYSTEM'))
