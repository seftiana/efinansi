<?php
class SearchFakultas extends Database{
	protected $mSqlFile= 'module/search_fakultas/business/searchfakultas.sql.php';
	function __construct($connectionNumber=0) {
		parent::__construct($connectionNumber);
		//
	}   
   
   function GetFakultas ()
   {
      return $this->Open($this->mSqlQueries['get_fakultas'], array());
   }
   
   function GetDataProdiAll ()
   {
      return $this->Open($this->mSqlQueries['get_data_prodi_all'], array());
   }
   
	function GetDataProdi () { 
      
      $result = $this->Open($this->mSqlQueries['get_data_prodi'], array());
      return $result;
	
	}
   
}
?>
