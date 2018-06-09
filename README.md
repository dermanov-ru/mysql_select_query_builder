О проекте
===
Проект представляет собой ООП конструктор запросов к MySql. 

Мне нужно было написать пару селектов в проекте на чистом PHP, но руки отказывались делать это по старинке. Подключать полноценную ORM библиотеку не хотелось, и я решил написать простой компоновщик запросов. Далее, из спортивного интереса, я решил реализовать поддержку всех основных операций для формирования SELECT запросов.

Возможности
===
Реализована поддержка следующих операций:
- указать поля для выборки, указать им алиасы
- агрегатные функции (min, max, sum, avg, count), и возможность расширения
- указать сортировку, можно несколько
- сгруппировать, можно по нескольким полям
- фильтрация по рантайм полям (having)
- присоединение таблиц, можно несколько (join)
- фильтрация по полям присоединенных таблиц
- сортировка по полям присоединенных таблиц
- объединение запросов (union), можно несколько
- поддержка логических операций NOT, OR, OR NOT
- человекопонятное форматирование условий в блоке WHERE (переносы строк, скобки, отступы)
- поддержка подзапросов  в операторе IN

Установка
===
1. Склонировать репозиторий
2. Настроить подключение к БД, чтобы иметь возможность сразу выполнить запрос.
Для этого создать файл с конфигом для подключения к БД
`/lib/conf/db.php`, в котором объявить параметры подключения:

```
$db   = '';
$user = '';
$pass = '';
```

Формировать запросы можно и без подключения к БД.

Начало работы
===

## Подключение ядра
Для начала работы нужно подключить автозагрузчик классов проекта.
```
<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/init.php";
```

## Работа без подключения к БД
Можно сформировать запрос и посмотреть что получилось.
```
<?php
$selectBuilder = new \Core\Db\Query\SelectQueryBuilder("b_iblock_element_property");
$result = $selectBuilder->getSql();
```

## Работа с настроенным подключением к БД
При настроенном подключении к БД можно создавать объект конструктора через объект подключения к БД. 
В таком случае, у конструктора сразу же заполняется ссылка на подключение к БД. И тогда можно сформировать запрос, сразу выполнить его и посмотреть на результат - в одной цепочке вызовов.

Далее в примерах будет использоваться именно этот способ создания объекта конструктора и выполнения запроса. 
```
<?php
$connection = \Core\Db\DbManager::getConnection();

$selectBuilder = $connection->selectFrom("b_iblock_element_property");
$result = $selectBuilder->fetchAll();
```

Можно и явно получить итоговый запрос и выполнить его через объект подключения к БД. Но мне первый способ нравится больше.
```
<?php
$connection = \Core\Db\DbManager::getConnection();

$selectBuilder = new \Core\Db\Query\SelectQueryBuilder("b_iblock_element_property");
$result = $connection->query( $selectBuilder->getSql() );
```

Примеры
===

## Получить все содержимое таблицы
```
<?php
$selectBuilder = $connection->selectFrom("b_iblock_element_property");
$result = $selectBuilder->fetchAll();
```

Будет сформирован запрос
```
SELECT  *
FROM `b_iblock_element_property`
```

## Настройка SELECT

### Указать поля для выборки
#### Пример

Вторым параметром можно указать алиас
```
<?php
$selectBuilder
    ->addSelectField("NAME", "IBLOCK_NAME")
```

#### Общий пример
```
<?php
$selectBuilder = $connection->selectFrom("b_iblock");

$result = $selectBuilder
    ->addSelectField("ID")
    ->addSelectField("NAME", "IBLOCK_NAME")
    ->addSelectField("CODE", "IBLOCK_CODE")
    ->fetchAll();
```

Будет сформирован запрос
```
SELECT  `b_iblock`.`ID`, `b_iblock`.`NAME` AS `IBLOCK_NAME`, `b_iblock`.`CODE` AS `IBLOCK_CODE` 
FROM `b_iblock`
```

### Выбрать только уникальные строки (DISTINCT)
#### Пример
```
<?php
$selectBuilder
    ->distinct()
```

