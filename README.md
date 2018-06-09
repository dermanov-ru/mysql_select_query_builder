О проекте
===
Проект представляет собой ООП конструтор запросов к MySql. 

Мне нужно было написать пару селектов в проекте на чистом PHP, но руки отказывались делать это по старинке. Подключать полноценную ORM библиотеку не хотелось, и я решил написать простой компоновщик запросов. Далее, из спортивного интереса, я решил реализовать поддержку всех основновных операций для формирования SELECT запросов.

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
