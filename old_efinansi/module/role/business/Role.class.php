<?php

class Role extends Database {

   protected $mSqlFile= 'module/role/business/role.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
   
   function GetComboRole(){
      return $this->Open($this->mSqlQueries['get_combo_role'], array());
   }
}
?>