#### Общий пример
```
<?php
$selectBuilder = $connection->selectFrom("b_iblock");

$result = $selectBuilder
    ->distinct()
    ->addSelectField("ID")
    ->addSelectField("NAME", "IBLOCK_NAME")
    ->addSelectField("CODE", "IBLOCK_CODE")
    ->fetchAll();
```

Будет сформирован запрос
```
SELECT DISTINCT `b_iblock`.`ID`, `b_iblock`.`NAME` AS `IBLOCK_NAME`, `b_iblock`.`CODE` AS `IBLOCK_CODE` 
FROM `b_iblock`
```

### Агрегатные функции
#### Пример
Функция добавляет поле в селект. 
Можно выбрать только уникальные значение - задается вторым параметром.
Третим параметром задается алиас, по умолчанию - название агрегатной функции.
```
<?php
$selectBuilder
    ->min("sort")
    ->max("sort")
    ->avg("sort")
    ->count("sort", false, "total_count")
    ->count("sort", true, "unique_count")
```

#### Общий пример
```
<?php
$selectBuilder = $connection->selectFrom("b_iblock");

$result = $selectBuilder
    ->min("sort")
    ->max("sort")
    ->avg("sort")
    ->count("sort", false, "total_count")
    ->count("sort", true, "unique_count")
    ->fetchAll();
```

Будет сформирован запрос
```
SELECT  MIN( `b_iblock`.`sort` )  AS `MIN`, MAX( `b_iblock`.`sort` )  AS `MAX`, AVG( `b_iblock`.`sort` )  AS `AVG`, COUNT( `b_iblock`.`sort` )  AS `total_count`, COUNT(  DISTINCT `b_iblock`.`sort` )  AS `unique_count` 
FROM `b_iblock`
```

## Настройка ORDER BY

#### Пример

Вторым параметром можно указать обратное направление сортировки.
```
<?php
$selectBuilder
    ->addOrderbyField("sort", false)
    ->addOrderbyField("name")
```

Чтобы отсортировать результат по алиасу
```
<?php
$selectBuilder
    ->addOrderbyAlias("COUNT", false)
```

#### Общий пример
```
<?php
$selectBuilder = $connection->selectFrom("b_iblock");

$result = $selectBuilder
    ->addSelectField("ID")
    ->addSelectField("sort")
    ->addSelectField("NAME")
    ->addSelectField("CODE")
    ->addOrderbyField("sort", false)
    ->addOrderbyField("name")
    ->fetchAll();
```

Будет сформирован запрос
```
SELECT  `b_iblock`.`ID`, `b_iblock`.`sort`, `b_iblock`.`NAME`, `b_iblock`.`CODE` 
FROM `b_iblock`
ORDER BY `b_iblock`.`sort` DESC, `b_iblock`.`name` ASC
```

## Настройка LIMIT и OFFSET

#### Пример

```
<?php
$selectBuilder
    ->setLimit(1)
    ->setOffset(2)
```

#### Общий пример
```
<?php
$selectBuilder = $connection->selectFrom("b_iblock");

$result = $selectBuilder
    ->addSelectField("ID")
    ->addSelectField("sort")
    ->addSelectField("NAME")
    ->addSelectField("CODE")
    ->addOrderbyField("sort", false)
    ->addOrderbyField("name")
    ->setLimit(1)
    ->setOffset(2)
    ->fetchAll();
```

Будет сформирован запрос

```
SELECT  `b_iblock`.`ID`, `b_iblock`.`sort`, `b_iblock`.`NAME`, `b_iblock`.`CODE` 
FROM `b_iblock`
ORDER BY `b_iblock`.`sort` DESC, `b_iblock`.`name` ASC
LIMIT 1 OFFSET 2
```

## Настройка GROUP BY

#### Пример

Обычно группировка нужна для подсчета количества
```
<?php
$selectBuilder
    ->addGroupBy("IBLOCK_PROPERTY_ID")
    ->addGroupBy("IBLOCK_ELEMENT_ID")
    ->count("ID", false, "COUNT")
```

#### Общий пример

