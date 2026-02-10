<?php

/**
 * Class ProcessSubAccountKetujuh
 * @package sub_account_ketujuh
 * @copyright 2011 gamatechno
 */

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/sub_account_ketujuh/business/SubAccountKetujuh.class.php';

/**
 * Class ProcessSubAccountKetujuh
 * untuk menangani proses request
 * @access public
 */
class ProcessSubAccountKetujuh {
   protected $mSubAccoutKetujuh;
   protected $mPageView;
   protected $mPageInput;
   protected $mPageEdit;

   /**
    * variable css untuk menampilkan notifikasi warning
    */
   protected $mCssDone = "notebox-done";
   protected $mCssFail = "notebox-warning";

   protected $mData     = array();

   public function __construct()
   {
      $this->mSubAccoutKetujuh = new SubAccountKetujuh();
      $queryString             = $this->mSubAccoutKetujuh->_getQueryString();

      /**
       * sebagai tujuan halaman dalam proses redirect ke page view
       */
      $this->mPageView = Dispatcher::Instance()->GetUrl(
         'sub_account_ketujuh',
         'subAccountKetujuh',
         'view',
         'html'
      ).'&search=1&'.$queryString;

      /**
       * sebagai tujuan halaman dalam proses redirect ke page input
       */
      $this->mPageInput = Dispatcher::Instance()->GetUrl(
         'sub_account_ketujuh',
         'inputSubAccountKetujuh',
         'view',
         'html'
      ).'&'.$queryString;

      $this->mPageEdit  = Dispatcher::Instance()->GetUrl(
         'sub_account_ketujuh',
         'editSubAccountKetujuh',
         'view',
         'html'
      ).'&'.$queryString;
   }

   private function setData()
   {
      $requestData      = array();
      if($this->mSubAccoutKetujuh->method == 'post'){
         $kode       = preg_replace('/\s[\s]+/', '', $this->mSubAccoutKetujuh->_POST['kode']);
         $kode       = preg_replace('/[\s\W]+/', '', $kode);
         $kode       = preg_replace('/^[\_]+/', '', $kode);
         $kode       = preg_replace('/[\_]+$/', '', $kode);
         $requestData['id']      = $this->mSubAccoutKetujuh->_POST['dataId'];
         $requestData['kode']    = $kode;
         $requestData['nama']    = $this->mSubAccoutKetujuh->_POST['nama'];
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
      $requestData      = $this->mData;

      if(empty($requestData)){
         $err[]         = 'Tidak ada data yang di set untuk di simpan ke dalam sistem';
      }
      if($requestData['kode'] == ''){
         $err[]         = 'Isikan kode sub akun';
      }

      if($requestData['nama'] == ''){
         $err[]         = 'Isikan nama/keterangan sub akun';
      }

      $unique           = $this->mSubAccoutKetujuh->doCheckUniqueData(
         $requestData['kode'],
         $requestData['id']
      );
      if($unique === false){
         $err[]         = 'Sub akun dengan kode <strong>'.$requestData['kode'].'</strong> sudah tersimpan di dalam sistem';
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

   /**
    * function Add
    * digunakan untuk menangani proses request simpan data ke tabel
    * @access public
    */
   public function Add()
   {
      $this->setData();
      $checkData           = $this->checkData();
      if($checkData['result'] === false){
         $return['data']      = $this->mSubAccoutKetujuh->_POST;
         $return['message']   = $checkData['message'];
         $return['style']     = $this->mCssFail;
         $return['url']       = $this->mPageInput;
      }else{
         $process             = $this->mSubAccoutKetujuh->DoAdd(
            $this->mData['kode'],
            $this->mData['nama']
         );

         if($process === true){
            $return['data']      = $this->mSubAccoutKetujuh->_POST;
            $return['message']   = 'Penambahan data Berhasil Dilakukan';
            $return['style']     = $this->mCssDone;
            $return['url']       = $this->mPageView;
         }else{
            $return['data']      = $this->mSubAccoutKetujuh->_POST;
            $return['message']   = 'Gagal Menambah Data';
            $return['style']     = $this->mCssFail;
            $return['url']       = $this->mPageInput;
         }
      }

      return $return;
   }

   /**
    * function Update
    * digunakan untuk menangani proses request update data ke tabel
    * @access public
    */
   public function Update()
   {
      $this->setData();
      $checkData           = $this->checkData();
      if($checkData['result'] === false){
         $return['data']      = $this->mSubAccoutKetujuh->_POST;
         $return['message']   = $checkData['message'];
         $return['style']     = $this->mCssFail;
         $return['url']       = $this->mPageEdit;
      }else{
         $process             = $this->mSubAccoutKetujuh->DoUpdate(
            $this->mData['kode'],
            $this->mData['nama'],
            $this->mData['id']
         );

         if($process === true){
            $return['data']      = $this->mSubAccoutKetujuh->_POST;
            $return['message']   = 'Perubahan Data Berhasil Dilakukan';
            $return['style']     = $this->mCssDone;
            $return['url']       = $this->mPageView;
         }else{
            $return['data']      = $this->mSubAccoutKetujuh->_POST;
            $return['message']   = 'Perubahan Data Gagal Dilakukan';
            $return['style']     = $this->mCssFail;
            $return['url']       = $this->mPageEdit;
         }
      }

      return $return;
   }

   /**
    * function Delete
    * digunakan untuk menangani proses request hapus data ke tabel
    * @access public
    */
   public function Delete()
   {
      $return['data']      = NULL;
      $return['url']       = $this->mPageView;
      if($this->mSubAccoutKetujuh->method != 'post'){
         $return['message']   = 'You don\'t have permission to access this page';
         $return['style']     = $this->mCssFail;
      }else{
         $arrId         = $this->mSubAccoutKetujuh->_POST['idDelete'];
         $success       = 0;
         $failed        = 0;
         foreach ($arrId as $id) {
            $delete     = $this->mSubAccoutKetujuh->DoDelete($id);
            if($delete === true){
               $success+=1;
            }else{
               $failed+=1;
            }
         }

         if((int)$failed === 0){
            $return['message']   = 'Semua data berhasil dihapus dari sistem';
            $return['style']     = $this->mCssDone;
         }else{
            $return['message']   = '<strong>'.$success.'</strong> berhasil dihapus dari sistem, <strong>'.$failed.'</strong> Tidak bisa di hapus dari sistem';
            $return['style']     = $this->mCssFail;
         }
      }

      return (array)$return;
   }
}
?>