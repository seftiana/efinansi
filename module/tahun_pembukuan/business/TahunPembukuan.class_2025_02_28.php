<?php

class TahunPembukuan extends Database {

   protected $mSqlFile;
   public $_POST;
   public $_GET;
   public $method;
   protected $mUserId   = null;
   # subaccount
   public $subAccName;
   public $subAccJml;

   function __construct($connectionNumber=0) {
      $this->_POST         = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET          = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method        = $_SERVER['REQUEST_METHOD'];
      $this->mSqlFile      = 'module/tahun_pembukuan/business/tahun_pembukuan.sql.php';
      parent::__construct($connectionNumber);
      $this->subAccName    = array('Pertama','Kedua','Ketiga','Keempat','Kelima','Keenam','Ketujuh');
      $this->subAccJml     = ((GTFWConfiguration::GetValue('application','subAccJml') == NULL) ? 7 : GTFWConfiguration::GetValue('application','subAccJml'));
   }

   private function setUserId()
   {
      if(class_exists('Security')){
         $this->mUserId    = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      }
   }

   public function getUserId()
   {
      $this->setUserId();
      return (int)$this->mUserId;
   }

   function GetListCoaAsTahunPembukuan($idUnitKerja='1') {
      return $this->Open($this->mSqlQueries['get_list_coa_as_tahun_pembukuan'], array());
   }

   function GetBalancePembukuanCoa($params) {
      $sql = $this->mSqlQueries['get_balance_pembukuan_coa'];

        if($this->subAccJml > 0){
           for($i=0;$i<=($this->subAccJml-1);$i++){
              $arrView[$i] = 'tpSubacc'.$this->subAccName[$i].'Kode';
           }
           $addSqlView = ',CONCAT('.implode(",'-',",$arrView).') AS subacc';
           $sql = str_replace('[SUBACC_VIEW]', $addSqlView, $sql);
        }else $sql = str_replace('[SUBACC_VIEW]', '', $sql);

      $result = $this->Open($sql, array($params));
      #echo $this->GetLastError();
      return $result;
   }

   function GetBalancePembukuanSubAccCoa($params,$subAcc='') {
        $arrSubAcc= array();
        $arrFilter ='';
        $sql = $this->mSqlQueries['get_balance_pembukuan_sub_acc_coa'];
        if((int)$this->subAccJml > 0){
            if(!empty($subAcc)) {
                $arrSubAcc = explode('-',$subAcc);
            }         
            for($i=0;$i<=($this->subAccJml-1);$i++){
               $arrView[$i] = 'tpSubacc'.$this->subAccName[$i].'Kode';
               if(count($arrSubAcc)>0) {
                   $arrFilter .= ' AND tpSubacc'.$this->subAccName[$i]."Kode = '".$arrSubAcc[$i]."'";
               }
            }
            $addSqlView = ',CONCAT('.implode(",'-',",$arrView).') AS subacc';
            $sql = str_replace('[SUBACC_VIEW]', $addSqlView, $sql);
            $sql = ($arrFilter <> '') ? str_replace('[FILTER_SUBACC]', $arrFilter, $sql) : str_replace('[FILTER_SUBACC]', '', $sql);
        }else{
            $sql = str_replace('[SUBACC_VIEW]', '', $sql);
            $sql = str_replace('[FILTER_SUBACC]', '', $sql);
        }

      $result = $this->Open($sql, array($params,$params));
      #echo $this->GetLastError();
      return $result;
   }

   function GetCheckBalance(){

      //$result = $this->Open($this->mSqlQueries['check_balance'],array());
      $aktiva = $this->GetAktiva();
      $pasiva = $this->GetPasiva();  
      if((string)$aktiva == (string)$pasiva){
         return true;
      }else{
         return false;
      }
   }

   function GetAktiva() {
      #
      $rs = $this->Open($this->mSqlQueries['get_aktiva'], array('Aktiva'));

     # echo $this->GetLastError();
      return $rs[0]['jumlah'];
   }

   function GetKewajiban() {
      $rs = $this->Open($this->mSqlQueries['get_kewajiban'], array('Pasiva'));
      #
      #echo $this->GetLastError();
      return $rs[0]['jumlah'] ;
   }

   function GetModal() {
      $rs = $this->Open($this->mSqlQueries['get_modal'], array('Modal'));
      #
      #echo $this->GetLastError();
      return $rs[0]['jumlah'] ;
   }
   
   function GetPasiva() {
      $k = $this->GetKewajiban();
      $m = $this->GetModal();
       return strval($k) + strval($m);
   }

   function GetDataKodeAkun(){
         return $this->Open($this->mSqlQueries['get_data_kode_akun'], array());
   }

   function GetCountTransaksiNotJurnal() {
      $rs = $this->Open($this->mSqlQueries['get_count_transaksi_not_jurnal'], array());
      return $rs[0]['jumlah'];
   }

   function GetCountJurnalNotPosting() {
      $rs = $this->Open($this->mSqlQueries['get_count_jurnal_not_posting'], array());
      return $rs[0]['jumlah'];
   }

