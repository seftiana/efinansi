<?php
class JenisIdentitas extends Database {
   
   protected $mSqlFile = 'module/components/business/jenis_identitas.sql.php';   
   
   function __construct($connectionNumber = 0) {
      parent::__construct($connectionNumber);           
   }
   
   function GetAllJenisIdentitas() {             
      return $this->Open($this->mSqlQueries['get_all_jenis_identitas'], array());
   }  
   
}
?>