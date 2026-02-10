<?php

class RkaklOutput extends Database 
{

   protected $mSqlFile= 'module/program_kegiatan/business/rkakloutput.sql.php';
   
   public function __construct($connectionNumber=0) 
   {
      parent::__construct($connectionNumber);       
   }
      
   public function GetData($offset, $limit, $kode='', $nama='') 
   {
      $result = $this->Open($this->mSqlQueries['get_data'], 
							array(
									$kode.'%', 
									'%'.$nama.'%', 
									$offset, 
									$limit)
								 );
      return $result;
   }

   public function GetCountData() 
   {
      $result = $this->Open($this->mSqlQueries['get_count_data'], array());
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
   
}

?>