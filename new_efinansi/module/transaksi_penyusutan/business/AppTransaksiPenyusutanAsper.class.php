<?php

class AppTransaksiPenyusutanAsper extends Database {

   protected $mSqlFile= 'module/transaksi_penyusutan/business/apptransaksipenyusutanasper.sql.php';

   function __construct($connectionNumber=1) {
      parent::__construct($connectionNumber);
      //$this->mrDbEngine->debug = 1;
#      $this->SetDebugOn();
   }

   function GetDataPenyusutanKib($key) {
      $result = $this->Open($this->mSqlQueries['get_data_penyusutan_kib'], array('%'.$key.'%','%'.$key.'%'));
      return $result;
   }

   function GetCountDetailPenyusutan($kib_id) {
      $result = $this->Open($this->mSqlQueries['get_count_detail_penyusutan'], array($kib_id));
      return $result[0]['total'];
   }

   function GetDetailPenyusutan($kib_id, $offset, $limit) {
       return $this->Open($this->mSqlQueries['get_detail_penyusutan'], array($kib_id,  $offset, $limit));
       //echo $this->GetLastError();
   }

   function GetDetailDataPenyusutan($kib_id) {
      return $this->Open($this->mSqlQueries['get_detail_data_penyusutan'], array($kib_id));
   }

   #untuk list detil penyusutan
   function GetComboJenisKib() {
      return $this->Open($this->mSqlQueries['get_combo_kib'], array());
   }

   function GetCountListPenyusutan($key) {
      $post = $_POST->AsArray();
      $sql_add = '';
      if(!empty($post)) {
         if($post['jenis_kib'] != 'all')
            $sql_add .= ' AND golbrgId = '.$post['jenis_kib'];
         if($post['unitkerja'] != '')
            $sql_add .= ' AND invUnitKerja = '.$post['unitkerja'];
         if($post['periode_penyusutan_mon'] != '') {
            $awal = $post['periode_penyusutan_year'].'-'.$post['periode_penyusutan_mon'].'-';
            $akhir = $post['periode_penyusutan_akhir_year'].'-'.$post['periode_penyusutan_akhir_mon'].'-';
            $sql_add .= " AND (left(penyusutanMstPeriode,8)  BETWEEN '".$awal."' AND '$akhir') ";
         } else {
            $sql_add .= ' AND penyusutanMstPeriode = (SELECT MAX(penyusutanMstPeriode) FROM penyusutan_mst) ';
         }
      }else {
         $sql_add .= ' AND penyusutanMstPeriode = (SELECT MAX(penyusutanMstPeriode) FROM penyusutan_mst) ';
      }
      $sql = sprintf($this->mSqlQueries['get_count_list_penyusutan'], '%'.$key.'%','%'.$key.'%', $sql_add,'%'.$key.'%','%'.$key.'%', $sql_add);
      $result = $this->Open($sql,array());

      return $result[0]['total'];
   }

   function GetListPenyusutan($offset,$limit,$key,$jenis_kib) {
      if($jenis_kib=='')
         $jenis_kib='all';
      $result = $this->Open($this->mSqlQueries['get_list_penyusutan'],array($jenis_kib,$jenis_kib,'%'.$key.'%','%'.$key.'%',$offset,$limit));
      return $result;
   }

   function GetListPenyusutanAll($key,$jenis_kib) {

      if($jenis_kib=='')
         $jenis_kib='all';
      $result = $this->Open($this->mSqlQueries['get_list_penyusutan_all'],array($jenis_kib,$jenis_kib,'%'.$key.'%','%'.$key.'%'));
      return $result;
   }

   public function GetCount(){
      $result = $this->Open($this->mSqlQueries['get_search_count'], array());
      return $result[0]['total'];
   }

   function GetCountLogPenyusutan($inv_id) {
      $result = $this->Open($this->mSqlQueries['count_log_penyusutan'], array($inv_id, $inv_id));
      return $result[0]['total'];
   }

   function GetLogPenyusutan($offset, $limit, $inv_id) {
      return $this->Open($this->mSqlQueries['log_penyusutan'], array($inv_id, $inv_id, $offset, $limit));
   }



   function GetCetakListPenyusutan($tgl_awal, $tgl_akhir) {
      #$tes = sprintf($this->mSqlQueries['get_cetak_penyusutan'], $tgl_awal, $tgl_akhir);
      #echo 'tes'.$tes;
      return $this->Open($this->mSqlQueries['get_cetak_penyusutan'], array($tgl_awal, $tgl_akhir));
   }

   function GetMaxIdPenyusutanMst() {
      $this->Connect();
      $result =  $this->Open($this->mSqlQueries['get_max_id_penyusutan_mst'], array());
      return $result[0]['max_id_penyusutan_mst'];
   }

   function CekPenyusutanSebelomnya($id_barang_ref) {
      $this->Connect();
      $result =  $this->Open($this->mSqlQueries['cek_penyusutan_sebelomnya'], array($id_barang_ref));
      return $result[0];
   }

   function CekPenyusutanGedungSebelomnya($id_gedung) {
      $this->Connect();
      $result =  $this->Open($this->mSqlQueries['cek_penyusutan_gedung_sebelomnya'], array($id_gedung));
      return $result[0];
   }

   function DoAddPenyusutanMst($periode, $no_ba, $ket) {
      $this->Connect();
      $result = $this->Execute($this->mSqlQueries['insert_penyusutan_mst'], array($periode, $no_ba, $ket));
      return $result;
   }

   function DoAddPenyusutanDet($id_peny_mst, $id_inv_det, $nilai_akhir, $umr_ek) {
      $this->Connect();
      return $this->Execute($this->mSqlQueries['insert_penyusutan_det'], array($id_peny_mst, $id_inv_det, $nilai_akhir, $umr_ek));
   }

   function DoAddPenyusutanDetGedung($id_peny_mst, $id_gedung, $nilai_akhir, $umr_ek) {
      $this->Connect();
      return $this->Execute($this->mSqlQueries['insert_penyusutan_det_gedung'], array($id_peny_mst, $id_gedung, $nilai_akhir, $umr_ek));
   }

   function CekKib($id) {
      $this->Connect();
      $result =  $this->Open($this->mSqlQueries['cek_kib'], array($id));
      return $result[0]['kib'];
   }

   function UpdateDataPenyusutanMaster($kib_id){
   	return $this->Execute($this->mSqlQueries['update_data_penyusutan_mst'], array($kib_id));
   }

   function DoAddPenyusutanDetil($kib){
      $this->Connect();

      $result = $this->Execute($this->mSqlQueries['update_data_penyusutan_mst'],array($kib));
      if($result)
         $result = $this->Execute($this->mSqlQueries['insert_detil_penyusutan'],array($kib));

      return $result;
   }

   function DoAddPenyusutanGedungDetil(){
      $this->Connect();

      $result = $this->Execute($this->mSqlQueries['update_data_penyusutan_gedung_mst'],array());
      if($result)
         $result = $this->Execute($this->mSqlQueries['insert_detil_penyusutan_gedung'],array());

      return $result;
   }

   function GetDataPenyusutan($id){

      $result =  $this->Open($this->mSqlQueries['get_data_penyusutan'],array($id));

      return $result;
   }

   function GetDataPenyusutanGedung($id){
      return $this->Open($this->mSqlQueries['get_gedung_penyusutan'],array($id));
   }

}

?>