   function InsertTahunPembukuan($params,$subAcc='') {
 
         $sql = $this->mSqlQueries['insert_tahun_pembukuan'];
         if(!empty($subAcc)){
            $arrSubAcc = explode('-',$subAcc); $i=0;
            foreach($arrSubAcc as $val){
               $addSql .= ',`tpSubacc'.$this->subAccName[$i]."Kode` = '".$val."'";
               $i++;
            }
            $sql = str_replace('[INSERT_SUBACC]', $addSql, $sql);
         }else{$sql = str_replace('[INSERT_SUBACC]','', $sql);}

         $result = $this->Execute($sql, $params);

      return $result;
   }

   function UpdateTahunPembukuan($params,$subAcc='') {
         $sql = $this->mSqlQueries['update_tahun_pembukuan'];
         if(!empty($subAcc)){
            $arrSubAcc = explode('-',$subAcc); $i=0;
            foreach($arrSubAcc as $val){
               $addSql .= ' AND `tpSubacc'.$this->subAccName[$i]."Kode` = '".$val."'";
               $i++;
            }
            $sql = str_replace('[FILTER_SUBACC]', $addSql, $sql);
         }else{$sql = str_replace('[FILTER_SUBACC]','', $sql);}
      return $this->Execute($sql, $params);
   }

   function GetTahunPembukuanFromCoa($params,$subAcc='') {

         $sql = $this->mSqlQueries['get_tahun_pembukuan_from_coa'];
      if($this->subAccJml > 0){
         if(!empty($subAcc)) $arrSubAcc = explode('-',$subAcc);
            for($i=0;$i<=($this->subAccJml-1);$i++){
               $arrView[$i] = 'tpSubacc'.$this->subAccName[$i].'Kode';
               if(count($arrSubAcc)>0) @$arrFilter .= ' AND tpSubacc'.$this->subAccName[$i].'Kode = "'.$arrSubAcc[$i].'"';
            }
            $addSqlView = ',CONCAT('.implode(",'-',",$arrView).') AS subacc';
            $sql = str_replace('[SUBACC_VIEW]', $addSqlView, $sql);
            $sql = ($arrFilter <> '') ? str_replace('[FILTER_SUBACC]', $arrFilter, $sql) : str_replace('[FILTER_SUBACC]', '', $sql);
      }else{
            $sql = str_replace('[SUBACC_VIEW]', '', $sql);
            $sql = str_replace('[FILTER_SUBACC]', '', $sql);
            }

         return $this->Open($sql, array($params));
   }

   function GetTahunPembukuanById($tpId){
         $sql = $this->mSqlQueries['get_tahun_pembukuan_by_id'];
         if($this->subAccJml > 0){
            for($i=0;$i<=($this->subAccJml-1);$i++){
               $arrView[$i] = 'tpSubacc'.$this->subAccName[$i].'Kode';
            }
            $addSqlView = ',CONCAT('.implode(",'-',",$arrView).') AS subacc';
            $sql = str_replace('[SUBACC_VIEW]', $addSqlView, $sql);
         }else $sql = str_replace('[SUBACC_VIEW]', '', $sql);
         $result = $this->Open($sql, array($tpId));
         return $result;
   }

   function GetJumlahTransaksiNotJurnal() {
      $rs = $this->Open($this->mSqlQueries['get_jumlah_transaksi_not_jurnal'], array());
      return $rs[0]['jumlah'];
   }

   function GetJumlahJurnalNotPosting() {
      $rs = $this->Open($this->mSqlQueries['get_jumlah_jurnal_not_posting'], array());
      return $rs[0]['jumlah'];
   }

   function GetBukuBesarAsTahunPembukuan($coaId,$tpId) {

         $sql = $this->mSqlQueries['get_buku_besar_as_tahun_pembukuan'];
         if($this->subAccJml > 0){
            for($i=0;$i<=($this->subAccJml-1);$i++){
               $arrView[$i] = 'bbh.bbhisSubacc'.$this->subAccName[$i].'Kode';
            }
            $addSqlView = ',CONCAT('.implode(",'-',",$arrView).') AS subacc';
            $addSqlGrp = ','.implode(',', $arrView);
            $sql = str_replace('[SUBACC_VIEW]', $addSqlView, $sql);
            $sql = str_replace('[SUBACC_GROUP]', $addSqlGrp, $sql);
         }else{
            $sql = str_replace('[SUBACC_VIEW]', '', $sql);
            $sql = str_replace('[SUBACC_GROUP]', '', $sql);
         }

         return $this->Open($sql, array($coaId,$tpId));
   }

   function GetAllCoaAsTahunPembukuan() {
      return $this->Open($this->mSqlQueries['get_all_coa_as_tahun_pembukuan'], array());
   }

   function UpdateTahunPembukuanByCoaAsTutupBuku($params,$subAcc='') {

         $sql = $this->mSqlQueries['update_tahun_pembukuan_by_coa_id_as_tutup_buku'];
         if(!empty($subAcc)){
            $arrSubAcc = explode('-',$subAcc); $i=0;
            foreach($arrSubAcc as $val){
               $addSql .= ' AND `tpSubacc'.$this->subAccName[$i].'Kode` = "'.$val.'"';
               $i++;
            }
            $sql = str_replace('[FILTER_SUBACC]', $addSql, $sql);
         }else{$sql = str_replace('[FILTER_SUBACC]','', $sql);}

      return $this->Execute($sql, $params);
   }

