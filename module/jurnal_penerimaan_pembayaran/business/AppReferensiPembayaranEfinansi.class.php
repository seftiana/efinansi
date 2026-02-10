<?php


class AppReferensiPembayaranEfinansi extends Database
{
   
   protected $mSqlFile= 'module/jurnal_penerimaan_pembayaran/business/appreferensipembayaranefinansi.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);
   }
   
   function GetCoaDebet($key){ 
      $ret = $this->open($this->mSqlQueries['get_data_coa'],array($key));          
      return $ret;
   }

   
}
?>