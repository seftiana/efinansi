<?php

class AppGroup extends Database {

   protected $mSqlFile= 'module/user/business/appgroup.sql.php';

   function __construct($connectionNumber=0) {
		parent::__construct($connectionNumber);

   }

//===GET===
   function GetDataGroupById($groupId) {
      $result = $this->GetAllDataAsArray($this->mSqlQueries['get_data_group_by_id'], array($groupId));
      return $result;
   }

   function GetDataGroup($groupName, $withPrivilege= false) {
      if ($withPrivilege){
         $result = $this->Open($this->mSqlQueries['get_data_group_with_privilege'], array('%'.$groupName.'%'));
      } else {
         $result = $this->Open($this->mSqlQueries['get_data_group'], array('%'.$groupName.'%'));
      }
      return $result;
   }

   function GetLastGroupId() {
      $result = $this->GetAllDataAsArray($this->mSqlQueries['get_last_group_id'], array());
      return $result;
   }

//===DO===
   function DoAddGroup($groupName, $description) {
      return $this->ExecuteInsertQuery($this->mSqlQueries['do_add_group'], array($groupName, $description));
   }

   function DoUpdateGroup($groupName, $description, $groupId) {
      $result = $this->Execute($this->mSqlQueries['do_update_group'], array($groupName, $description, $groupId));
      return $result;
   }

   function DoDeleteGroup($groupId) {
      $result = $this->Execute($this->mSqlQueries['do_delete_group'], array($groupId));
      return $result;
   }
}
?>
