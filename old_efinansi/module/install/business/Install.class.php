<?php
/*
   @ClassName : Install
   @Copyright : PT Gamatechno Indonesia
   @Analyzed By : Nanang Ruswianto <nanang@gamatechno.com>
   @Author By : Dyan Galih <galih@gamatechno.com>
   @Version : 0.1
   @StartDate : 2010-10-25
   @LastUpdate : 2010-10-25
   @Description : Install
*/

class Install extends Database
{
   protected $mSqlFile;

   function __construct ($connectionNumber=0)
   {
      $this->mSqlFile = 'module/install/business/install.sql.php';
      parent::__construct($connectionNumber);
   }

   public function CheckTriggers(){
      $result = $this->Open($this->mSqlQueries['check_triggers'], array());
      return $result;
   }

   public function GetModule(){
      $result = $this->Open($this->mSqlQueries['get_data_module'], array());
      return $result;
   }

   public function GetCountDataReferensi(){
      $result = $this->Open($this->mSqlQueries['check_count_referensi'], array());
      for ($i = 0; $i < count($result)-1; $i++)
      {
         $newSql .= $result[$i]['DYN_QUERY'] . " UNION ";
      }

      $newSql .= $result[$i]['DYN_QUERY'];

      $result = $this->Open($newSql, array());
      return $result;
   }

}
?>