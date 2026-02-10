<?php
/**
* ================= doc ====================
* FILENAME     : ProcessRealisasiPenerimaan.php
* @package     : ProcessRealisasiPenerimaan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-09
* @Modified    : 2015-03-09
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_realisasi_penerimaan/business/TransaksiPenerimaan.class.php';

class ProcessRealisasiPenerimaan
{
   # internal variables
   private $mObj;
   private $urlInput;
   private $urlDetail;
   private $urlHome;
   protected $mData     = array();
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mObj       = new TransaksiPenerimaan($connectionNumber);
      $queryString      = $this->mObj->_getQueryString();
      $queryReturn      = ($queryString == '') ? '' : '&search=1&'.$queryString;
      $this->urlInput   = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_realisasi_penerimaan',
         'InputTransaksiRealisasiPenerimaan',
         'view',
         'html'
      ).'&'.$queryString;

      $this->urlDetail  = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_realisasi_penerimaan',
         'DetailTransaksi',
         'view',
         'html'
      ).'&'.$queryString;

      $this->urlHome    = Dispatcher::Instance()->GetUrl(
         'home',
         'home',
         'view',
         'html'
      );
   }

   private function setData()
   {
      $requestData      = array();
      if($this->mObj->method == 'post'){
         $tanggalDay       = (int)$this->mObj->_POST['tanggal_transaksi_day'];
         $tanggalMon       = (int)$this->mObj->_POST['tanggal_transaksi_mon'];
         $tanggalYear      = (int)$this->mObj->_POST['tanggal_transaksi_year'];
         $dueDateDay       = (int)$this->mObj->_POST['due_date_day'];
         $dueDateMon       = (int)$this->mObj->_POST['due_date_mon'];
         $dueDateYear      = (int)$this->mObj->_POST['due_date_year'];

         $requestData['id']      = $this->mObj->_POST['data_id'];
         $requestData['unit_id'] = $this->mObj->_POST['unit_id'];
         $requestData['unit_nama']  = $this->mObj->_POST['unit_nama'];
         $requestData['map_id']     = $this->mObj->_POST['map_id'];
         $requestData['map_nama']   = $this->mObj->_POST['map_nama'];
         $requestData['jenis_transaksi_id']  = $this->mObj->_POST['jenis_transaksi'];
         $requestData['tipe_transaksi_id']   = $this->mObj->_POST['tipe_transaksi'];
         $requestData['no_invoice']          = $this->mObj->_POST['no_invoice'];
         $requestData['nominal_approve']     = $this->mObj->_POST['nominal_approve'];
         $requestData['nominal_realisasi']   = $this->mObj->_POST['nominal_realisasi'];
         $requestData['nominal']             = $this->mObj->_POST['nominal'];
         $requestData['keterangan']          = $this->mObj->_POST['catatan_transaksi'];
         $requestData['penanggung_jawab']    = $this->mObj->_POST['penanggung_jawab'];
         $requestData['skenario']            = $this->mObj->_POST['skenario'];
         $requestData['skenario_label']      = $this->mObj->_POST['skenario_label'];
         $requestData['auto_jurnal']         = (strtoupper($this->mObj->_POST['skenario']) == 'AUTO') ? 'Y' : 'T';
         $requestData['tanggal']          = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));
         $requestData['check_tanggal']    = checkdate($tanggalMon, $tanggalDay, $tanggalYear);
         $requestData['due_date']         = date('Y-m-d', mktime(0,0,0, $dueDateMon, $dueDateDay, $dueDateYear));
         $requestData['check_due_date']   = checkdate($dueDateMon, $dueDateDay, $dueDateYear);
         $requestData['invoices']         = array();
         $requestData['attachment']       = array();
         $requestData['skenario_jurnal']  = array();

         if($this->mObj->_POST['invoices'] && !empty($this->mObj->_POST['invoices'])){
            $index      = 0;
            foreach ($this->mObj->_POST['invoices'] as $inv) {
               $requestData['invoices'][$index]['nomor']    = $inv['nomor'];
               $index++;
            }
         }

         if($this->mObj->_POST['attachment'] && !empty($this->mObj->_POST['attachment'])){
            $index      = 0;
            foreach ($this->mObj->_POST['attachment'] AS $file) {
               $requestData['attachment'][$index]['path']   = $file['path'];
               $requestData['attachment'][$index]['name']   = $file['name'];
               $requestData['attachment'][$index]['size']   = $file['size'];

               $index++;
            }
         }

         if($this->mObj->_POST['skenario_jurnal'] && !empty($this->mObj->_POST['skenario_jurnal'])){
            $index      = 0;
            foreach ($this->mObj->_POST['skenario_jurnal'] as $sk) {
               $requestData['skenario_jurnal'][$index]['id']      = $sk['id'];
               $requestData['skenario_jurnal'][$index]['name']    = $sk['nama'];
               $index++;
            }
         }
      }
      $this->mData      = (array)$requestData;
   }

   public function getData()
   {
      $this->setData();

      return (array)$this->mData;
   }

   private function checkData()
   {
      $requestData      = $this->mData;
      if(empty($requestData)){
         $err[]      = 'Tidak ada data yang akan di submit';
      }

      if($requestData['unit_id'] == ''){
         $err[]      = 'Definisikan unit kerja';
      }

      if($requestData['map_id'] == ''){
         $err[]      = 'Definisikan MAP';
      }

      if($requestData['jenis_transaksi_id'] == ''){
         $err[]      = 'Definisikan jenis transaksi';
      }

      if($requestData['tipe_transaksi_id'] == ''){
         $err[]      = 'Definisikan tipe transaksi';
      }

      if($requestData['nominal'] <= 0){
         $err[]      = 'Isikan nominal realisasi';
      }

      if(($requestData['nominal']+$requestData['nominal_realisasi']) > $requestData['nominal_approve']){
         $err[]      = 'Nominal yang Anda masukkan melebihi nominal tersisa <strong>Rp.'.number_format(($requestData['nominal_approve']-$requestData['nominal_realisasi']), 2, ',','.').'</strong>';
      }

      if($requestData['penanggung_jawab'] == ''){
         $err[]      = 'Isikan nama penanggung jawab realisasi';
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

      if(isset($this->mObj->_POST['btnReset'])){
         $this->resetData();
         unset($this->mObj->_POST['attachment']);
         $return['url']       = $this->urlHome;
         $return['message']   = NULL;
         $return['style']     = NULL;
         $return['data']      = NULL;
      }else{
         $this->setData();
         $check         = $this->checkData();
         if($check['result'] === false){
            $return['url']       = $this->urlInput;
            $return['message']   = $check['message'];
            $return['style']     = 'notebox-warning';
            $return['data']      = $this->mObj->_POST;
         }else{
            $process    = $this->mObj->SaveTransaksi($this->mData);
            if($process['result'] === true){
               if(method_exists(Dispatcher::Instance(), 'getQueryString')){
                  # @param array
                  $queryString   = Dispatcher::instance()->getQueryString($process);
               }else{
                  $query         = array();
                  foreach ($process as $key => $value) {
                     $query[$key]   = Dispatcher::Instance()->Encrypt($value);
                  }
                  $queryString   = urldecode(http_build_query($query));
               }

               $return['url']       = $this->urlDetail . '&' . $queryString;
               $return['message']   = 'Proses penyimpanan data berhasil';
               $return['style']     = 'notebox-done';
               $return['data']      = $this->mObj->_POST;
            }else{
               $return['url']       = $this->urlInput;
               $return['message']   = 'Proses penyimpanan data gagal';
               $return['style']     = 'notebox-warning';
               $return['data']      = $this->mObj->_POST;
            }
         }
      }

      return (array)$return;
   }

   /**
    * @package resetData
    * @description Untuk reset semua data dan menghapus file attachment jika memang ada
    */
   private function resetData()
   {
      $requestData   = $this->getData();

      if (!defined('DS')) {
         define('DS', DIRECTORY_SEPARATOR);
      }
      $dataDir       = GTFW_APP_DIR . DS . 'file';
      $tmpDirUpload  = realpath($dataDir) . DS . 'tmp';
      $uploadDir     = realpath($dataDir) . DS . 'rencana_penerimaan';
      if($requestData['attachment'] && !empty($requestData['attachment'])){
         foreach ($requestData['attachment'] as $attachment) {
            $files   = realpath($tmpDirUpload . DS . $attachment['path']);
            if(file_exists($files)){
               unlink($files);
            }
         }
      }
   }
}
?>