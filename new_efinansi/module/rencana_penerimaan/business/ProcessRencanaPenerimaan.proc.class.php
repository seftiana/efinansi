<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rencana_penerimaan/business/AppRencanaPenerimaan.class.php';

class ProcessRencanaPenerimaan {

   // var $_POST;
   private $Obj;
   protected $mData  = array();
   public $pageView;
   public $pageInput;
   public $pageEdit;
   //css hanya dipake di view
   public $cssDone = "notebox-done";
   public $cssFail = "notebox-warning";

   private $return;
   private $decId;
   private $encId;
   private $perbulan;
   private $persenPerBulan;

   public function __construct() {
      $this->Obj     = new AppRencanaPenerimaan();
      $queryString   = $this->Obj->_getQueryString();
      $this->pageView   = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'RencanaPenerimaan',
         'view',
         'html'
      ).'&search=1&'.$queryString;

      $this->pageInput  = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'InputRencanaPenerimaan',
         'view',
         'html'
      ) . '&'.$queryString;

      $this->pageEdit   = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'EditRencanaPenerimaan',
         'view',
         'html'
      ).'&'.$queryString;

   }

   private function setData(){
      $requestData      = array();
      if($this->Obj->method == 'post'){
         $requestData['id']                  = $this->Obj->_POST['data_id'];
         $requestData['ta_id']               = $this->Obj->_POST['tahun_anggaran'];
         $requestData['unit_id']             = $this->Obj->_POST['unit_id'];
         $requestData['unit_nama']           = $this->Obj->_POST['unit_nama'];
         $requestData['alokasi_pusat_id']    = $this->Obj->_POST['alokasi_pusat_id'];
         $requestData['alokasi_pusat']       = $this->Obj->_POST['alokasi_pusat'];
         $requestData['alokasi_unit_id']     = $this->Obj->_POST['alokasi_unit_id'];
         $requestData['alokasi_unit']        = $this->Obj->_POST['alokasi_unit'];
         $requestData['kode_penerimaan_id']     = $this->Obj->_POST['kodepenerimaan_id'];
         $requestData['kode_penerimaan_kode']   = $this->Obj->_POST['kodepenerimaan_kode'];
         $requestData['kode_penerimaan_nama']   = $this->Obj->_POST['kodepenerimaan_nama'];
         $requestData['volume']              = $this->Obj->_POST['volume']; // volume satuan
         $requestData['satuan']              = $this->Obj->_POST['satuan'];
         $requestData['tarif']               = $this->Obj->_POST['tarif']; // nominal satuan
         $requestData['nominal']             = $this->Obj->_POST['totalterima'];
         $requestData['realisasi_pagu']      = $this->Obj->_POST['pagu']; // prosentase
         $requestData['nominal_pagu']        = $this->Obj->_POST['totalpagu'];
         $requestData['keterangan']          = $this->Obj->_POST['keterangan'];
         $requestData['sumber_dana_id']      = $this->Obj->_POST['sumber_dana'];
         $requestData['sumber_dana_nama']    = $this->Obj->_POST['sumber_dana_label'];
         $requestData['total_penerimaan']    = $this->Obj->_POST['totalpenerimaan'];
         $requestData['btn_toogle_state']    = $this->Obj->_POST['btn_toogle_state'];
         //print_r($requestData);
         // index [1,2,3,4,5,6,7,8,9,10,11,12]
         $requestData['rincian']             = array();
         $requestData['total_persen']        = $this->Obj->_POST['rincian']['persen_total'];
         $requestData['total_rincian']       = $this->Obj->_POST['rincian']['nominal_total'];
         $months     = $this->Obj->indonesianMonth;
         //print_r($requestData);
         foreach ($months as $mon) {
            if((int)$mon['id'] === 0){
               continue;
            }
            $rincian          = $this->Obj->_POST['rincian'][$mon['id']];
            if(empty($rincian)){
               $requestData['rincian'][$mon['id']]['id']       = $mon['id'];
               $requestData['rincian'][$mon['id']]['nama']     = $mon['name'];
               $requestData['rincian'][$mon['id']]['persen']   = 0;
               $requestData['rincian'][$mon['id']]['nominal']  = 0;
            }else{
               $requestData['rincian'][$mon['id']]    = $rincian;
            }
         }
      }

      $this->mData      = $requestData;
   }

   public function getData(){
      $this->setData();

      return (array)$this->mData;
   }

   public function checkData(){
      $requestData      = $this->mData;

      if(empty($requestData)){
         $err[]      = 'Tidak ada data yang akan diproses';
      }

      if($requestData['ta_id'] == ''){
         $err[]      = 'Definisikan tahun anggaran untuk rencana penerimaan';
      }

      if($requestData['unit_id'] == ''){
         $err[]      = 'Definisikan unit kerja';
      }

      if($requestData['kode_penerimaan_id'] == ''){
         $err[]      = 'Definisikan kode penerimaan';
      }

      if($requestData['volume'] == '' OR $requestData['volume'] <= 0){
         $err[]      = 'Isikan volume satuan rencana penerimaan';
      }

      if($requestData['satuan'] == ''){
         $err[]      = 'Definisikan satuan volume';
      }

      if($requestData['tarif'] == '' OR $requestData['tarif'] <= 0){
         $err[]      = 'Isikan nominal tarif untuk rencana penerimaan';
      }

      if($requestData['nominal'] == '' OR $requestData['nominal'] <= 0){
         $err[]      = 'Isikan nominal rencana penerimaan';
      }

      if($requestData['realisasi_pagu'] == '' OR $requestData['realisasi_pagu'] <= 0){
         $err[]      = 'Isikan prosentase alokasi pagu';
      }

      if($requestData['nominal_pagu'] == '' OR $requestData['nominal_pagu'] <= 0){
         $err[]      = 'Isikan nominal realisasi pagu';
      }

      if($requestData['total_penerimaan'] == '' OR $requestData['total_penerimaan'] <= 0){
         $err[]      = 'Isikan nominal total rencana penerimaan';
      }

      if($requestData['sumber_dana_id'] == ''){
         $err[]      = 'Isikan sumber dana untuk rencana penerimaan yang akan dibuat';
      }
/*
      if($requestData['total_persen'] <> 0 AND $requestData['total_rincian'] <> $requestData['total_penerimaan']){
         $err[]      = 'Pastikan rincian penerimaan per bulan sesuai. Total rincian <strong>'.$requestData['total_persen'].'%</strong> dari <strong>Rp. '.number_format($requestData['total_penerimaan'], 0, ',','.').';-</strong>';
      }*/
      if( $requestData['total_rincian']  > 0 AND $requestData['total_rincian'] <> $requestData['total_penerimaan']){
         $err[]      = 'Pastikan rincian penerimaan per bulan sesuai. Total rincian <strong>'.$requestData['total_persen'].'%</strong> dari <strong>Rp. '.number_format($requestData['total_penerimaan'], 0, ',','.').';-</strong>';
      }
      //var_dump((float) $requestData['total_persen']);
      if(isset($err)){
         $return['message']   = $err[0];
         $return['result']    = false;
      }else{
         $return['message']   = NULL;
         $return['result']    = true;
      }

      return $return;
   }

   public function Add() {
      $this->setData();
      $checkData           = $this->checkData();
      if($checkData['result'] === false){
         $return['url']       = $this->pageInput;
         $return['message']   = $checkData['message'];
         $return['style']     = $this->cssFail;
         $return['data']      = $this->Obj->_POST;
      }else{
         $process             = $this->Obj->doSaveRencanaPenerimaan($this->mData);
         if($process === true){
            $return['url']       = $this->pageView;
            $return['message']   = 'Proses penyimpanan data berhasil';
            $return['style']     = $this->cssDone;
            $return['data']      = $this->Obj->_POST;
         }else{
            $return['url']       = $this->pageInput;
            $return['message']   = 'Proses penyimpanan data gagal';
            $return['style']     = $this->cssFail;
            $return['data']      = $this->Obj->_POST;
         }
      }

      return (array)$return;
   }

   public function Update() {
      $this->setData();
      $checkData           = $this->checkData();
      if($checkData['result'] === false){
         $return['url']       = $this->pageEdit;
         $return['message']   = $checkData['message'];
         $return['style']     = $this->cssFail;
         $return['data']      = $this->Obj->_POST;
      }else{
         $process             = $this->Obj->doUpdateRencanaPenerimaan($this->mData);
         if($process === true){
            $return['url']       = $this->pageView;
            $return['message']   = 'Proses update data berhasil';
            $return['style']     = $this->cssDone;
            $return['data']      = $this->Obj->_POST;
         }else{
            $return['url']       = $this->pageEdit;
            $return['message']   = 'Proses update data gagal';
            $return['style']     = $this->cssFail;
            $return['data']      = $this->Obj->_POST;
         }
      }

      return (array)$return;
   }

   public function Delete() {
      $return['url']       = $this->pageView;
      $return['data']      = NULL;

      if($this->Obj->method != 'post'){
         $return['message']   = 'You don\'t have permission to access this page';
         $return['style']     = $this->cssFail;
      }else{
         $ids        = $this->Obj->_POST['idDelete'];
         $success    = 0;
         $failed     = 0;
         for ($i=0; $i < count($ids); $i++) {
            $process          = $this->Obj->doDeleteRencanaPenerimaan($ids[$i]);

            if($process === true){
               $success       += 1;
            }else{
               $failed        += 1;
            }
         }

         if((int)$failed === 0){
            $return['message']   = 'Proses penghapusan data berhasil';
            $return['style']     = $this->cssDone;
         }elseif((int)$success === 0){
            $return['message']   = 'Proses penghapusan data gagal';
            $return['style']     = $this->cssFail;
         }else{
            $return['message']   = 'Proses penghapusan data sukses <strong>'.$success.'</strong> Error <strong>'.$failed.'</strong>';
            $return['style']     = $this->cssDone;
         }
      }

      return (array)$return;
   }
}

?>