Так можно посчитать сколько свойств имеет каждый элемент инфоблока. 
Получим первые 10 элементов с самым большим количеством свойств.
```
<?php
$selectBuilder = $connection->selectFrom("b_iblock_element_property");

$result = $selectBuilder
    ->addSelectField("IBLOCK_PROPERTY_ID")
    ->addSelectField("IBLOCK_PROPERTY_ID")
    ->count("ID", false, "COUNT")
    ->addGroupBy("IBLOCK_PROPERTY_ID")
    ->addGroupBy("IBLOCK_ELEMENT_ID")
    ->addOrderbyAlias("COUNT", false)
    ->setLimit(10)
    ->fetchAll();
```

Будет сформирован запрос
```
SELECT  `b_iblock_element_property`.`IBLOCK_PROPERTY_ID`, `b_iblock_element_property`.`IBLOCK_PROPERTY_ID`, COUNT( `b_iblock_element_property`.`ID` )  AS `COUNT` , `b_iblock_element_property`.`IBLOCK_PROPERTY_ID`, `b_iblock_element_property`.`IBLOCK_ELEMENT_ID`
FROM `b_iblock_element_property`
GROUP BY `b_iblock_element_property`.`IBLOCK_PROPERTY_ID`, `b_iblock_element_property`.`IBLOCK_ELEMENT_ID`
ORDER BY `COUNT` DESC
LIMIT 10
```

## Настройка JOIN

#### Пример
 
```
<?php
$selectBuilder = $connection->selectFrom("b_iblock_element_property");

$selectBuilder
    ->addSelectField("ID")
    ->join(
        $selectBuilder->joinQuery("b_iblock_element_property", "iblock_element_id", "b_iblock_element_property", "IBLOCK_ELEMENT_ID", "INNER",  "joined_table_alias")
    )
```

Будет сформирован запрос
```
SELECT  `b_iblock_element_property`.`ID`, `joined_table_alias`.* 
FROM `b_iblock_element_property`
INNER JOIN `b_iblock_element_property` AS `joined_table_alias` ON `joined_table_alias`.`iblock_element_id` = `b_iblock_element_property`.`IBLOCK_ELEMENT_ID`
```

#### Общий пример
```
<?php
$selectBuilder = $connection->selectFrom("b_iblock_element_property");

$result = $selectBuilder
    ->whereEqual("IBLOCK_PROPERTY_ID", 31)
    ->whereEqual("VALUE", 20)
    ->join(
        $selectBuilder->joinQuery("b_iblock_element_property", "iblock_element_id", "b_iblock_element_property", "IBLOCK_ELEMENT_ID", "INNER",  "slice_by_offer_prop")
            ->addSelectField("IBLOCK_ELEMENT_ID", "PRODUCT_ID")
            ->setWhere(
                $connection->selectFrom("slice_by_offer_prop")
                    ->whereEqual("IBLOCK_PROPERTY_ID", 28)
            )
    )
    ->fetchAll();
```

Будет сформирован запрос
```
SELECT  `slice_by_offer_prop`.`IBLOCK_ELEMENT_ID` AS `PRODUCT_ID` 
FROM `b_iblock_element_property`
INNER JOIN `b_iblock_element_property` AS `slice_by_offer_prop` ON `slice_by_offer_prop`.`iblock_element_id` = `b_iblock_element_property`.`IBLOCK_ELEMENT_ID`
WHERE  (
	`b_iblock_element_property`.`IBLOCK_PROPERTY_ID` = '31'
) AND (
	`b_iblock_element_property`.`VALUE` = '20'
) AND (
	`slice_by_offer_prop`.`IBLOCK_PROPERTY_ID` = '28'
)
```

## Настройка WHERE

### Операции сравнения
#### Пример
 
```
<?php
$selectBuilder
    ->whereEqual("sort", 100)
    ->whereNotEqual("sort", 100)
    
    ->whereLower("sort", 600)
    ->whereLowerOrEqual("sort", 600)
    
    ->whereGreater("sort", 600)
    ->whereGreaterOrEqual("sort", 600)
    
    ->whereIn("sort", [ 100, 200, 300 ])
    ->whereNotIn("sort", [ 400, 500, 600 ])
```

