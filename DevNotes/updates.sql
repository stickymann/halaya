ALTER TABLE `hndshkif`.`hsi_orders`     ADD COLUMN `notes` VARCHAR(256) NULL AFTER `orderlines`;
ALTER TABLE `hndshkif`.`hsi_orders_is`     ADD COLUMN `notes` VARCHAR(256) NULL AFTER `orderlines`;
ALTER TABLE `hndshkif`.`hsi_orders_hs`     ADD COLUMN `notes` VARCHAR(256) NULL AFTER `orderlines`;
UPDATE `hndshkif`.`params` SET `formfields`='<?xml version=\'1.0\' standalone=\'yes\'?>\n<formfields>\n <field><name>id</name><label>Id</label><type>hidden</type><value></value><options></options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n <field><name>order_id</name><label>Order Id</label><type>input</type><value></value><options>size=50</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n <field><name>batch_id</name><label>Batch Id</label><type>input</type><value></value><options>size=50</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n <field><name>customer_id</name><label>Customer Id</label><type>input</type><value></value><options>size=50</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n <field><name>tax_id</name><label>Tax Id</label><type>input</type><value></value><options>size=50</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n <field><name>name</name><label>Name</label><type>input</type><value></value><options>size=50</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n <field><name>contact</name><label>Contact</label><type>input</type><value></value><options>size=50</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n <field><name>street</name><label>Street</label><type>input</type><value></value><options>size=50</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n <field><name>city</name><label>City</label><type>input</type><value></value><options>size=50</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n <field><name>country</name><label>Country</label><type>input</type><value></value><options>size=50</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n <field><name>phone</name><label>Phone</label><type>input</type><value></value><options>size=50</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n <field><name>paymentterms</name><label>Payment Terms</label><type>input</type><value></value><options>size=50</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n <field><name>cdate</name><label>Creation Date</label><type>date</type><value></value><options>size=12 maxlength=10</options><onnew>enabled_po</onnew><onedit>enabled_po</onedit></field>\n <field><name>ctime</name><label>Creation Time</label><type>input</type><value></value><options>size=50</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n <field><name>orderlines</name><label>Orderlines</label><type>xmltable</type><value></value><options>size=50</options><onnew></onnew><onedit></onedit>\r\n <subtable>\r\n  <subfield><subname>sku</subname><sublabel>Sku</sublabel><width>120</width><align>left</align></subfield>\r\n  <subfield><subname>description</subname><sublabel>Description</sublabel><width>600</width><align>left</align></subfield>\r\n  <subfield><subname>qty</subname><sublabel>Qty</sublabel><width>100</width><align>center</align></subfield>\r\n  <subfield><subname>unitprice</subname><sublabel>Unitprice</sublabel><width>100</width><align>center</align></subfield>\r\n  <subfield><subname>total</subname><sublabel>Total</sublabel><width>125</width><align>left</align></subfield>\r\n </subtable>\r\n </field>\r\n <field><name>notes</name><label>Notes</label><type>input</type><value></value><options>size=50</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\r\n</formfields>' WHERE `id`='1003' AND `indexfield`='id';
UPDATE `hndshkif`.`enquirydefs` SET `formfields`='<?xml version=\'1.0\' standalone=\'yes\'?>\r\n<formfields> \r\n<field><name>id</name><label>Id</label><filterfield>yes</filterfield></field> \r\n<field><name>batch_id</name><label>Batch Id</label><filterfield>yes</filterfield></field> \r\n<field><name>customer_id</name><label>Customer Id</label><filterfield>yes</filterfield></field> \r\n<field><name>tax_id</name><label>Tax Id</label><filterfield>yes</filterfield></field>\r\n<field><name>name</name><label>Name</label><filterfield>yes</filterfield></field>\r\n<field><name>contact</name><label>Contact</label><filterfield>yes</filterfield></field>\r\n<field><name>street</name><label>Street</label><filterfield>yes</filterfield></field>\r\n<field><name>city</name><label>City</label><filterfield>yes</filterfield></field>\r\n<field><name>country</name><label>Country</label><filterfield>yes</filterfield></field>\r\n<field><name>phone</name><label>Phone</label><filterfield>no</filterfield></field>\r\n<field><name>paymentterms</name><label>Payment Terms</label><filterfield>yes</filterfield></field>\r\n<field><name>cdate</name><label>Order Date</label><filterfield>yes</filterfield></field>\r\n<field><name>ctime</name><label>Order Time</label><filterfield>no</filterfield></field>\r\n<field><name>orderlines</name><label>Order Lines</label><filterfield>no</filterfield></field>\r\n<field><name>notes</name><label>Notes</label><filterfield>no</filterfield></field>\r\n<field><name>input_date</name><label>Batch Date</label><filterfield>yes</filterfield></field> \r\n</formfields>\r\n' WHERE `id`='1001';
