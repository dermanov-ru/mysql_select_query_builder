<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 06.06.2018
 * Time: 12:44
 *
 *
 */


namespace Core\Db;


class DbHelper
{
    
    public static function implodeAndEscape( $array, $escaper = "`" )
    {
        $escaped = array();
        
        foreach ( $array as $item ) {
            $escaped[] = DbHelper::escape($item, $escaper);
        }
        
        $result = implode(", ", $escaped);
        $result = trim($result, ", ");
        
        return $result;
    }
    
    public static function implodePlaceholders( $array )
    {
        $escaped = array();
        
        foreach ( $array as $item ) {
            $escaped[] = ":" . $item . "";
        }
        
        $result = implode(", ", $escaped);
        $result = trim($result, ", ");
        
        return $result;
    }
    
    public static function escapeWithBacktick( $field )
    {
        $escaper = "`";
        $result = DbHelper::escape($field, $escaper);
        
        return $result;
    }
    
    public static function wrapWithBrakets( $str )
    {
        $result = " ( " . $str . " ) ";
        
        return $result;
    }
    
    public static function wrapWithBraketsAndTabulate( $str )
    {
        $result = " (" . self::tab($str) . ") ";
        
        return $result;
    }
    
    public static function tab( $str )
    {
        // add tab for all lines exclude first
        $str = str_replace("\n", "\n\t", $str);
        
        // add tab for first line
        $result = "\n\t$str\n";
    
        return $result;
    }
    
    public static function escapeWithSingleQuotes( $field )
    {
        $result = DbHelper::escape($field, "'");
        
        return $result;
    }
    
    public static function escape( $field, $escaper )
    {
        $field = str_replace("$escaper", "\\$escaper", $field);
        $result = $escaper . $field . $escaper;
        
        return $result;
    }
    
    public static function fieldWithTable( $table, $field){
        if ($table)
            $result = DbHelper::escapeWithBacktick($table) . "." . DbHelper::escapeWithBacktick($field);
        else
            $result = DbHelper::escapeWithBacktick($field);
        
        return $result;
    }
}