#### Общий пример
```
<?php
$selectBuilder = $connection->selectFrom("b_iblock");

$result = $selectBuilder
    ->addSelectField("ID")
    ->addSelectField("NAME")
    ->addSelectField("CODE")
    
    ->whereEqual("sort", 100)
    ->whereNotEqual("sort", 100)
    
    ->whereLower("sort", 600)
    ->whereLowerOrEqual("sort", 600)
    
    ->whereGreater("sort", 600)
    ->whereGreaterOrEqual("sort", 600)
    
    ->whereIn("sort", [ 100, 200, 300 ])
    ->whereNotIn("sort", [ 400, 500, 600 ])
    
    ->whereLike("code", "product", $inFront = false, $inEnd = true)
    ->whereLike("code", "new", $inFront = true, $inEnd = true)
    ->whereNotLike("code", "offer", $inFront = true, $inEnd = false)
    
    ->fetchAll();
```

Будет сформирован запрос
```
SELECT  `b_iblock`.`ID`, `b_iblock`.`NAME`, `b_iblock`.`CODE` 
FROM `b_iblock`
WHERE  (
	`b_iblock`.`sort` = '100'
) AND (
	`b_iblock`.`sort` != '100'
) AND (
	`b_iblock`.`sort` < '600'
) AND (
	`b_iblock`.`sort` <= '600'
) AND (
	`b_iblock`.`sort` > '600'
) AND (
	`b_iblock`.`sort` >= '600'
) AND (
	`b_iblock`.`sort` IN  (
		'100', '200', '300'
	) 
) AND NOT  (
	`b_iblock`.`sort` IN  (
		'400', '500', '600'
	) 
) AND (
	`b_iblock`.`code` LIKE 'product%'
) AND (
	`b_iblock`.`code` LIKE '%new%'
) AND NOT  (
	`b_iblock`.`code` LIKE '%offer'
)
```

### Логические операторы NOT, OR, OR NOT

#### Общий пример
```
<?php
$selectBuilder = $connection->selectFrom("b_iblock");

$result = $selectBuilder
    ->addSelectField("ID")
    ->addSelectField("NAME")
    ->addSelectField("CODE")
    
    ->whereEqual("NAME", "Товары")
    ->whereEqual("code", "products")
    
    ->whereOr(
        $selectBuilder->newQuery()
            ->whereEqual("sort", 500)
    )
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
```

Будет сформирован запрос
```
SELECT  `b_iblock`.`ID`, `b_iblock`.`NAME`, `b_iblock`.`CODE` 
FROM `b_iblock`
WHERE  (
	`b_iblock`.`NAME` = 'Товары'
) AND (
	`b_iblock`.`code` = 'products'
) OR (
	 (
		`b_iblock`.`sort` = '500'
	) 
) AND NOT  (
	 (
		`b_iblock`.`code` = 'products_offers'
	) AND (
		`b_iblock`.`code` = 'payment'
	) 
) OR (
	 (
		`b_iblock`.`code` = 'products_offers'
	) AND (
		`b_iblock`.`code` = 'payment'
	) 
) OR NOT  (
	 (
		`b_iblock`.`code` = 'payment'
	) AND (
		`b_iblock`.`code` = 'payment'
	) 
) AND NOT  (
	 NOT  (
		 (
			`b_iblock`.`code` = 'products_offers'
		) AND (
			`b_iblock`.`code` = 'payment'
		) AND NOT  (
			 (
				`b_iblock`.`code` = 'products_offers'
			) AND (
				`b_iblock`.`code` = 'payment'
			) 
		) 
	) 
)
```

### Подзапрос
#### Пример
 
```
<?php

$selectBuilder = $connection->selectFrom("b_iblock_element");
$selectBuilderSubquery = $connection->selectFrom("b_iblock_element");

$selectBuilderSubquery
    ->addSelectField("ID")
    ->whereEqual("SORT", 500)
;

$result = $selectBuilder
    ->whereInSubquery("ID", $selectBuilderSubquery)
```

Будет сформирован запрос
```
SELECT  * 
FROM `b_iblock_element`
WHERE  (
	`b_iblock_element`.`ID` IN  (
		SELECT  `b_iblock_element`.`ID` 
		FROM `b_iblock_element`
		
		WHERE  (
			`b_iblock_element`.`SORT` = '500'
		)
	) 
)
```

