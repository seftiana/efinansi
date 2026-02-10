<?php
/**
* ================= doc ====================
* FILENAME     : ProcessTransaksiSpj.php
* @package     : ProcessTransaksiSpj
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-18
* @Modified    : 2015-03-18
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_spj/business/TransaksiSpj.class.php';

class ProcessTransaksiSpj
{
   # internal variables
   protected $mData  = array();
   private $mObj;
   private $urlInput;
   private $urlDetail;
   private $urlHome;
   public $cssDone   = 'notebox-done';
   public $cssFail   = 'notebox-warning';
   # Constructor
   function __construct ($connetionNumber = 0)
   {
      $this->mObj       = new TransaksiSpj($connetionNumber);
      $queryString      = $this->mObj->_getQueryString();
      $this->urlInput   = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj',
         'AddTransaksiSpj',
         'view',
         'html'
      ) . '&' . $queryString;

      $this->urlDetail  = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj',
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
      $requestData         = array();
      if($this->mObj->method == 'post'){
         $tanggalDay    = (int)$this->mObj->_POST['tanggal_day'];
         $tanggalMon    = (int)$this->mObj->_POST['tanggal_mon'];
         $tanggalYear   = (int)$this->mObj->_POST['tanggal_year'];
         $dueDateDay    = (int)$this->mObj->_POST['due_date_day'];
         $dueDateMon    = (int)$this->mObj->_POST['due_date_mon'];
         $dueDateYear   = (int)$this->mObj->_POST['due_date_year'];

         $requestData['id']            = $this->mObj->_POST['data_id'];
         $requestData['unit_id']       = $this->mObj->_POST['unit_id'];
         $requestData['unit_nama']     = $this->mObj->_POST['unit_nama'];
         $requestData['jenis_transaksi']  = $this->mObj->_POST['jenis_transaksi'];
         $requestData['tipe_transaksi']   = $this->mObj->_POST['tipe_transaksi'];
         $requestData['realisasi_id']     = $this->mObj->_POST['realisasi_id'];
         $requestData['kegiatan_id']      = $this->mObj->_POST['kegiatan_id'];
         $requestData['akun_id']          = $this->mObj->_POST['akun_id'];
         $requestData['akun_nama']        = $this->mObj->_POST['akun_nama'];
         $requestData['tanggal']          = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));
         $requestData['check_tanggal']    = checkdate($tanggalMon, $tanggalDay, $tanggalYear);
         $requestData['due_date']         = date('Y-m-d', mktime(0,0,0, $dueDateMon, $dueDateDay, $dueDateYear));
         $requestData['check_due_date']   = checkdate($dueDateMon, $dueDateDay, $dueDateYear);
         $requestData['nominal_approve']     = $this->mObj->_POST['nominal_approve'];
         $requestData['nominal_realisasi']   = $this->mObj->_POST['nominal_realisasi'];
         $requestData['nominal']             = $this->mObj->_POST['nominal'];
         $requestData['uraian']              = $this->mObj->_POST['uraian'];
         $requestData['penanggung_jawab']    = $this->mObj->_POST['penanggung_jawab'];
         $requestData['invoices']            = array();
         $requestData['attachment']          = array();
         if($this->mObj->_POST['invoices'] && !empty($this->mObj->_POST['invoices'])){
            $index      = 0;
            foreach ($this->mObj->_POST['invoices'] as $inv) {
               $requestData['invoices'][$index]['nomor']    = $inv['nomor'];
               $index++;
            }
         }

         if($this->mObj->_POST['attachment'] && !empty($this->mObj->_POST['attachment'])){
            unset($index);
            $index   = 0;
            foreach ($this->mObj->_POST['attachment'] as $files) {
               $requestData['attachment'][$index]['name']   = $files['name'];
               $requestData['attachment'][$index]['path']   = $files['path'];
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
      $requestData         = $this->mData;
      if(empty($requestData)){
         $err[]   = 'Tidak ada yang akan di simpan';
      }
      if($requestData['unit_id'] == ''){
         $err[]   = 'Silahkan definisikan unit kerja';
      }

      if($requestData['jenis_transaksi'] == ''){
         $err[]   = 'Definisikan jenis transaksi';
      }

      if($requestData['tipe_transaksi'] == ''){
         $err[]   = 'Definisikan tipe transaksi';
      }

      if($requestData['akun_id'] == ''){
         $err[]   = 'Definisikan '.GTFWConfiguration::GetValue('language', 'akun');
      }

      if(empty($requestData['invoices'])){
         $err[]   = 'Silahkan isikan invoice';
      }

      if($requestData['nominal'] <= 0){
         $err[]   = 'Isikan nominal transaksi';
      }

      if($requestData['penanggung_jawab'] == ''){
         $err[]   = 'Isikan penanggung jawab transaksi';
      }

      if(isset($err)){
         $return['message']   = $err[0];
         $return['result']    = false;
      }else{
         $return['message']   = null;
         $return['result']    = true;
      }

      return $return;
   }
   public function Save()
   {
      if(isset($this->mObj->_POST['btnbalik'])){
         $this->resetData();
         unset($this->mObj->_POST['attachment']);
         $return['url']       = $this->urlHome;
         $return['message']   = NULL;
         $return['style']     = NULL;
         $return['data']      = NULL;
      }else{
         $this->setData();
         $check      = $this->checkData();
         if($check['result'] === false){
            $return['message']   = $check['message'];
            $return['style']     = $this->cssFail;
            $return['url']       = $this->urlInput;
            $return['data']      = $this->mObj->_POST;
         }else{
            $process    = $this->mObj->doSaveTransaksi($this->mData);
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
               $return['style']     = $this->cssDone;
               $return['data']      = NULL;
            }else{
               $return['message']   = 'Proses penyimpanan data gagal';
               $return['style']     = $this->cssFail;
               $return['url']       = $this->urlInput;
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
      $uploadDir     = realpath($dataDir) . DS . 'spj';
      if($requestData['attachment'] && !empty($requestData['attachment'])){
         foreach ($requestData['attachment'] as $attachment) {
            $files      = realpath($tmpDirUpload . DS . $attachment['path']);
            $realFile   = realpath($uploadDir . DS . $attachment['path']);
            if(file_exists($files)){
               unlink($files);
            }
         }
      }
   }
}
?>