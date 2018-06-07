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
    ->whereEqual("NAME", "Товары")
    ->whereEqual("code", "products")
    ->whereNot(
        $selectBuilder->newQuery()
            ->whereEqual("code", "products_offers")
            ->whereEqual("code", "payment")
    )
    ->whereOr(
        $selectBuilder->newQuery()
            ->whereEqual("code", "products_offers")
            ->whereEqual("code", "payment")
    )
    ->whereOrNot(
        $selectBuilder->newQuery()
            ->whereEqual("code", "payment")
            ->whereEqual("code", "payment")
    )
    ->whereNot(
        $selectBuilder->newQuery()
            ->whereNot(
                $selectBuilder->newQuery()
                    ->whereEqual("code", "products_offers")
                    ->whereEqual("code", "payment")
                    ->whereNot(
                        $selectBuilder->newQuery()
                            ->whereEqual("code", "products_offers")
                            ->whereEqual("code", "payment")
                    )
            )
    )
    ->fetchAll();

echo '<pre><=== \$result ===></pre><pre>' . print_r($result, 1) . '</pre><pre><\=== \$result ===></pre>';