#### Общий пример
**Задача**: 
отфильтровать товары по свойствам торговых предложений. Найти обувь 43 размера.
Размер - свойство типа список, 43 размер - это ENUM_ID=20. ID свойства "размер" = 31, ID свойства "привязка к товару" = 28. 

**Решение**:
Сначала нужно отфильтровать торговые предложения по размеру, затем присоединить свнова таблицу со свойствами товаров (этих самых ТП), и взять оттуда только значение свойства=28, то есть получить IDшники товаров, к которым относятся ТП.
Это и будут итогвовые товары по фильтру.

Этот запрос подставляем в качестве подзапроса.
```
<?php

$selectBuilderSubquery = $connection->selectFrom("b_iblock_element_property");

$selectBuilderSubquery
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
```

Будет сформирован запрос
```
SELECT  `b_iblock_element`.`ID` AS `PRODUCT_ID`, `b_iblock_element`.`NAME` AS `PRODUCT_NAME` 
FROM `b_iblock_element`
WHERE  (
	`b_iblock_element`.`ID` IN  (
		SELECT  `slice_by_offer_prop`.`IBLOCK_ELEMENT_ID` AS `PRODUCT_ID` 
		FROM `b_iblock_element_property`
		INNER JOIN `b_iblock_element_property` AS `slice_by_offer_prop` ON `slice_by_offer_prop`.`iblock_element_id` = `b_iblock_element_property`.`IBLOCK_ELEMENT_ID`
		WHERE  (
			`b_iblock_element_property`.`IBLOCK_PROPERTY_ID` = '31'
		) AND (
			`b_iblock_element_property`.`VALUE` = '20'
		) AND (
			`slice_by_offer_prop`.`IBLOCK_PROPERTY_ID` = '28'
		)
	) 
) 
LIMIT 10
```

## Настройка HAVING

#### Общий пример
```
<?php
$selectBuilder = $connection->selectFrom("test_table");

$result = $selectBuilder
    ->addGroupBy("column_1")
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
```

Будет сформирован запрос
```
SELECT  COUNT( `debug`.`ID` )  AS `COUNT` , `debug`.`param_1`
FROM `debug`
GROUP BY `debug`.`param_1`
HAVING  (
	 (
		`COUNT` = '2'
	) OR (
		 (
			`COUNT` = '4'
		) 
	) 
) 
ORDER BY `COUNT` ASC
```

## Настройка UNION

#### Общий пример
```
<?php
$selectBuilder = $connection->selectFrom("b_iblock_element");
$catalogIbockId = 2;

$result = $selectBuilder
    ->addSelectField("SORT")
    ->whereEqual("iblock_id", $catalogIbockId)
    ->whereEqual("ACTIVE", "Y")
    ->setLimit(1)
    ->union(
        $selectBuilder->unionQuery()
            ->whereEqual("iblock_id", $catalogIbockId)
            ->whereEqual("ACTIVE", "N")
            ->setLimit(2)
    )
    ->union(
        $selectBuilder->unionQuery()
            ->whereEqual("iblock_id", $catalogIbockId)
            ->whereEqual("ACTIVE", "N")
            ->whereEqual("SORT", 500)
            ->setLimit(3)
    )
    ->fetchAll();
```

Будет сформирован запрос
```
SELECT  `b_iblock_element`.`SORT` 
FROM `b_iblock_element`

WHERE  (
	`b_iblock_element`.`iblock_id` = '2'
) AND (
	`b_iblock_element`.`ACTIVE` = 'Y'
) 



LIMIT 1
UNION (
	SELECT  `b_iblock_element`.`SORT` 
	FROM `b_iblock_element`
	
	WHERE  (
		`b_iblock_element`.`iblock_id` = '2'
	) AND (
		`b_iblock_element`.`ACTIVE` = 'N'
	) 
	
	
	
	LIMIT 2
) 
UNION (
	SELECT  `b_iblock_element`.`SORT` 
	FROM `b_iblock_element`
	
	WHERE  (
		`b_iblock_element`.`iblock_id` = '2'
	) AND (
		`b_iblock_element`.`ACTIVE` = 'N'
	) AND (
		`b_iblock_element`.`SORT` = '500'
	) 
	
	
	
	LIMIT 3
)
```
