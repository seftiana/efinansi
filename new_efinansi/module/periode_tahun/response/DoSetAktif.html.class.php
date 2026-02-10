<?php 
#doc
# package:     DoSetAktif
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-05-27
# @Modified    2013-05-27
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/periode_tahun/response/ProcessPeriodeTahun.proc.class.php';

class DoSetAktif extends HtmlResponse
{
   #   internal variables
   public $mObj;
   protected $_POST;
   protected $_GET;
   #   Constructor
   function __construct ()
   {
      $this->mObj       = new ProcessPeriodeTahun();
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
   
   public function ProcessRequest()
   {
      $urlRedirect      = $this->mObj->DoSetAktif();
      $this->RedirectTo($this->mObj->pageView);

      return false;
   }
}
?>