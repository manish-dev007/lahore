<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product_details extends Model
{

    
    /**   
    * Return the sluggable configuration array for this model.
    *
    * @return array
    */


function selectdata($column, $cond, $search,$start="",$limit=""){
      $limitcond='';
    $condStrArr = array();
    if(is_array($cond) && count($cond)){
      foreach($cond as $ind=>$val) {
        $condStrArr[] = "$ind='".$val."'";
      }
    }

    dd($condStrArr);
    $condStr = implode(" and ", $condStrArr);
    if(count($condStrArr)){
             $condStr="and ".$condStr;  
    }
    if($limit!='' || $start!=''){
      //if($start==''){$start=0;}
      $limitcond=' LIMIT '.$start.','.$limit;
    }
    $sql = "select $column from ".$this->table_name." where 1=1  $condStr $search $limitcond";
        $query_result=$this->db->query($sql);
    $result = $query_result->result();
      return $result;
 }


}
