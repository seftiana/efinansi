<?php
/**
* ================= doc ====================
* FILENAME     : ProcessJurnal.php
* @package     : ProcessJurnal
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-20
* @Modified    : 2015-02-20
* @Analysts    : Dyah Fajar N
* @modified    : noor hadi <noor.hadi@gamatechno.com>
* @last Modified : 2016-02-26
* @copyright   : Copyright (c) 2016 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_penerimaan_bank/business/TransaksiPenerimaanBank.class.php';

class ProcessTransaksiPenerimaanBank
{
   # internal variables
   private $mObj;
   protected $mData  = array();
   public $cssDone   = 'notebox-done';
   public $cssFail   = 'notebox-warning';
   public $urlList;
   public $urlAdd;
   public $urlEdit;
   # Constructor
   function __construct ()
   {
      $this->mObj       = new TransaksiPenerimaanBank();
      $queryString      = $this->mObj->_getQueryString();
      $queryString      = ($queryString == '' OR $queryString === NULL) ? '' : '&'.$queryString;
      $queryReturn      = ($queryString == '' OR $queryString === NULL) ? '' : '&search=1'.$queryString;
      $this->urlReturn  = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_bank',
         'TransaksiPenerimaanBank',
         'view',
         'html'
      ) . $queryReturn;
      $this->urlAdd     = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_bank',
         'Transaksi',
         'view',
         'html'
      ) . $queryString;

      $this->urlEdit    = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_bank',
         'EditTransaksi',
         'view',
         'html'
      ) . $queryString;
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
         $requestData['id']         = $this->mObj->_POST['data_id'];
         $requestData['pembukuan_id']  = $this->mObj->_POST['pembukuan_referensi_id'];
         $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));
         $requestData['tanggal_old']= date('Y-m-d', strtotime($this->mObj->_POST['tanggal_old']));
         $requestData['my_tanggal']    = date('Y-m', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));
         $requestData['my_tanggal_old']= date('Y-m', strtotime($this->mObj->_POST['tanggal_old']));
         $requestData['checkdate']  = checkdate($tanggalMon, $tanggalDay, $tanggalYear);
         $requestData['status']     = $this->mObj->_POST['status_kas'];
         $requestData['bentuk_transaksi'] = $this->mObj->_POST['bentuk_transaksi'];
         $requestData['keterangan']       = trim($this->mObj->_POST['keterangan']);
         $requestData['auto_number']      = trim($this->mObj->_POST['auto_number']);
         $requestData['auto_number_status'] = trim($this->mObj->_POST['auto_number_status']);
         $requestData['bpkb']             = trim($this->mObj->_POST['bpkb']);
         $requestData['bpkb_auto']        = trim($this->mObj->_POST['bpkb_auto']);
         $requestData['bpkb_old']         = trim($this->mObj->_POST['bpkb_old']);
         $requestData['nama_penyetor']    = trim($this->mObj->_POST['nama_penyetor']);//diterima dari
         $requestData['nama_penerima']    = trim($this->mObj->_POST['nama_penerima']);// tujuan
         //additional
         $requestData['unit_kerja_id']    = trim($this->mObj->_POST['unit_kerja_id']);
         $requestData['unit_kerja_nama']  = trim($this->mObj->_POST['unit_kerja_nama']);
         $requestData['rpen_id']          = trim($this->mObj->_POST['rpen_id']);
         $requestData['rpen_nama']        = trim($this->mObj->_POST['rpen_nama']);
         $requestData['rpen_nominal']     = trim($this->mObj->_POST['rpen_nominal']);
         $requestData['skenario_id']      = trim($this->mObj->_POST['skenario_id']);
         $requestData['skenario_nama']    = trim($this->mObj->_POST['skenario_nama']);
         $requestData['pemb_id']          = trim($this->mObj->_POST['pemb_id']);
         $requestData['pemb_id_detil']    = trim($this->mObj->_POST['pemb_id_detil']);
         $requestData['pemb_id_detil_old']    = trim($this->mObj->_POST['pemb_id_detil_old']);
         $requestData['pemb_prodi_id']        = trim($this->mObj->_POST['pemb_prodi_id']);
         $requestData['pemb_prodi_nama']      = trim($this->mObj->_POST['pemb_prodi_nama']);
         $requestData['pemb_jenis_biaya']     = trim($this->mObj->_POST['pemb_jenis_biaya']);
         $requestData['pemb_tipe_pembayaran'] = trim($this->mObj->_POST['pemb_tipe_pembayaran']);
         $requestData['pemb_tipe_pembayaran_old'] = trim($this->mObj->_POST['pemb_tipe_pembayaran_old']);
         $requestData['pemb_nominal']     = trim($this->mObj->_POST['pemb_nominal']);
         $requestData['pemb_potongan']    = trim($this->mObj->_POST['pemb_potongan']);
         $requestData['pemb_deposit']     = trim($this->mObj->_POST['pemb_deposit']);
         $requestData['pemb_deposit_masuk']     = trim($this->mObj->_POST['pemb_deposit_masuk']);
         $requestData['pemb_keterangan']     = trim($this->mObj->_POST['pemb_keterangan']);
         $requestData['pemb_penanggung_jawab']     = trim($this->mObj->_POST['pemb_penanggung_jawab']);
         $requestData['pemb_id_detail']    = trim($this->mObj->_POST['pemb_id_detail']);
         
         $requestData['tipe_transaksi']   = trim($this->mObj->_POST['tipe_transaksi']);
         //end
         $requestData['bpkb']             = trim($this->mObj->_POST['bpkb']);
         $requestData['akun_debet']       = array();
         $requestData['nominal_debet']    = 0;
         $requestData['akun_kredit']      = array();
         $requestData['nominal_kredit']   = 0;
         if($this->mObj->_POST['debet'] && !empty($this->mObj->_POST['debet'])){
            $index            = 0;
            foreach ($this->mObj->_POST['debet'] as $debet) {
               $requestData['akun_debet'][$index]['id']     = $debet['id'];
               $requestData['akun_debet'][$index]['kode']   = $debet['kode'];
               $requestData['akun_debet'][$index]['nama']   = $debet['nama'];
               // $requestData['akun_debet'][$index]['sub_akun']     = $debet['subaccount'];
               $requestData['akun_debet'][$index]['referensi']    = $debet['nomor_referensi'];
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
               $requestData['akun_kredit'][$index]['referensi']   = $kredit['nomor_referensi'];
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
         
         if($this->mObj->_POST['jb'] && !empty($this->mObj->_POST['jb'])){
            $index            = 0;
            foreach ($this->mObj->_POST['jb'] as $jbp) {
                $requestData['jb'][$index]['prodi_id'] = $jbp['prodi_id'];
                $requestData['jb'][$index]['prodi_nama'] = $jbp['prodi_nama'];
                $requestData['jb'][$index]['jenis_biaya_id'] = $jbp['jenis_biaya_id'];
                $requestData['jb'][$index]['jenis_biaya_nama'] = $jbp['jenis_biaya_nama'];
                $requestData['jb'][$index]['nominal'] = $jbp['nominal'];
                $requestData['jb'][$index]['potongan'] = $jbp['potongan'];
                $requestData['jb'][$index]['deposit'] = $jbp['deposit'];
                $requestData['jb'][$index]['keterangan'] = $jbp['keterangan'];
                $requestData['jb'][$index]['id_detail'] = $jbp['id_detail'];
                $requestData['jb'][$index]['tipe'] = $jbp['tipe'];
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
      
      $cekNoref = $this->mObj->getCekNoref($requestData['bpkb']);
      
      if(empty($requestData)){
         $err[]      = 'Tidak ada transaksi penerimaan bank yang akan diproses';
      }

      // if(empty($requestData['unit_kerja_id'])){
      //    $err[]      = 'Unit Kerja Belum Dipilih';
      // }

      // if(empty($requestData['rpen_id'])){
      //    $err[]      = 'Rencana Penerimaan Belum Dipilih';
      // }
      
      if($requestData['auto_number'] === 'T' && $requestData['bpkb']===''){
         $err[]      = 'Nomor BPKB belum diisi';
      }
      
      if($requestData['auto_number'] === 'T' && 
            (($requestData['bpkb'] !== $requestData['bpkb_old']) ||
            ( $requestData['my_tanggal'] !==  $requestData['my_tanggal_old'])) && 
            $cekNoref === TRUE) {
         $err[]      = 'Nomor BPKB sudah ada';
      }

      if($requestData['checkdate'] === false){
         $err[]      = 'Definisikan tanggal dengan benar';
      }

      if(empty($requestData['nama_penyetor'])){
         $err[]      = 'Text Diterima dari belum diisi';
      }
      
      if(empty($requestData['nama_penerima'])){
         $err[]      = 'Text Kepada belum diisi';
      }
/*
      if(empty($requestData['tipe_transaksi'])){
         $err[]      = 'Tipe Transaksi Belum Dipilih';
      }
  */    
      if(empty($requestData['akun_debet'])){
         $err[]      = 'Definisikan akun debet yang akan di simpan';
      }

      if(empty($requestData['akun_kredit'])){
         $err[]      = 'Definisikan akun kredit yang akan di simpan';
      }

      
      
      if($requestData['nominal_debet'] <= 0){
         $err[]      = 'Belum ada nominal yang akan di jurnal untuk semua akun';
      }

      if(strcmp($requestData['nominal_debet'], $requestData['nominal_kredit']) <> 0){
         $err[]      = 'Nominal debet dengan nominal kredit tidak sesuai. Terdapat selisih antara nominal debet dengan nominal kredit';
      }
