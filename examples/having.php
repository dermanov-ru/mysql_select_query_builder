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

$selectBuilder = $connection->selectFrom("debug");

$result = $selectBuilder
    ->addGroupBy("param_1")
    ->count("ID")
    ->setHaving(
        $selectBuilder->havingQuery()
            ->whereEqual("COUNT", 2)
            ->whereOr(
                $selectBuilder->havingQuery()
                    ->whereEqual("COUNT", 4)
            )
    )
    ->addOrderbyAlias("COUNT")
    ->fetchAll();

echo '<pre><=== \$result ===></pre><pre>' . print_r($result, 1) . '</pre><pre><\=== \$result ===></pre>';