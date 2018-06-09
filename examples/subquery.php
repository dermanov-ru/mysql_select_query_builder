<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 03.06.2018
 * Time: 19:31
 *
 *
 */


require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/init.php";

$connection = \Core\Db\DbManager::getConnection();
$catalogIbockId = 2;

/*
 * поулчить торговые предложения товара
 * и получтить их размер
 *

SELECT    `b_iblock_element`.`ID`, `b_iblock_element`.`NAME` AS `ELEMENT_NAME`,    `e`.`value`
FROM `b_iblock_element`

INNER JOIN `b_iblock_element_property` as `b1` ON `b1` .`iblock_element_id` = `b_iblock_element`.`id`

INNER JOIN `b_iblock_element_property` `b2` ON b2 .`iblock_element_id` = `b1` .`iblock_element_id`

INNER JOIN `b_iblock_property_enum` `e` ON `e`.`id` = `b2` .`value`

WHERE  ( ( b1 .`IBLOCK_PROPERTY_ID` = '28' AND b1 .`VALUE` = '36923' )  AND  ( `b2` .`IBLOCK_PROPERTY_ID` = '31' ) )

 * */


/*
 * а нужно получтить все товары у которых етсть ТП с размером=40
 *
    SELECT   b2.*
    FROM `b_iblock_element_property` b1
    
# присоединить строки в которых в клонке елем_ид сидит тоже значение отфильтрованного ТП. но взять только значения св-в=28, то есть привязка к товару
# то есть находим подходящие ТП и потом берем у них значение св-ва "привязка к товару" - это и будут итогвовые товары по фильтру.
    INNER JOIN `b_iblock_element_property` as `b2` ON `b2` .`IBLOCK_ELEMENT_ID` = b1.IBLOCK_ELEMENT_ID
    WHERE
    b1. `IBLOCK_PROPERTY_ID` = '31'  AND b1.VALUE=20
    and
    b2.IBLOCK_PROPERTY_ID=28

# ищем заведомо известный товар у которого есть предложение с 43 размером (енум=20)
    #and
    #b2.value=36923
    
    limit 10
 * */


/*
 * а это финальный запрос с получение инфы по найденным товарам - надо его собрыть через билдер!
 *
SELECT    `b_iblock_element`.`ID`, `b_iblock_element`.`NAME` AS `ELEMENT_NAME`
FROM `b_iblock_element`

WHERE
`b_iblock_element`.id IN (
    SELECT   b2.value
    FROM `b_iblock_element_property` b1
    
    INNER JOIN `b_iblock_element_property` as `b2` ON `b2` .`IBLOCK_ELEMENT_ID` = b1.IBLOCK_ELEMENT_ID
    
    WHERE
    b1. `IBLOCK_PROPERTY_ID` = '31'  AND b1.VALUE=20
    and
    b2.IBLOCK_PROPERTY_ID=28
)
limit 10

 * */

$selectBuilderSubquery = $connection->selectFrom("b_iblock_element_property");

$result = $selectBuilderSubquery
    // filter by offer prop "size"
    ->whereEqual("IBLOCK_PROPERTY_ID", 31)
    ->whereEqual("VALUE", 20)
    ->join(
        $selectBuilderSubquery->joinQuery("b_iblock_element_property", "iblock_element_id", "b_iblock_element_property", "IBLOCK_ELEMENT_ID", "INNER",  "slice_by_offer_prop")
            ->addSelectField("IBLOCK_ELEMENT_ID", "PRODUCT_ID")
            ->setWhere(
                $connection->selectFrom("slice_by_offer_prop")
                    ->whereEqual("IBLOCK_PROPERTY_ID", 28)
            )
    )
;


$selectBuilderFIlteredProducts = $connection->selectFrom("b_iblock_element");

$result = $selectBuilderFIlteredProducts
    ->addSelectField("ID", "PRODUCT_ID")
    ->addSelectField("NAME", "PRODUCT_NAME")
    ->whereInSubquery("ID", $selectBuilderSubquery)
    ->setLimit(10)
    ->fetchAll();

echo '<pre><=== \$result ===></pre><pre>' . print_r($result, 1) . '</pre><pre><\=== \$result ===></pre>';



$selectBuilderFIlteredProducts = $connection->selectFrom("b_iblock_element");

$result = $selectBuilderFIlteredProducts
    ->addSelectField("ID", "PRODUCT_ID")
    ->addSelectField("NAME", "PRODUCT_NAME")
    ->whereNotInSubquery("ID", $selectBuilderSubquery)
    ->setLimit(10)
    ->fetchAll();

echo '<pre><=== \$result ===></pre><pre>' . print_r($result, 1) . '</pre><pre><\=== \$result ===></pre>';