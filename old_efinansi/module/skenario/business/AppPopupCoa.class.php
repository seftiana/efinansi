<?php
/**
* @module skenario
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/

class AppPopupCoa extends Database {

   protected $mSqlFile= 'module/skenario/business/apppopupcoa.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);     
   }
//==GET==   
  
   function GetDataCoa($startRec,$itemViewed,$nama) {        
		return $this->open($this->mSqlQueries['get_data_coa'],array('%'.$nama.'%',$startRec,$itemViewed));
   }
   
   function GetCountCoa($nama) {
      $ret = $this->open($this->mSqlQueries['get_count_coa'],array('%'.$nama.'%'));
	  if($ret)
	     return $ret[0]['total'];
	  else
	    return 0;
   }
   

//===DO==
   
   
}
?>
