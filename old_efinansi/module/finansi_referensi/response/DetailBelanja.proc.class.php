<?php
/**
* ================= doc ====================
* FILENAME     : DetailBelanja.proc.class.php
* @package     : DetailBelanja
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-01-07
* @Modified    : 2014-01-07
* @Analysts    : Dyah Fajar
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';

class DetailBelanja
{
   # internal variables
   private $mObj;
   private $method      = '';
   protected $mData     = array();
   public $urlRedirect;
   public $urlInput;
   public $urlEdit;

   public $cssDone   = 'notebox-done';
   public $cssFail   = 'notebox-warning';
   # Constructor
   function __construct ()
   {
      $this->mObj          = new FinansiReferensi();
      $queryString         = $this->mObj->__getQueryString();
      $this->method        = $_SERVER['REQUEST_METHOD'];
      $this->urlRedirect   = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ManajemenDetailBelanja',
         'view',
         'html'
      ).'&'.$queryString;

      $this->urlInput      = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'AddDetailBelanja',
         'view',
         'html'
      ).'&'.$queryString;

      $this->urlEdit       = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'EditDetailBelanja',
         'view',
         'html'
      ).'&'.$queryString;

      if(strtoupper($this->method) === 'POST'){
         $this->mData['kegref_id']     = $this->mObj->_POST['komponen_id'];
         $this->mData['id']            = $this->mObj->_POST['data_id'];
         $this->mData['komponen_id']   = $this->mObj->_POST['detail_belanja_id'];
         $this->mData['komponen']      = $this->mObj->_POST['detail_belanja'];
         $this->mData['nominal']       = $this->mObj->_POST['nominal'];
         $this->mData['unit_kerja']    = array();

         if(!empty($this->mObj->_POST['unit'])){
            $this->mData['unit_kerja'] = $this->mObj->_POST['unit'];
         }
      }
   }

   public function Check()
   {
      if(strtoupper($this->method) === 'POST'){
         if(isset($this->mObj->_POST['btnsimpan'])){
            $dataList            = $this->mData;
            $checkDetailBelanja  = $this->mObj->CheckDetailBelanja($dataList['kegref_id'], $dataList['komponen_id']);
            if(empty($dataList)){
               $err[]      = 'Tidak ada data yang akan di simpan';
            }
            if($dataList['komponen_id'] == ''){
               $err[]      = 'Terjadi kesalahan dalam pendefinisian data. Data komponen tidak di temukan';
            }
            if($dataList['komponen_id'] == ''){
               $err[]      = 'Tidak ada data detail belanja yang akan di simpan';
            }
            if($checkDetailBelanja === false AND $dataList['id'] != ''){
               $err[]      = 'Duplicate data, Data detail belanja yang akan anda simpan sudah tersimpan dalam sistem';
            }
            if($dataList['nominal'] <= 0){
               $err[]      = 'Masukan Nominal Satuan untuk detail belanja';
            }
            // if(empty($dataList['unit_kerja'])){
            //    $err[]      = 'Definisikan minimal satu unit kerja untuk detail belanja';
            // }

            if(isset($err)){
               $return['message']      = $err[0];
               $return['return']       = false;
            }else{
               $return['message']      = null;
               $return['return']       = true;
            }

            return (array)$return;
         }else{
            return false;
         }
      }else{
         return false;
      }

      return false;
   }

   /**
    * @return Array $result, boolean redirect true, false
    * @return Array $result, String url, URL to redirect
    */
   public function Save()
   {
      $check      = $this->Check();
      // jika tidak lolos proses pengecekan data
      if($check === false){
         $result['redirect']     = true;
         $result['url']          = $this->urlRedirect;
      }

      // jika lolos proses pengecekan data
      if($check['return'] === true){
         // jika semua proses pengecekan sukses tidak di temukan error
         $process       = $this->mObj->DoInsertKomponenKegiatan($this->mData);
         if($process === true){
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'ManajemenDetailBelanja',
               'view',
               'html',
               array(
                  NULL,
                  'Proses Update data berhasil di jalankan',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );

            $result['redirect']     = true;
            $result['url']          = $this->urlRedirect;
         }else{
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'AddDetailBelanja',
               'view',
               'html',
               array(
                  $this->mObj->_POST,
                  'Proses Update data detail belanja gagal di jalankan, silahkan cek kembali data Anda',
                  $this->cssFail
               ),
               Messenger::NextRequest
            );
            $result['redirect']     = false;
            $result['url']          = $this->urlInput;
         }
      }else{
         // jika di temukan error ketika proses pengecekan data
         Messenger::Instance()->Send(
            Dispatcher::Instance()->mModule,
            'AddDetailBelanja',
            'view',
            'html',
            array(
               $this->mObj->_POST,
               $check['message'],
               $this->cssFail
            ),
            Messenger::NextRequest
         );
         $result['redirect']     = false;
         $result['url']          = $this->urlInput;
      }

      return (array)$result;
   }

   /**
    * @return Array $result, boolean redirect true, false
    * @return Array $result, String url, URL to redirect
    */
   public function Update()
   {
      $check      = $this->Check();
      // jika tidak lolos proses pengecekan data
      if($check === false){
         $result['redirect']     = true;
         $result['url']          = $this->urlRedirect;
      }

      // jika lolos proses pengecekan data
      if($check['return'] === true){
         // jika semua proses pengecekan sukses tidak di temukan error
         $process       = $this->mObj->DoUpdateDetailBelanja($this->mData);
         if($process === true){
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'ManajemenDetailBelanja',
               'view',
               'html',
               array(
                  NULL,
                  'Proses penyimpanan data berhasil di jalankan',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );

            $result['redirect']     = true;
            $result['url']          = $this->urlRedirect;
         }else{
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'EditDetailBelanja',
               'view',
               'html',
               array(
                  $this->mObj->_POST,
                  'Proses penyimpanan data detail belanja gagal di jalankan, silahkan cek kembali data Anda',
                  $this->cssFail
               ),
               Messenger::NextRequest
            );
            $result['redirect']     = false;
            $result['url']          = $this->urlEdit;
         }
      }else{
         // jika di temukan error ketika proses pengecekan data
         Messenger::Instance()->Send(
            Dispatcher::Instance()->mModule,
            'EditDetailBelanja',
            'view',
            'html',
            array(
               $this->mObj->_POST,
               $check['message'],
               $this->cssFail
            ),
            Messenger::NextRequest
         );
         $result['redirect']     = false;
         $result['url']          = $this->urlEdit;
      }

      return (array)$result;
   }

   public function Delete()
   {
      $idDelete                     = $this->mObj->_POST['idDelete'];
      list($komponenId, $kegrefId)  = explode('|', $idDelete);

      $process       = $this->mObj->DoDeleteKomponenKegiatan($kegrefId, $komponenId);

      if($process === true){
         $message    = 'Proses penghapusan data berhasil di jalankan';
         $style      = $this->cssDone;
      }else{
         $message    = 'Proses penghapusan data gagal di jalankan';
         $style      = $this->cssFail;
      }

      Messenger::Instance()->Send(
         Dispatcher::Instance()->mModule,
         'ManajemenDetailBelanja',
         'view',
         'html',
         array(
            NULL,
            $message,
            $style
         ),
         Messenger::NextRequest
      );

      $result['redirect']           = true;
      $result['url']                = $this->urlRedirect;
      return (array)$result;
   }
}
?>