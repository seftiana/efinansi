<?php
/**
* ================= doc ====================
* FILENAME     : ProcessTransaksi.php
* @package     : ProcessTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-13
* @Modified    : 2015-04-13
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_pengeluaran_bank/business/TransaksiPengeluaranBank.class.php';

class ProcessTransaksi
{
   # internal variables
   protected $mData  = array();
   private $mObj;
   private $urlInput;
   private $urlReturn;
   private $urlEdit;
   public $cssDone   = 'notebox-done';
   public $cssFail   = 'notebox-warning';
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mObj       = new TransaksiPengeluaranBank($connectionNumber);
      $queryString      = $this->mObj->_getQueryString();
      $queryString      = ($queryString == '' OR $queryString === NULL) ? '' : '&'.$queryString;
      $queryReturn      = ($queryString == '' OR $queryString === NULL) ? '' : '&search=1'.$queryString;
      $this->urlInput   = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pengeluaran_bank',
         'Transaksi',
         'view',
         'html'
      ).$queryString;

      $this->urlReturn  = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pengeluaran_bank',
         'TransaksiPengeluaranBank',
         'view',
         'html'
      ) . $queryReturn;

      $this->urlEdit    = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pengeluaran_bank',
         'EditTransaksi',
         'view',
         'html'
      ).$queryString;
   }

   private function setData()
   {
      $subAkunDefault   = $this->mObj->getDefaultSubAkun();
      $patern           = $subAkunDefault['patern'];
      $regex            = $subAkunDefault['regex'];
      $default          = $subAkunDefault['default'];

      $requestData      = array();

      if($this->mObj->method == 'post'){
         $tanggalDay       = (int)$this->mObj->_POST['referensi_tanggal_day'];
         $tanggalMon       = (int)$this->mObj->_POST['referensi_tanggal_mon'];
         $tanggalYear      = (int)$this->mObj->_POST['referensi_tanggal_year'];
         
         $requestData['id']            = $this->mObj->_POST['data_id'];
         $requestData['bpkb']          = trim($this->mObj->_POST['bpkb']);
         $requestData['referensi_id']  = $this->mObj->_POST['referensi_id'];
         $requestData['referensi_nama']  = $this->mObj->_POST['referensi_nama'];
         $requestData['referensi_no']  = $this->mObj->_POST['referensi_no'];
         $requestData['nominal']       = $this->mObj->_POST['nominal'];
         $requestData['pembukuan_id']  = $this->mObj->_POST['pembukuan_referensi_id'];

         $requestData['checkdate']     = checkdate($tanggalMon, $tanggalDay, $tanggalYear);
         $requestData['status']        = $this->mObj->_POST['status_kas'];
         $requestData['bentuk_transaksi'] = $this->mObj->_POST['bentuk_transaksi'];
         $requestData['keterangan']       = trim($this->mObj->_POST['keterangan']);
         $requestData['akun_debet']       = array();
         $requestData['nominal_debet']    = 0;
         $requestData['akun_kredit']      = array();
         $requestData['nominal_kredit']   = 0;
         $requestData['is_ref']      = trim($this->mObj->_POST['is_ref']);
         $requestData['nama_penyetor']    = trim($this->mObj->_POST['nama_penyetor']);//diterima dari
         $requestData['nama_penerima']    = trim($this->mObj->_POST['nama_penerima']);// tujuan      
         // untuk isi keteragan tambahan pada pembukuan detail 
         $requestData['tanggal_ymd']   = date('Y-m-d', strtotime($this->mObj->_POST['tanggal_ymd']));
         if($requestData['is_ref'] == 'Y') {
            $noRef = $requestData['referensi_no'];
            $requestData['tanggal']       = date('Y-m-d', strtotime($this->mObj->_POST['tanggal_ymd']));
         } else {
             $noRef = $requestData['bpkb'];
            $requestData['tanggal']       = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));            
         }
         if($this->mObj->_POST['debet'] && !empty($this->mObj->_POST['debet'])){
            $index            = 0;
            foreach ($this->mObj->_POST['debet'] as $debet) {
               $requestData['akun_debet'][$index]['id']     = $debet['id'];
               $requestData['akun_debet'][$index]['kode']   = $debet['kode'];
               $requestData['akun_debet'][$index]['nama']   = $debet['nama'];
               // $requestData['akun_debet'][$index]['sub_akun']     = $debet['subaccount'];
               // $requestData['akun_debet'][$index]['referensi']    = $debet['nomor_referensi'];
               $requestData['akun_debet'][$index]['referensi']    = $noRef;
               $requestData['akun_debet'][$index]['keterangan']   = $debet['keterangan'];
               $requestData['akun_debet'][$index]['nominal']      = $debet['nominal'];

               if($debet['subaccount'] != ''){
                  $requestData['akun_debet'][$index]['sub_akun']  = $debet['subaccount'];
               }else{
                  $requestData['akun_debet'][$index]['sub_akun']  = $default;
               }
               $requestData['nominal_debet']       += $debet['nominal'];
               $index++;
            }
         }

         if($this->mObj->_POST['kredit'] && !empty($this->mObj->_POST['kredit'])){
            $index            = 0;
            foreach ($this->mObj->_POST['kredit'] as $kredit) {
               $requestData['akun_kredit'][$index]['id']    = $kredit['id'];
               $requestData['akun_kredit'][$index]['kode']  = $kredit['kode'];
               $requestData['akun_kredit'][$index]['nama']  = $kredit['nama'];
               // $requestData['akun_kredit'][$index]['sub_akun']    = $kredit['subaccount'];
               // $requestData['akun_kredit'][$index]['referensi']   = $kredit['nomor_referensi'];
               $requestData['akun_kredit'][$index]['referensi']   = $noRef;
               $requestData['akun_kredit'][$index]['keterangan']  = $kredit['keterangan'];
               $requestData['akun_kredit'][$index]['nominal']     = $kredit['nominal'];

               if($kredit['subaccount'] != ''){
                  $requestData['akun_kredit'][$index]['sub_akun'] = $kredit['subaccount'];
               }else{
                  $requestData['akun_kredit'][$index]['sub_akun'] = $default;
               }
               $requestData['nominal_kredit']         += $kredit['nominal'];
               $index++;
            }
         }
      }
      
      $this->mData      = $requestData;
   }

   public function getData()
   {
      $this->setData();

      return (array)$this->mData;
   }

   private function checkData()
   {
      $requestData      = (array)$this->mData;
      $subAkunPatern    = $this->mObj->getPaternSubAccount();
      $patern           = $subAkunPatern['patern'];
      $regex            = $subAkunPatern['regex'];
      
      if($requestData['is_ref'] === 'T'){
          $cekNoref = $this->mObj->getCekNoref($requestData['bpkb']);
      } else {
          $cekNoref = $this->mObj->getCekNoref($requestData['referensi_no']);
      }
      /*
      $dataId           = trim($requestData['id']);
      if(empty($dataId) || $dataId === '') { 
        if($requestData['is_ref'] === 'Y' && $requestData['referensi_id']==='' && $requestData['referensi_nama']===''){
           $err[]      = 'Belum memilih Referensi Sppu';
        }            

        if($requestData['is_ref'] === 'Y' && $requestData['referensi_no']==='' ){
           $err[]      = 'Nomor BPKB belum diisi';
        }          
        
        if($requestData['is_ref'] === 'T' && $requestData['bpkb']===''){
           $err[]      = 'Nomor BPKB belum diisi';
        }

        if($requestData['is_ref'] === 'T' && $cekNoref === TRUE){
           $err[]      = 'Nomor BPKB sudah ada';
        }

        if($requestData['is_ref'] === 'Y' && $cekNoref === TRUE){
           $err[]      = 'Nomor BPKB sudah ada';
        }        
      }
      */
      if(empty($requestData)){
         $err[]      = 'Tidak ada data yang akan di jurnal';
      }
      if($requestData['checkdate'] === false && $requestData['is_ref'] === 'T'){
         $err[]      = 'Definisikan tanggal dengan benar';
      }

      if(empty($requestData['nama_penyetor'])){
         $err[]      = 'Text Diterima dari belum diisi ';
      }

      if(empty($requestData['nama_penerima'])){
         $err[]      = 'Text Kepada belum diisi ';
      }
            
      if(empty($requestData['keterangan'])){
         $err[]      = 'Definisikan keterangan';
      }

      if(empty($requestData['akun_debet'])){
         $err[]      = 'Definisikan akun debet yang akan di simpan';
      }

      if(empty($requestData['akun_kredit'])){
         $err[]      = 'Definisikan akun kredit yang akan di simpan';
      }

      if($requestData['nominal_debet'] <= 0 && $requestData['nominal_kredit'] <= 0){
         $err[]      = 'Belum ada nominal yang akan di jurnal untuk semua akun';
      }

      if(strcmp($requestData['nominal_debet'], $requestData['nominal_kredit']) <> 0){
         $err[]      = 'Nominal debet dengan nominal kredit tidak sesuai. Terdapat selisih antara nominal debet dengan nominal kredit';
      }

      if(($requestData['nominal_debet'] != $requestData['nominal']) && 
            ($requestData['nominal_kredit']  != $requestData['nominal'] )) {
          $err[]      = 'Nominal Debet, Kredit tidak sesuai dengan Nominal Transaksi';
      }
      // proses pengecekan sub akun
      if(!empty($requestData['akun_debet'])){
         foreach ($requestData['akun_debet'] as $debet) {
            // pengecekan sub akun
            if($this->mObj->doCheckSubAkun($debet['sub_akun']) === false){
               $err[]   = 'Sub Account <strong>'.$debet['sub_akun'].'</strong> untuk Rekening Debet <strong>'.$debet['kode'].'</strong> tidak sesuai';
            }

            if($debet['nominal'] <= 0){
               $err[]   = 'Isikan Nominal untuk Rekening Debet <strong>'.$debet['kode'].'</strong>';
            }
         }
      }

      if(!empty($requestData['akun_kredit'])){
         foreach ($requestData['akun_kredit'] as $kredit) {
            // pengecekan sub akun
            if($this->mObj->doCheckSubAkun($kredit['sub_akun']) === false){
               $err[]   = 'Sub Account <strong>'.$kredit['sub_akun'].'</strong> untuk Rekening Debet <strong>'.$kredit['kode'].'</strong> tidak sesuai';
            }

            if($kredit['nominal'] <= 0){
               $err[]   = 'Isikan Nominal untuk Rekening Debet <strong>'.$kredit['kode'].'</strong>';
            }
         }
      }

      if(isset($err)){
         // $return['message']   = implode('<br />', $err);
         $return['message']   = $err[0];
         $return['result']    = false;
      }else{
         $return['message']   = null;
         $return['result']    = true;
      }


      return (array)$return;
   }

   public function Save()
   {
      $this->setData();
      $checkData           = $this->checkData();
      if($checkData['result'] === false){
         $return['url']       = $this->urlInput;
         $return['message']   = $checkData['message'];
         $return['style']     = $this->cssFail;
         $return['data']      = $this->mObj->_POST;
      }else{
         $process             = $this->mObj->doSaveTransaksiPengeluranBank($this->mData);
         // $process       = true;
         if($process === true){
            $return['url']       = $this->urlReturn;
            $return['message']   = 'Penambahan data berhasil dilakukan';
            $return['style']     = $this->cssDone;
            $return['data']      = $this->mObj->_POST;
         }else{
            $return['url']       = $this->urlInput;
            $return['message']   = 'Penambahan data gagal dilakukan';
            $return['style']     = $this->cssFail;
            $return['data']      = $this->mObj->_POST;
         }
      }

      return $return;
   }

   public function Update()
   {
      $this->setData();
      $checkData           = $this->checkData();
      if($checkData['result'] === false){
         $return['url']       = $this->urlEdit;
         $return['message']   = $checkData['message'];
         $return['style']     = $this->cssFail;
         $return['data']      = $this->mObj->_POST;
      }else{
         $process             = $this->mObj->doUpdateTransaksiPengeluranBank($this->mData);
         // $process       = false;
         if($process === true){
            $return['url']       = $this->urlReturn;
            $return['message']   = 'Update data berhasil dilakukan';
            $return['style']     = $this->cssDone;
            $return['data']      = $this->mObj->_POST;
         }else{
            $return['url']       = $this->urlEdit;
            $return['message']   = 'Update data gagal dilakukan';
            $return['style']     = $this->cssFail;
            $return['data']      = $this->mObj->_POST;
         }
      }

      return $return;
   }

   public function Delete()
   {
      $return['url']       = $this->urlReturn;
      $return['data']      = NULL;

      if($this->mObj->method != 'post'){
         $return['message']   = 'You don\'t have permission to access this page';
         $return['style']     = $this->cssFail;
      }else{
         list($transaksiId, $pembukuanId, $sppuId)    = explode('.', $this->mObj->_POST['idDelete']);

         $process             = $this->mObj->doDeleteTransaksiPengeluaranBank($pembukuanId, $transaksiId, $sppuId);
         if($process === true){
            $return['message']   = 'Proses penghapusan data berhasil';
            $return['style']     = $this->cssDone;
         }else{
            $return['message']   = 'Proses penghapusan data gagal';
            $return['style']     = $this->cssFail;
         }
      }

      return $return;
   }

}
?>