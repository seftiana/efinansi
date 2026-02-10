<?php

class CoaTipe extends Database {

   protected $mSqlFile= 'module/coa_tipe/business/coatipe.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }

   function GetComboCoaTipe(){
      return $this->Open($this->mSqlQueries['get_combo_coa_tipe'], array());
   }
   
   function GetListCoaTipe(){
      return $this->Open($this->mSqlQueries['get_list_coa_tipe'], array());
   }
   
   function GetCoaTipeFromId($params){
      return $this->Open($this->mSqlQueries['get_coa_tipe_from_id'], array($params));
   }
}
?>
