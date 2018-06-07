<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 01.06.2018
 * Time: 21:39
 *
 * TODO operations
 */


namespace Core\Db\Query;


use Core\Db\DbConnection;
use Core\Db\DbHelper;
use Core\Db\Query\AgregatFunction\AgregatFunctionAbstract;
use Core\Db\Query\AgregatFunction\AvgFunction;
use Core\Db\Query\AgregatFunction\CountFunction;
use Core\Db\Query\AgregatFunction\MaxFunction;
use Core\Db\Query\AgregatFunction\MinFunction;
use Core\Db\Query\AgregatFunction\SumFunction;
use Core\Db\Query\WhereCondition\WhereAndNot;
use Core\Db\Query\WhereCondition\WhereConditionAbstract;
use Core\Db\Query\WhereCondition\WhereIn;
use Core\Db\Query\WhereCondition\WhereInSubquery;
use Core\Db\Query\WhereCondition\WhereLike;
use Core\Db\Query\WhereCondition\WhereOr;
use Core\Db\Query\WhereCondition\WhereOrNot;
use Core\Db\Query\WhereCondition\WhereValue;

class SelectQueryBuilder
{
    /**
     * @var DbConnection $connection
     * */
    protected $connection = null;
    
    protected $table = "";
    
    /**
     * @var WhereConditionAbstract[] $where
     * */
    protected $where = [ ];
    
    protected $having = "";
    protected $orderby = [];
    protected $groupby = [];
    
    /**
     * @var SelectStatement[] $select
     * */
    protected $select = [];
    
    protected $distinct = false;
    protected $limit  = 0;
    protected $offset = 0;
    
    /**
     * @var JoinQueryBuilder[] $join
     * */
    protected $join = [];
    
    protected $orWhere = [];
    
    protected $union = [];
    
    /**
     * SelectQueryBuilder constructor.
     *
     * @param $connection
     */
    public function __construct( $table )
    {
        $this->table = $table;
    }
    
