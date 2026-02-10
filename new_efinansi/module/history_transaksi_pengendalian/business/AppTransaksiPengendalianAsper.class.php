<?php

class AppTransaksiPengendalianAsper extends Database {

   protected $mSqlFile= 'module/transaksi_pengendalian/business/transpengendalianasper.sql.php';
   
   function __construct($connectionNumber=1) {
      parent::__construct($connectionNumber);
      //$this->mrDbEngine->debug = 1;
   }
   
   function ComboStatusBrg() {
      return $this->Open($this->mSqlQueries['combo_status_brg'], array());
   }
   
   function CountDataPengendalian($key, $tgl_awal, $tgl_akhir) {
      $sql_add = '';
      if(isset($_POST)) {
         if($_POST['status_brg'] != 'all')
            $sql_add .= " AND pengBrgStatusId = '" . $_POST['status_brg'] . "'";
         if($_POST['unitkerja'] != '')
            $sql_add .= " AND pengBrgUnitId = '" .$_POST['unitkerja']  . "'";
      }
      $sql = sprintf($this->mSqlQueries['count_pengendalian'], '%%'.$key.'%%', '%%'.$key.'%%', $tgl_awal, $tgl_akhir, $sql_add);
      $result = $this->Open($sql, array($_POST['status_brg'], $_POST['unitkerja']));
      #$result = $this->Open($this->mSqlQueries['count_pengendalian'], array('%'.$key.'%', '%'.$key.'%', $tgl_awal, $tgl_akhir));
      return $result[0]['total'];
   }
   
   function GetListPengendalian($key, $tgl_awal, $tgl_akhir, $offset, $limit) {
      $sql_add = '';
      if(isset($_POST)) {
         if($_POST['status_brg'] != 'all')
            $sql_add .= " AND pengBrgStatusId = '" . $_POST['status_brg'] . "'";
         if($_POST['unitkerja'] != '')
            $sql_add .= " AND pengBrgUnitId = '" .$_POST['unitkerja']  . "'";
      }
      $sql = sprintf($this->mSqlQueries['get_list_data_pengendalian'], '%%'.$key.'%%', '%%'.$key.'%%', $tgl_awal, $tgl_akhir, $sql_add, $offset, $limit);
      $result = $this->Open($sql, array($_POST['status_brg'], $_POST['unitkerja']));
      #
      #echo $this->GetLastError();
      return $result;
   }
   
   function GetDetilPengendalianBrg($id, $status) {
      return $this->Open($this->mSqlQueries['detil_pengendalian'], array($id, $status));
   }
   
}
?>
