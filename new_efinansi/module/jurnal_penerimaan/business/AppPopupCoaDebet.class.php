<?php
class Popup extends Database {

   protected $mSqlFile= 'module/jurnal_penerimaan/business/apppopupcoadebet.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);
   }
   
   function GetPupUpCoaDebet($key, $offset, $limit){ 
      /*switch($type) {
         case 'debet':
            $type ='1';
         break;
         
         case 'kredit':
            $type ='0';
         break;
         
         case 'all':
            $type ='%%';
         break;
      }*/
      $ret = $this->open($this->mSqlQueries['get_popup_coa_debet'],array('%'.$key.'%', '%'.$key.'%', $offset, $limit));          
      return $ret;
   }
   
   function GetCount($key) {
      /*switch($type) {
         case 'debet':
            $type ='1';
         break;
         
         case 'kredit':
            $type ='0';
         break;
         
         case 'all':
            $type ='%%';
         break;
      }*/
      $ret = $this->open($this->mSqlQueries['get_count'],array('%'.$key.'%', '%'.$key.'%'));
      return $ret[0]['total'];
   }
}

?>