    /**
     * @return null
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * @param null $connection
     */
    public function setConnection( DbConnection $connection )
    {
        $this->connection = $connection;
    }
    
    
    /**
     * @param string $table
     */
    public function setTable( $table )
    {
        $this->table = $table;
    }
    
    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }
    
    
    
    /**
     * Use for "OR" and "NOT" queries.
     *
     * ---
     *
     * Example
     * $selectBuilder
     *    ->whereEqual("foo", "bar")
     *    ->whereOr(
     *       $selectBuilder->newQuery()->whereEqual("foo", "baz")
     *    )
     * */
    public function newQuery(  )
    {
        $result = new SelectQueryBuilder($this->table);
        
        return $result;
    }
    
    /**
     * Use for filter queries in setHaving func.
     *
     * ---
     *
     * Example
     * $selectBuilder
     *    ->whereEqual("foo", "bar")
     *    ->setHaving(
     *       $selectBuilder->havingQuery()->whereEqual("foo", "baz")
     *    )
     * */
    public function havingQuery(  )
    {
        // all filters will be applied at runtime fileds (aliases) without link to table
        $result = new SelectQueryBuilder("");
        
        return $result;
    }
    
    /**
     * Use for "JOIN" queries.
     *
     * Just sugar :)
     * */
    public function joinQuery( $tableFrom, $fieldFrom, $tableTo, $fieldTo, $joinType = "INNER", $tableFromAlias = "" )
    {
        $result = new JoinQueryBuilder($tableFrom, $fieldFrom, $tableTo, $fieldTo, $joinType, $tableFromAlias);
        
        return $result;
    }
    
    /**
     * Use for "UNION" queries, for do not repeat all columns in select in same table.
     *
     * ---
     *
     * Example
     * ->addSelectField("ID")
     * ->addSelectField("NAME", "ELEMENT_NAME")
     * ->addSelectField("CODE")
     * ->addSelectField("ACTIVE")
     * ->addSelectField("IBLOCK_SECTION_ID")
     * ->whereEqual("iblock_id", $catalogIbockId)
     * ->whereEqual("ACTIVE", "Y")
     * //->whereEqual("IBLOCK_SECTION_ID", $nikeSectionId)
     * ->setLimit(5)
     * ->union(
     *     $selectBuilder->unionQuery()
     *         ->whereEqual("iblock_id", $catalogIbockId)
     *         ->whereEqual("ACTIVE", "N")
     *         ->setLimit(5)
     * ->fetchAll();
     * */
    public function unionQuery(  )
    {
        $result = new SelectQueryBuilder($this->table);
        $result->select = $this->select;
        
        return $result;
    }
    
    public function getSql(  )
    {
        $tableEscaped = DbHelper::escapeWithBacktick($this->table);
        
        $selectFields = $this->getSelect();
        $distinct = $this->getDistinct();
        
        $join = $this->getJoin();
        
        $filter = $this->getFilter();
        $orderby = $this->getOrderby();
        
        $groupbySelectFields = $this->getGroupBySelect();
        $groupby = $this->getGroupBy();
        $having = $this->getHaving();
        
        $limitAndOffset = $this->getLimitAndOffset();
        
        $union = $this->getUnion();
        
        $sql = "SELECT $distinct $selectFields $groupbySelectFields
FROM $tableEscaped
$join
$filter
$groupby
$having
$orderby
$limitAndOffset
$union
";
    
        $result = rtrim( $sql );
    
        return $result;
    }
    
    /**
     * Returns all rows from result sql.
     *
     * ---
     *
     * **Warning**: for first you need set up connection.
     *
     * @see SelectQueryBuilder::setConnection()
     * */
    public function fetchAll(  )
    {
        $sql = $this->getSql();
        $result = $this->connection->query($sql);
        
        return $result;
    }
    
    
    /**
     * Returns first row from result sql.
     *
     * ---
     *
     * **Warning**: for first you need set up connection.
     *
     * @see SelectQueryBuilder::setConnection()
     * */
    public function fetchOne(  )
    {
        $result = $this->fetchAll();
        
        return current($result);
    }
    
    public function distinct(  )
    {
        $this->distinct = true;
        
        return $this;
    }
    
    protected function getDistinct(  )
    {
        $result = $this->distinct ? "DISTINCT" : "";
        
        return $result;
    }
    
    public function count( $field, $distinct = false, $alias = "COUNT" )
    {
        $this->addSelectField($field, $alias, new CountFunction($distinct));
        
        return $this;
    }
    
    public function avg( $field, $distinct = false, $alias = "AVG" )
    {
        $this->addSelectField($field, $alias, new AvgFunction($distinct));
    
        return $this;
    }
    
    public function min( $field, $distinct = false, $alias = "MIN" )
    {
        $this->addSelectField($field, $alias, new MinFunction($distinct));
    
        return $this;
    }
    
    public function max( $field, $distinct = false, $alias = "MAX" )
    {
        $this->addSelectField($field, $alias, new MaxFunction($distinct));
    
        return $this;
    }
    
    public function sum( $field, $distinct = false, $alias = "SUM" )
    {
        $this->addSelectField($field, $alias, new SumFunction($distinct));
    
        return $this;
    }
    
    protected function getSelect(  )
    {
        $result = $this->select ? DbHelper::implodeAndEscape( $this->select, "" ) : "*";
        
        return $result;
    }
    
    public function addSelectField( $field, $alias = "", AgregatFunctionAbstract $agregatFunction = null )
    {
        $this->select[] = ( new SelectStatement($this->getTable(), $field, $alias, $agregatFunction) )->getSelectField();
        
        return $this;
    }
    
    public function addSelectStatement( SelectStatement $selectStatement )
    {
        $this->select[] = $selectStatement->getSelectField();
        
        return $this;
    }
    
    public function addOrderbyField( $field, $asc = true )
    {
        $direction = $asc ? "ASC" : "DESC";
        $this->orderby[] = DbHelper::fieldWithTable($this->table, $field). " " . $direction;
        
        return $this;
    }
    
    public function addOrderbyAlias( $alias, $asc = true )
    {
        $direction = $asc ? "ASC" : "DESC";
        $this->orderby[] = DbHelper::escapeWithBacktick($alias). " " . $direction;
        
        return $this;
    }
    
    public function addGroupBy( $field )
    {
        $this->groupby[] = DbHelper::fieldWithTable($this->table, $field);
        
        return $this;
    }
    
    protected function getGroupBySelect( )
    {
        if (!$this->groupby)
            return "";
    
        $result = implode(", ", $this->groupby);
        $result = ", " . $result;
    
        return $result;
    }
    
    protected function getGroupBy( )
    {
        if (!$this->groupby)
            return "";
    
        $groupBy = implode(", ", $this->groupby);
        $result = "GROUP BY " . $groupBy;
    
        return $result;
    }
    
    protected function getHaving( )
    {
        $having = $this->having;
    
        if ($having) {
            $result = "HAVING " . $having;
        }
        else
            $result = "";
    
        return $result;
    }
    
    protected function getOrderby(  )
    {
        if (!$this->orderby)
            return "";
        
        $orderby = implode(", ", $this->orderby);
        $result = "ORDER BY " . $orderby;
        
        return $result;
    }
    
    public function selectAll(  )
    {
        $this->select = [];
        
        return $this;
    }
    
    public function whereBetween( $field, $from, $to, $includeFrom = true, $includeTo = true )
    {
        if ($includeFrom)
            $this->whereGreaterOrEqual($field, $from);
        else
            $this->whereGreater($field, $from);
        
        if ($includeTo)
            $this->whereLowerOrEqual($field, $to);
        else
            $this->whereLower($field, $to);
        
        return $this;
    }
    
    public function whereLower( $field, $to )
    {
        $this->where[] = new WhereConditionAbstract("AND", $field, " < ", new WhereValue($to), $this);
        
        return $this;
    }
    
    public function whereLowerOrEqual( $field, $to )
    {
        $this->where[] = new WhereConditionAbstract("AND", $field, " <= ", new WhereValue($to), $this);
        
        return $this;
    }
    
    public function whereGreater( $field, $from)
    {
        $this->where[] = new WhereConditionAbstract("AND", $field, " > ", new WhereValue($from), $this);
        
        return $this;
    }
    
    public function whereGreaterOrEqual( $field, $from)
    {
        $this->where[] = new WhereConditionAbstract("AND", $field, " >= ", new WhereValue($from), $this);
        
        return $this;
    }
    
    public function whereEqual( $field, $value )
    {
        $this->where[] = new WhereConditionAbstract("AND", $field, " = ", new WhereValue($value), $this);
        
        return $this;
    }
    
    public function whereNotEqual( $field, $value )
    {
        $this->where[] = new WhereConditionAbstract("AND", $field, " != ", new WhereValue($value), $this);
        
        return $this;
    }
    
    public function setHaving( SelectQueryBuilder $queryBuilder )
    {
        $having = $queryBuilder->combineFinalFilter();
    
        if ($having)
            $this->having = DbHelper::wrapWithBraketsAndTabulate( $having );
    
        return $this;
    }
    
    public function whereIn( $field, $values )
    {
        $this->where[] = new WhereIn("AND", $field, $values, $this);
        
        return $this;
    }
    
    public function whereNotIn( $field, $values )
    {
        $whereIn = new WhereIn( "AND", $field, $values, $this );
        $whereIn->setNotOperator(true);
        
        $this->where[] = $whereIn;
        
        return $this;
    }
    
    public function whereInSubquery( $field, SelectQueryBuilder $subQuerySelectBuilder )
    {
        $this->where[] = new WhereInSubquery("AND", $field, $subQuerySelectBuilder, $this);
        
        return $this;
    }
    
    public function whereNotInSubquery( $field, SelectQueryBuilder $subQuerySelectBuilder )
    {
        $whereInSubquery = new WhereInSubquery( "AND", $field, $subQuerySelectBuilder, $this );
        $whereInSubquery->setNotOperator(true);
        
        $this->where[] = $whereInSubquery;
        
        return $this;
    }
    
    public function whereLike( $field, $value, $inFront = false, $inEnd = true )
    {
        $this->where[] = new WhereLike("AND", $field, $value, $inFront, $inEnd, $this);
        
        return $this;
    }
    
    public function whereNotLike( $field, $value, $inFront = false, $inEnd = true )
    {
        $whereLike = new WhereLike( "AND", $field, $value, $inFront, $inEnd, $this );
        $whereLike->setNotOperator(true);
        
        $this->where[] = $whereLike;
        
        return $this;
    }
    
    public function whereOr ( SelectQueryBuilder $queryBuilder )
    {
        $this->assertWhereNotEmpty();
        $this->where[] = new WhereOr($queryBuilder);
    
        return $this;
    }
    
    public function whereNot ( SelectQueryBuilder $queryBuilder )
    {
        $this->where[] = new WhereAndNot($queryBuilder);
        
        return $this;
    }
    
    public function whereOrNot ( SelectQueryBuilder $queryBuilder )
    {
        $this->assertWhereNotEmpty();
        $this->where[] = new WhereOrNot($queryBuilder);
        
        return $this;
    }
    
    protected function getFilter()
    {
        $combinedFilter = $this->combineFinalFilter();
        $result = $combinedFilter ? "WHERE " . $combinedFilter : "";
    
        return $result;
    }
    
    public function combineFinalFilter()
    {
        $result = "";
        
        if ($this->where) {
            // first condition must goes without AND|OR logic operator
            $this->where[ 0 ]->setLogicOperator("");
            
            foreach ( $this->where as $item ) {
                $result .= $item->getWhereCondition();
            }
        }
        
        return $result;
    }
    
    /**
     * @param int $limit
     */
    public function setLimit( $limit )
    {
        $this->limit = $limit;
        
        return $this;
    }
    
    /**
     * @param int $offset
     */
    public function setOffset( $offset )
    {
        $this->offset = $offset;
    
        return $this;
    }
    
    /**
     * @return int
     */
    public function getLimitAndOffset()
    {
        if ($this->limit && $this->offset)
            $result = "LIMIT " . $this->limit . " OFFSET " . $this->offset;
        else if ($this->limit )
            $result = "LIMIT " . $this->limit;
        
        // offset without limit can't exists
        
        return $result;
    }
    
    public function join( JoinQueryBuilder $join )
    {
        $this->join[] = $join->getSql();
        $this->select = array_merge($this->select, $join->getSelect());
        $this->orderby = array_merge($this->orderby, $join->getOrderby());
    
        $joinWhereQueryBuilder = $join->getWhere();
        
        if ( $joinWhereQueryBuilder )
            $this->where = array_merge($this->where, $joinWhereQueryBuilder->where);
        
        return $this;
    }
    
    protected function getJoin( )
    {
        $result = implode("\n", $this->join);
    
        return $result;
    }
    
    public function union( SelectQueryBuilder $queryBuilder )
    {
        $this->union[] = $queryBuilder->getSql();
        
        return $this;
    }
    
    protected function getUnion( )
    {
        $result = "";
    
        foreach ( $this->union as $item ) {
            $result .= "
            
            UNION (
                $item
            )
            
            ";
        }
        
        return $result;
    }
    
    protected function assertWhereNotEmpty(  )
    {
        if (!$this->where)
            throw new \Exception ( "Where condition can not be empty to do this action" );
    }
}