<?php
/**
* @module skenario
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/

class AppPopupCoa extends Database {

   protected $mSqlFile= 'module/finansi_coa_jenis_biaya/business/apppopupcoa.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);     
   }
//==GET==   
  
   function GetDataCoa($startRec,$itemViewed,$kode,$nama) {        
        #$tipe = (trim($tipe)=='debet') ? '1' : '0';				  
		$ret =  $this->open($this->mSqlQueries['get_data_coa'],array($kode.'%', '%'.$nama.'%',$startRec,$itemViewed));				
		return $ret;
   }
   
   function GetCountCoa($kode,$nama) {
      #$tipe = (trim($tipe)=='debet') ? '1' : '0';
      $ret = $this->open($this->mSqlQueries['get_count_coa'],array($kode.'%', '%'.$nama.'%'));
	  if($ret)
	     return $ret[0]['total'];
	  else
	    return 0;
   }
   
   function GetUnitkerjaById($unit_id) {
      #$tipe = (trim($tipe)=='debet') ? '1' : '0';
      $ret = $this->open($this->mSqlQueries['get_unitkerja_by_id'],array($unit_id));
	  if($ret)
	     return $ret[0];
	  else
	    return '';
   }
   

//===DO==
   
   
}
?>
