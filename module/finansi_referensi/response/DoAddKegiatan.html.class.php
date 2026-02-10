<?php
#doc
# package:     DoAddKegiatan
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-09-13
# @Modified    2013-09-13
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';

class DoAddKegiatan extends HtmlResponse
{
   #   internal variables
   private $mObj;
   protected $_POST;
   protected $_GET;
   #   Constructor
   function __construct ()
   {
      $this->mObj       = new FinansiReferensi();
      if(is_object($_POST)){
         $this->_POST   = $_POST->AsArray();
      }else{
         $this->_POST   = $_POST;
      }
      if(is_object($_GET)){
         $this->_GET    = $_GET->AsArray();
      }else{
         $this->_GET    = $_GET;
      }
   }
}
?>