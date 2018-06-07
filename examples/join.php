<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 06.06.2018
 * Time: 14:59
 *
 *
 */


require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/init.php";

$connection = \Core\Db\DbManager::getConnection();

$selectBuilder = $connection->selectFrom("b_iblock");

$result = $selectBuilder
    ->addSelectField("ID")
    ->addSelectField("NAME")
    ->addSelectField("CODE")
    ->whereEqual("code", "products")
    ->join(
        $selectBuilder->joinQuery("b_iblock_element", "iblock_id", "b_iblock", "id", "INNER", "elements")
            ->setWhere(
                (new \Core\Db\Query\SelectQueryBuilder("elements"))
                    //->whereEqual("active", "N")
                    ->whereEqual("iblock_section_id", 17)
            )
            ->addSelectField("name", "elem_name")
            ->addOrderbyField("elements", "sort", true)
            ->addOrderbyAlias("elem_name", "sort")
    )
    ->join(
        $selectBuilder->joinQuery("b_iblock_element", "iblock_id", "b_iblock", "id", "INNER", "elements__123")
            ->setWhere(
                (new \Core\Db\Query\SelectQueryBuilder("elements__123"))
                    //->whereEqual("active", "N")
                    ->whereEqual("iblock_section_id", 17)
            )
            ->addSelectField("name", "elem_name_123")
            ->addOrderbyField("elements__123", "sort", true)
            ->addOrderbyAlias("elem_name_123", "sort")
    )
    ->setLimit(10)
    ->fetchAll();

echo '<pre><=== \$result ===></pre><pre>' . print_r($result, 1) . '</pre><pre><\=== \$result ===></pre>';
