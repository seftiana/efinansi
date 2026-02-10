<?php

class AppTipeunit extends Database {

   protected $mSqlFile= 'module/tipeunit/business/apptipeunit.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }

   function GetError() {
		$errno = mysql_errno();
		if($errno == "1451") {
			$return = "Terdapat data lain yang menggunakan data ini.";
		}
		return $return;
   }
      
   function GetDataTipeunit ($offset, $limit, $tipeunit='') {
	   //$this->setDebugOn();
      $sql = sprintf($this->mSqlQueries['get_data_tipeunit'], '%s','%d','%d');
      $result = $this->Open($sql, array('%'.$tipeunit.'%', $offset, $limit));
	  //$debug = sprintf($sql, '%'.$tipeunit.'%', $offset, $limit);
	  //echo $debug;
      return $result;
   }

   function GetCountDataTipeunit ($tipeunit='') {
      $result = $this->Open($this->mSqlQueries['get_count_data_tipeunit'], array('%'.$tipeunit.'%'));      
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
    
   function GetDataTipeunitById($tipeunitId) {
      $result = $this->Open($this->mSqlQueries['get_data_tipeunit_by_id'], array($tipeunitId));  
      return $result;
   }

//===DO==
   
   function DoAddTipeunit($tipeunitNama) {
      $result = $this->Execute($this->mSqlQueries['do_add_tipeunit'], array($tipeunitNama));
      return $result;
   }
   
   function DoUpdateTipeunit($tipeunitNama, $tipeunitId) {
      $result = $this->Execute($this->mSqlQueries['do_update_tipeunit'], array($tipeunitNama, $tipeunitId));
	  //$debug = sprintf($this->mSqlQueries['do_update_tipeunit'], $tipeunitNama, $tipeunitId);
	  //echo $debug;
      return $result;
   }
   
   function DoDeleteTipeunitById($tipeunitId) {
      $result=$this->Execute($this->mSqlQueries['do_delete_tipeunit_by_id'], array($tipeunitId));
	  //print_r($_GET);
	//  $debug = sprintf($this->mSqlQueries['do_delete_tipeunit'], $tipeunitId);
	  //echo $debug;
//	  echo mysql_error();
//	  echo $result;
//	  echo mysql_errno();
//	  exit();
      return $result;
   }

	function DoDeleteTipeunitByArrayId($arrTipeunitId) {
		$propinsiId = implode("', '", $arrTipeunitId);
		$result=$this->Execute($this->mSqlQueries['do_delete_tipeunit_by_array_id'], array($arrTipeunitId));
//	  echo mysql_error();
//	  echo $result;
//	  echo mysql_errno();
//	  exit();
		return $result;
	}
	
	function CekDataTipeunit ($tipeunit) {
	   //$this->setDebugOn();
      $sql = sprintf($this->mSqlQueries['cek_data_tipeunit'], '%s');
      $result = $this->Open($sql, array('%'.$tipeunit.'%'));
	  //$debug = sprintf($sql, '%'.$tipeunit.'%', $offset, $limit);
	  //echo $debug;
      return $result[0];
   }
}
?>
