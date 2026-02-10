<?php

class SumberDana extends Database {

   protected $mSqlFile= 'module/pagu_anggaran_unit/business/sumberdana.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataSumberDana ($offset, $limit, $nama='') {
      $result = $this->Open($this->mSqlQueries['get_data_sumber_dana'], array('%'.$nama.'%', $offset, $limit));
      return $result;
   }

   function GetCountDataSumberDana ($nama='') {
      $result = $this->Open($this->mSqlQueries['get_count_data_sumber_dana'], array('%'.$nama.'%'));
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
   
}
?>
