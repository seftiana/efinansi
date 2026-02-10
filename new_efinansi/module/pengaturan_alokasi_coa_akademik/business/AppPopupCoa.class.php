<?php

class AppPopupCoa extends Database {

   protected $mSqlFile= 'module/pengaturan_alokasi_coa_akademik/business/apppopupcoa.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);     
   }
//==GET==   
  
   function GetDataCoa($startRec,$itemViewed,$nama,$kode) {        
        #$tipe = (trim($tipe)=='debet') ? '1' : '0';				  
		$ret =  $this->open($this->mSqlQueries['get_data_coa'],array('%'.$nama.'%','%'.$kode.'%',$startRec,$itemViewed));				
		return $ret;
   }
   
   function GetCountCoa($nama,$kode) {
      #$tipe = (trim($tipe)=='debet') ? '1' : '0';
      $ret = $this->open($this->mSqlQueries['get_count_coa'],array('%'.$nama.'%','%'.$kode.'%'));
	  if($ret)
	     return $ret[0]['total'];
	  else
	    return 0;
   }
   

//===DO==
   
   
}
?>