/*
      if(!empty($requestData['pemb_nominal'])) {
           if(($requestData['pemb_nominal'] != $requestData['nominal_debet']) || 
                  ($requestData['pemb_nominal'] != $requestData['nominal_kredit'])) {
                  $err[]      = 'Nominal Debet atau Kredit Tidak Sesuai Dengan Nominal Pembayaran';
          }
      }*/
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
         $return['url']       = $this->urlAdd;
         $return['message']   = $checkData['message'];
         $return['style']     = $this->cssFail;
         $return['data']      = $this->mObj->_POST;
      }else{
         $process             = $this->mObj->doSaveJurnal($this->mData);
         if($process === true){
            $return['url']       = $this->urlReturn;
            $return['message']   = 'Penambahan data berhasil dilakukan';
            $return['style']     = $this->cssDone;
            $return['data']      = NULL;
         }else{
            $return['url']       = $this->urlAdd;
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
         $process             = $this->mObj->doUpdateData($this->mData);
         if($process === true){
            $return['url']       = $this->urlReturn;
            $return['message']   = 'Update data berhasil dilakukan';
            $return['style']     = $this->cssDone;
            $return['data']      = NULL;
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
         $return['message']   = 'You don\'t have permisson to access this page';
         $return['style']     = $this->cssFail;
      }else{
         list($transaksiId, $pembukuanId)    = explode('.', $this->mObj->_POST['idDelete']);
         $nomor_referensi     = $this->mObj->_POST['nameDelete'];
         $process             = $this->mObj->doDeleteDataJurnal($transaksiId, $pembukuanId);
         if($process === true){
            $return['message']   = 'Data jurnal dengan referensi <strong>'.$nomor_referensi.'</strong> Berhasil dihapus';
            $return['style']     = $this->cssDone;
         }else{
            $return['message']   = 'Gagal menghapus data jurnal dengna nomor referensi <strong>'.$nomor_referensi.'</strong>';
            $return['style']     = $this->cssFail;
         }
      }

      return $return;
   }

   public function JurnalBalik()
   {
      $return['url']       = $this->urlReturn;
      $return['data']      = $this->mObj->_POST;

      if($this->mObj->method != 'post'){
         $return['message']   = 'You don\'t have permisson to access this page';
         $return['style']     = $this->cssFail;
      }else{
         $transaksiId         = $this->mObj->_POST['id'];
         $pembukuanId         = $this->mObj->_POST['pembukuan_id'];
         $referensi           = $this->mObj->_POST['referensi'];
         $process             = $this->mObj->doJurnalBalik($transaksiId, $pembukuanId);
         // $process             = true;
         if($process === true){
            $return['message']   = 'Jurnal Balik untuk jurnal dengan referensi <strong>'.$referensi.'</strong> Berhasil';
            $return['style']     = $this->cssDone;
         }else{
            $return['message']   = 'Gagal melakukan jurnal balik untuk jurnal dengan referensi <strong>'.$referensi.'</strong>';
            $return['style']     = $this->cssFail;
         }
      }

      return $return;
   }
}
?>