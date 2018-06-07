<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 06.06.2018
 * Time: 14:43
 *
 *
 */


namespace Core\Db\Query\WhereCondition;


use Core\Db\DbHelper;

class WhereValue
{
    protected $value;
    protected $needEscape;
    
    /**
     * WhereValue constructor.
     *
     * @param $value
     * @param $needEscape
     */
    public function __construct( $value, $needEscape = true )
    {
        $this->value = $value;
        $this->needEscape = $needEscape;
    }
    
    public function getValue(  )
    {
        if ($this->needEscape)
            $result = DbHelper::escapeWithSingleQuotes($this->value);
        else
            $result = $this->value;
        
        return $result;
    }
}