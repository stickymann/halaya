SELECT
  `d`.`id`                        AS `id`,
  `d`.`deliverynote_id`           AS `deliverynote_id`,
  `o`.`id`                        AS `invoice_id`,
  `o`.`order_id`                  AS `order_id`,
  `o`.`branch_id`                 AS `branch_id`,
  `o`.`customer_id`               AS `customer_id`,
  `o`.`is_co`                     AS `is_co`,
  `o`.`cc_id`                     AS `cc_id`,
  `c`.`first_name`                AS `first_name`,
  `c`.`last_name`                 AS `last_name`,
  `c`.`customer_type`             AS `customer_type`,
  `c`.`address1`                  AS `address1`,
  `c`.`address2`                  AS `address2`,
  `c`.`city`                      AS `city`,
  `c`.`phone_mobile1`             AS `phone_mobile1`,
  `c`.`phone_home`                AS `phone_home`,
  `c`.`phone_work`                AS `phone_work`,
  `o`.`order_date`                AS `order_date`,
  `o`.`quotation_date`            AS `quotation_date`,
  `o`.`invoice_date`              AS `invoice_date`,
  `o`.`order_status`              AS `order_status`,
  `o`.`inventory_checkout_status` AS `inventory_checkout_status`,
  `o`.`inventory_update_type`     AS `inventory_update_type`,
  `o`.`inputter`                  AS `inputter`,
  `o`.`input_date`                AS `input_date`,
  `o`.`invoice_note`              AS `invoice_note`,
  `d`.`deliverynote_date`         AS `deliverynote_date`,
  `d`.`details`                   AS `details`,
  `d`.`status`                    AS `status`,
  `d`.`delivered_by`              AS `delivered_by`,
  `d`.`delivery_date`             AS `delivery_date`,
  `d`.`returned_signed_by`        AS `returned_signed_by`,
  `d`.`returned_signed_date`      AS `returned_signed_date`,
  `d`.`comments`                  AS `comments`
FROM ((`orders` `o`
    JOIN `deliverynotes` `d`
      ON (`o`.`order_id` = `d`.`order_id`))
   JOIN `customers` `c`
     ON (`o`.`customer_id` = `c`.`customer_id`))
