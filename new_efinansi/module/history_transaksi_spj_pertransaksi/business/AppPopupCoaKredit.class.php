<?php
class Popup extends Database {

   protected $mSqlFile= 'module/history_transaksi_spj_pertransaksi/business/apppopupcoakredit.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);
   }
   
   function GetPupUpCoaDebet($key, $offset, $limit){ 
      $ret = $this->open($this->mSqlQueries['get_popup_coa_kredit'],array('%'.$key.'%', '%'.$key.'%', $offset, $limit));          
      return $ret;
   }
   
   function GetCount($key) {
      $ret = $this->open($this->mSqlQueries['get_count'],array('%'.$key.'%', '%'.$key.'%'));
      return $ret[0]['total'];
   }
}

?>