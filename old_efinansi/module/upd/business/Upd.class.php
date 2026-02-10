<?php

class Upd extends Database {

   protected $mSqlFile= 'module/upd/business/upd.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
   
   function GetComboUpd(){
      return $this->Open($this->mSqlQueries['get_combo_upd'], array());
   }

   function GetListUpd(){
      return $this->Open($this->mSqlQueries['get_list_upd'], array());
   }
   
   function GetUpdFromId($params){
      return $this->Open($this->mSqlQueries['get_upd_from_id'], array($params));
   }
}
?>
