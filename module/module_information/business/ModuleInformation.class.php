<?php
/*
   @ClassName : ModuleInformation
   @Copyright : PT Gamatechno Indonesia
   @Analyzed By : Dyan Galih <galih@gamatechno.com>
   @Author By : Dyan Galih <galih@gamatechno.com>
   @Version : 0.1
   @StartDate : 2011-01-01
   @LastUpdate : 2011-01-01
   @Description : Module Information
*/

class ModuleInformation extends Database
{
   protected $mSqlFile;

   function __construct ($connectionNumber=0)
   {
      $this->mSqlFile = 'module/'.Dispatcher::Instance()->mModule.'/business/module_information.sql.php';
      parent::__construct($connectionNumber);
   }

   public function GetModuleInformation($module,$subModule,$action,$type){
      $result = $this->Open($this->mSqlQueries['get_module_information'], array($module,$subModule,$action,$type));
      return $result[0];
   }

}
?>