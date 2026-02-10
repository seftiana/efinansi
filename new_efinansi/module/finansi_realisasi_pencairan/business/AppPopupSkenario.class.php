<?php
/**
* ================= doc ====================
* FILENAME     : AppPopupSkenario.class.php
* @package     : AppPopupSkenario
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-01
* @Modified    : 2015-04-01
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

class AppPopupSkenario extends Database
{
   # internal variables
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/finansi_realisasi_pencairan/business/app_popup_skenarion.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      parent::__construct($connectionNumber);
   }

   public function Count()
   {
      $return     = $this->Open($this->mSqlQueries['count'], array());
      if($return) {
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function getData($param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data'], array(
         '%'.$param['nama'].'%'
      ));

      return $return;
   }
}
?>