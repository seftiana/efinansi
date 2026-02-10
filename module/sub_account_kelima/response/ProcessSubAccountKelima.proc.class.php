<?php

/**
 * Class ProcessSubAccountKelima
 * @package sub_account_kelima
 * @copyright 2011 gamatechno
 */

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/sub_account_kelima/business/SubAccountKelima.class.php';

/**
 * Class ProcessSubAccountKelima
 * untuk menangani proses request
 * @access public
 */
class ProcessSubAccountKelima {
   protected $mSubAccoutKelima;
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
      $this->mSubAccoutKelima = new SubAccountKelima();
      $queryString             = $this->mSubAccoutKelima->_getQueryString();

      /**
       * sebagai tujuan halaman dalam proses redirect ke page view
       */
      $this->mPageView = Dispatcher::Instance()->GetUrl(
         'sub_account_kelima',
         'subAccountKelima',
         'view',
         'html'
      ).'&search=1&'.$queryString;

      /**
       * sebagai tujuan halaman dalam proses redirect ke page input
       */
      $this->mPageInput = Dispatcher::Instance()->GetUrl(
         'sub_account_kelima',
         'inputSubAccountKelima',
         'view',
         'html'
      ).'&'.$queryString;

      $this->mPageEdit  = Dispatcher::Instance()->GetUrl(
         'sub_account_kelima',
         'editSubAccountKelima',
         'view',
         'html'
      ).'&'.$queryString;
   }

   private function setData()
   {
      $requestData      = array();
      if($this->mSubAccoutKelima->method == 'post'){
         $kode       = preg_replace('/\s[\s]+/', '', $this->mSubAccoutKelima->_POST['kode']);
         $kode       = preg_replace('/[\s\W]+/', '', $kode);
         $kode       = preg_replace('/^[\_]+/', '', $kode);
         $kode       = preg_replace('/[\_]+$/', '', $kode);
         $requestData['id']      = $this->mSubAccoutKelima->_POST['dataId'];
         $requestData['kode']    = $kode;
         $requestData['nama']    = $this->mSubAccoutKelima->_POST['nama'];
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

      $unique           = $this->mSubAccoutKelima->doCheckUniqueData(
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
         $return['data']      = $this->mSubAccoutKelima->_POST;
         $return['message']   = $checkData['message'];
         $return['style']     = $this->mCssFail;
         $return['url']       = $this->mPageInput;
      }else{
         $process             = $this->mSubAccoutKelima->DoAdd(
            $this->mData['kode'],
            $this->mData['nama']
         );

         if($process === true){
            $return['data']      = $this->mSubAccoutKelima->_POST;
            $return['message']   = 'Penambahan data Berhasil Dilakukan';
            $return['style']     = $this->mCssDone;
            $return['url']       = $this->mPageView;
         }else{
            $return['data']      = $this->mSubAccoutKelima->_POST;
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
         $return['data']      = $this->mSubAccoutKelima->_POST;
         $return['message']   = $checkData['message'];
         $return['style']     = $this->mCssFail;
         $return['url']       = $this->mPageEdit;
      }else{
         $process             = $this->mSubAccoutKelima->DoUpdate(
            $this->mData['kode'],
            $this->mData['nama'],
            $this->mData['id']
         );

         if($process === true){
            $return['data']      = $this->mSubAccoutKelima->_POST;
            $return['message']   = 'Perubahan Data Berhasil Dilakukan';
            $return['style']     = $this->mCssDone;
            $return['url']       = $this->mPageView;
         }else{
            $return['data']      = $this->mSubAccoutKelima->_POST;
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
      if($this->mSubAccoutKelima->method != 'post'){
         $return['message']   = 'You don\'t have permission to access this page';
         $return['style']     = $this->mCssFail;
      }else{
         $arrId         = $this->mSubAccoutKelima->_POST['idDelete'];
         $success       = 0;
         $failed        = 0;
         foreach ($arrId as $id) {
            $delete     = $this->mSubAccoutKelima->DoDelete($id);
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