   function InsertTahunPembukuanHistory($params) {
      return $this->Execute($this->mSqlQueries['insert_tahun_pembukuan_history'], $params);
   }

   function GetAllCoaBukuBesar($params) {
      return $this->Open($this->mSqlQueries['get_all_coa_buku_besar'], array($params));
   }

   function InsertTahunPembukuanHistoryAsTutupBuku($params,$subAcc=''){

         $sql = $this->mSqlQueries['insert_tahun_pembukuan_history_as_tutup_buku'];
         if(!empty($subAcc)){
            $arrSubAcc = explode('-',$subAcc); $i=0;
            foreach($arrSubAcc as $val){
               $addSql .= ',`tphSubacc'.$this->subAccName[$i].'Kode` = "'.$val.'"';
               $i++;
            }
            $sql = str_replace('[INSERT_SUBACC]', $addSql, $sql);
         }else{$sql = str_replace('[INSERT_SUBACC]','', $sql);}

         return $this->Execute($sql, $params);
   }

   function InsertTahunPembukuanAsBukaBuku($params,$subAcc='') {

        $sql = $this->mSqlQueries['insert_tahun_pembukuan_as_buka_buku'];
        if(!empty($subAcc)){
         $arrSubAcc = explode('-',$subAcc); $i=0;
         foreach($arrSubAcc as $val){
            $addSql .= ',`tpSubacc'.$this->subAccName[$i]."Kode` = '".$val."'";
            $i++;
         }
         $sql = str_replace('[INSERT_SUBACC]', $addSql, $sql);
        }else{$sql = str_replace('[INSERT_SUBACC]','', $sql);}

      return $this->Execute($sql, $params);
   }

   function UpdateTahunPembukuanAsBukaBuku($params) {
      return $this->Execute($this->mSqlQueries['update_tahun_pembukuan_as_buka_buku'], $params);
   }

   function IsCoaTahunPembukuanExist($params) {
      return $this->Open($this->mSqlQueries['is_coa_tahun_pembukuan_exist'], array($params));
   }

   #tambahan untuk insert posisi saldo buku besar ke tahun_pembukuan
   function CekCoaTahunPembukuan($coa_id,$subAcc='') {

         $sql = $this->mSqlQueries['cek_coa_thn_pembukuan'];
         if(!empty($subAcc)){
            $arrSubAcc = explode('-',$subAcc); $i=0;
            foreach($arrSubAcc as $val){
               $addSql .= ' AND `tpSubacc'.$this->subAccName[$i]."Kode` = '".$val."'";
               $i++;
            }
            $sql = str_replace('[FILTER_SUBACC]', $addSql, $sql);
         }else{$sql = str_replace('[FILTER_SUBACC]','', $sql);}
         $result =  $this->Open($sql, array($coa_id));

         return $result[0]['id'];
   }

   function InsertTahunPembukuanByCoaAsTutupBuku($params,$subAcc='') {

         $sql = $this->mSqlQueries['insert_tahun_pembukuan_by_coa_id_as_tutup_buku'];
         if(!empty($subAcc)){
            $arrSubAcc = explode('-',$subAcc); $i=0;
            foreach($arrSubAcc as $val){
               $addSql .= ',`tpSubacc'.$this->subAccName[$i]."Kode` = '".$val."'";
               $i++;
            }
            $sql = str_replace('[INSERT_SUBACC]', $addSql, $sql);
         }else{$sql = str_replace('[INSERT_SUBACC]','', $sql);}

         return $this->Execute($sql, $params);
   }

   function DeleteTahunPembukuanById($tpId){
      return $this->Execute($this->mSqlQueries['delete_tahun_pembukuan_by_id'],array($tpId));
   }


   // get laba rugi tahun berjalan
   public function getLabaRugiTahunBerjalanPembukuanAktif() {
      $rs = $this->Open($this->mSqlQueries['get_laba_rugi_tahun_berjalan_pembukuan_aktif'], array());
      $dataLR = array();
      if(!empty($rs)){
         foreach($rs as $value){
            $dataLR[$value['subAcc']] = $value;
         }
      }

      return $dataLR;
   }
   
   public function getLabaRugiAwalTahunPembukuanAktif() {
      $rs = $this->Open($this->mSqlQueries['get_laba_rugi_awal_tahun_pembukuan_aktif'], array());
      return $rs[0];
   }
   
   public function getLabaRugiAwalTahunPembukuanAktifSaldoAwal() {
      $rs = $this->Open($this->mSqlQueries['get_laba_rugi_awal_tahun_pembukuan_aktif_saldo_awal'], array());
      
      $dataLR = array();
      if(!empty($rs)){
         foreach($rs as $value){
            $coaId = $value['coa_id'];
            $dataLR[$value['subAcc']] = $value;
         }
         $dataLR['default'] = array('coa_id' => $coaId,'saldo_akhir'=>0);
      }
      return $dataLR;
   }
}
?>