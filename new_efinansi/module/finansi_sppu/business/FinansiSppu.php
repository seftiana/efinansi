<?php
/**
* ================= doc ====================
* FILENAME     : FinansiSppu.php
* @package     : FinansiSppu
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-08
* @Modified    : 2015-04-08
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_sppu/business/Sppu.class.php';

class FinansiSppu
{
   # internal variables
   private $mObj;
   private $urlList;
   private $urlDetail;
   private $urlInput;
   private $urlEdit;
   private $urlListSppu;
   protected $mData = array();
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mObj       = new Sppu($connectionNumber);
      $queryString      = $this->mObj->_getQueryString();
      //$requestQuery     = preg_replace('/(\search=[\d]+)/', '', $queryString);
      //$requestQuery     = preg_replace('/(\data_id=[\d]+)/', '', $requestQuery);
      //$requestQuery     = preg_replace('/\&[\&]+/', '&', $requestQuery);
      //$requestQuery     = preg_replace('/[\&]$/', '', $requestQuery);
      //var_dump($requestQuery);
      //echo $queryString;
      $requestQuery = $queryString;
      $this->urlInput   = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'InputSppu',
         'view',
         'html'
      ).'&'.$queryString;
      
      $this->urlEdit   = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'EditSppu',
         'view',
         'html'
      ).'&'.$queryString;
      
      $this->urlList    = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'Sppu',
         'view',
         'html'
      ).'&search=1&'.$requestQuery;
      $this->urlDetail  = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'DetailSppu',
         'view',
         'html'
      );
      $this->urlListSppu   = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'ListSppu',
         'view',
         'html'
      ).'&search=1&'.$requestQuery;
   }

   private function setData()
   {
      $requestData   = array();
      if($this->mObj->method == 'post'){
         $tanggalDay       = (int)$this->mObj->_POST['tanggal_day'];
         $tanggalMon       = (int)$this->mObj->_POST['tanggal_mon'];
         $tanggalYear      = (int)$this->mObj->_POST['tanggal_year'];
         $requestData['id']      = $this->mObj->_POST['data_id'];
         $requestData['nomor_bukti']   = $this->mObj->_POST['nomor_bukti'];
         $requestData['tanggal']       = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));
         $requestData['check_date']    = checkdate($tanggalMon, $tanggalDay, $tanggalYear);
         $requestData['bank']          = $this->mObj->_POST['bank'];
         $requestData['no_cek_giro']   = $this->mObj->_POST['no_cek_giro'];
         $requestData['no_rekening']   = $this->mObj->_POST['no_rekening'];
         $requestData['bp']            = $this->mObj->_POST['bp'];
         $requestData['cr']            = $this->mObj->_POST['cr'];
         $requestData['keterangan']    = $this->mObj->_POST['keterangan'];
         $requestData['nominal']       = 0;
         $requestData['items']         = array();
         if(!empty($this->mObj->_POST['data'])){
            $index      = 0;
            foreach ($this->mObj->_POST['data'] as $data) {
               $requestData['items'][$index]    = (array)$data;
               $requestData['nominal'] += $data['nominal'];
               $index++;
            }
         }
      }

      $this->mData      = (array)$requestData;
   }

   public function getData()
   {
      //$this->setData();
      return (array)$this->mData;
   }

   private function checkData()
   {
      $requestData      = $this->mData;
      if(empty($requestData)){
         $err[]         = 'Tidak ada data yang akan di proses';
      }

      if($requestData['bp'] == ''){
         $err[]         = 'Bank Payment Belum dicentang';
      }
      
      if($requestData['nomor_bukti'] == ''){
         $err[]         = 'Isikan nomor bukti';
      }

      if($requestData['check_date'] === false){
         $err[]         = 'Definisikan tanggal dengan benar';
      }

      if($requestData['bank'] == ''){
         $err[]         = 'Isikan nama bank';
      }

      if($requestData['keterangan'] == ''){
         $err[]         = 'Isikan keterangan';
      }

      if($requestData['no_cek_giro'] == ''){
         $err[]         = 'Isikan nomor cek/giro';
      }

      if($requestData['no_rekening'] == ''){
         $err[]         = 'Isikan nomor rekening';
      }
      
      if(empty($requestData['items'])) {
          $err[]       = 'Daftar FPA kosong';
      }
      if(isset($err)){
         $return['message']   = $err[0];
         $return['result']    = false;
      }else{
         $return['message']   = NULL;
         $return['result']    = true;
      }

      return $return;
   }

   public function Save()
   {
      $this->setData();
      $check      = $this->checkData();
      
      if($check['result'] === false){
         $return['url']       = $this->urlInput;
         $return['style']     = 'notebox-done';
         $return['data']      = $this->mObj->_POST;
         $return['message']   = $check['message'];
      }else{
         $process       = $this->mObj->doSaveSppu($this->mData); 
         if($process['result'] === true){
            $return['url']       = $this->urlDetail.'&data_id='.$process['data_id'];
            $return['style']     = 'notebox-done';
            $return['data']      = NULL;
            $return['message']   = 'Proses penyimpanan data berhasil ';
         }else{
            $return['url']       = $this->urlInput;
            $return['style']     = 'notebox-warning';
            $return['data']      = $this->mObj->_POST;
            $return['message']   = 'Proses penyimpanan data gagal';
         }
      }

      return (array)$return;
   }

   public function Update()
   {
      $this->setData();
      $check      = $this->checkData();

      if($check['result'] === false){
         $return['url']       = $this->urlEdit;
         $return['style']     = 'notebox-done';
         $return['data']      = $this->mObj->_POST;
         $return['message']   = $check['message'];
      }else{
         $process       = $this->mObj->doUpdateSppu($this->mData);

         if($process['result'] === true){
            $return['url']       = $this->urlDetail.'&data_id='.$process['data_id'];
            $return['style']     = 'notebox-done';
            $return['data']      = NULL;
            $return['message']   = 'Proses update data berhasil ';
         }else{
            $return['url']       = $this->urlEdit;
            $return['style']     = 'notebox-warning';
            $return['data']      = $this->mObj->_POST;
            $return['message']   = 'Proses update data gagal';
         }
      }

      return (array)$return;
   }

   public function Delete()
   {
      $return['url']          = $this->urlListSppu;
      $return['data']         = $this->mObj->_POST;
      if($this->mObj->method == 'post'){
         $idDelete      = $this->mObj->_POST['idDelete'];
         $name          = $this->mObj->_POST['nameDelete'];
         $sukses         = 0;
         $failed        = 0;
         foreach ($idDelete as $id) {
            $process          = $this->mObj->doDeleteSppu($id);
            if($process === true){
               $sukses+=1;
            }else{
               $failed+=1;
            }
         }

         if($sukses === 0){
            $return['message']   = 'Penghapusan data gagal';
            $return['style']     = 'notebox-warning';
         }elseif($sukses <> 0 AND $failed <> 0){
            $return['message']   = $sukses.' Data berhasil di hapus dan '.$failed.' Data gagal dihapus';
            $return['style']     = 'notebox-done';
         }else{
            $return['message']   = 'Penghapusan data berhasil';
            $return['style']     = 'notebox-done';
         }
      }else{
         $return['style']     = 'notebox-warning';
         $return['message']   = 'You don\'t have permission to access this page';
      }

      return $return;
   }
   

   public function DeleteBp()
   {
      $return['url']          = $this->urlListSppu;
      $return['data']         = $this->mObj->_POST;
      if($this->mObj->method == 'post'){
         $idDelete      = $this->mObj->_POST['idDelete'];
         $name          = $this->mObj->_POST['nameDelete'];
         $sukses         = 0;
         $failed        = 0;         
            $process          = $this->mObj->doDeleteBp($idDelete);
            if($process === true){
               $sukses+=1;
            }else{
               $failed+=1;
            }
         
         if($sukses === 0){
            $return['message']   = 'Penghapusan data gagal';
            $return['style']     = 'notebox-warning';
         }elseif($sukses <> 0 AND $failed <> 0){
            $return['message']   = $sukses.' Data berhasil di hapus dan '.$failed.' Data gagal dihapus';
            $return['style']     = 'notebox-done';
         }else{
            $return['message']   = 'Penghapusan data berhasil';
            $return['style']     = 'notebox-done';
         }
      }else{
         $return['style']     = 'notebox-warning';
         $return['message']   = 'You don\'t have permission to access this page';
      }

      return $return;
   }   
}
?>