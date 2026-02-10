<?php

class TahunPembukuanPeriode extends Database {

   protected $mSqlFile= 'module/tahun_pembukuan/business/tahun_pembukuan_periode.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);     
   }

   function GetTahunPembukuanPeriodeAktif() {
      return $this->Open($this->mSqlQueries['get_tahun_pembukuan_periode_aktif'], array());
   }

   public function GetTahunPembukuanPeriodeSelected($tanggalAwal, $tanggalAkhir) {
      return $this->Open($this->mSqlQueries['get_tahun_pembukuan_periode_selected'], array(
         date('Y-m-d', strtotime($tanggalAwal)),
         date('Y-m-d', strtotime($tanggalAkhir))
      ));
   }

   function SetNonAktifTahunPembukuanPeriode($params) {
      return $this->Execute($this->mSqlQueries['set_non_aktif_tahun_pembukuan_periode'], array($params));
   }

   function InsertTahunPembukuanPeriode($params) {
      return $this->Execute($this->mSqlQueries['insert_tahun_pembukuan_periode'], $params);
   }

	public function UpdatTransaksiKeTppBaru($taAktifBaru, $taAktifLama){
      $result = true;
      $result = $this->Execute($this->mSqlQueries['update_data_sppu'], array($taAktifBaru, $taAktifLama));
      $result &= $this->Execute($this->mSqlQueries['update_data_transaksi'], array($taAktifBaru, $taAktifLama));
      $result &= $this->Execute($this->mSqlQueries['delete_tpp_aktif_lama'], array( $taAktifLama));

      return $result;
	}

   function UpdateTahunPembukuan($params,$is_debet) {
      if ($is_debet=='1'){
         return $this->Execute($this->mSqlQueries['update_debet_tahun_pembukuan'], $params);
      }else{
         return $this->Execute($this->mSqlQueries['update_kredit_tahun_pembukuan'], $params);
      }
   }

   function GetTahunPembukuanIsRolledback() {
      return $this->Open($this->mSqlQueries['get_tahun_pembukuan_is_rolledback'], array());
   }

   function GetTahunPembukuanFromCoa($params) {
      return $this->Open($this->mSqlQueries['get_tahun_pembukuan_from_coa'], array($params));
   }
   
   function GetUnitKerja(){
      return $this->Open($this->mSqlQueries['get_unit_kerja'],array());
   }
   
   function GetCountTahunPembukuan(){
      $result = $this->Open($this->mSqlQueries['get_count_tahun_pembukuan'],array());
      return $result['0']['total'];
   }
}
?>
