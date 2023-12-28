UPDATE `idcsmart_nav` SET `url` = REPLACE(`url`,'.php','.htm');
UPDATE `idcsmart_menu` SET `url` = REPLACE(`url`,'.php','.htm') WHERE `menu_type`!='custom';
UPDATE `idcsmart_auth` SET `url` = REPLACE(`url`,'.php','.htm');
UPDATE `idcsmart_clientarea_auth` SET `url` = REPLACE(`url`,'.php','.htm');
UPDATE `idcsmart_configuration` SET `value` = REPLACE(`value`,'agreement.php','agreement.htm');