<?php
/**
* @package Komponen
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rest/business/RestDb.class.php';

class Komponen
{
   private $mObj;
   protected $mData  = array();
   private $urlInput;
   private $urlList;
   private $urlEdit;

   public $cssDone   = 'notebox-done';
   public $cssFail   = 'notebox-warning';
   function __construct()
   {
      $this->mObj          = new FinansiReferensi();
      $this->urlInput      = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'AddKomponen',
         'view',
         'html'
      );
      $this->urlEdit       = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'EditKomponen',
         'view',
         'html'
      );
      $this->urlList       = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ProgramKegiatan',
         'view',
         'html'
      );

      if(strtoupper($this->mObj->method) === 'POST'){
         $this->mData['id']            = $this->mObj->_POST['data_id'];
         $this->mData['kode']          = trim($this->mObj->_POST['kode']);
         $this->mData['nama']          = trim($this->mObj->_POST['nama']);
         $this->mData['ta_id']         = $this->mObj->_POST['tahun_anggaran'];
         $this->mData['kegiatan_id']   = $this->mObj->_POST['kegiatan_id'];
         $this->mData['kegiatan_kode'] = trim($this->mObj->_POST['kegiatan_kode']);
         $this->mData['kegiatan']      = trim($this->mObj->_POST['kegiatan']);
         $this->mData['output_id']     = $this->mObj->_POST['output_id'];
         $this->mData['output_kode']   = trim($this->mObj->_POST['output_kode']);
         $this->mData['output']        = trim($this->mObj->_POST['output']);
         $this->mData['rkakl_subkegiatan_id']   = $this->mObj->_POST['rkakl_subkegiatan_id'];
         $this->mData['rkakl_subkegiatan_kode'] = $this->mObj->_POST['rkakl_subkegiatan_kode'];
         $this->mData['rkakl_subkegiatan_nama'] = $this->mObj->_POST['rkakl_subkegiatan_nama'];
         $this->mData['unit']          = array();
         $this->mData['unit']          = $this->mObj->_POST['unit'];
      }
   }

   public function Check()
   {
      if(strtoupper($this->mObj->method) === 'POST'){
         if(isset($this->mObj->_POST['btnsimpan'])){
            $checkKegiatanRef    = $this->mObj->CheckKegiatanRef(
               $this->mData['kode'],
               $this->mData['ta_id'],
               $this->mData['id'],
               $this->mData['output_id']
            );
            if(empty($this->mData)){
               $err[]      = 'Data Kosong, Tidak ada data dari request';
            }
            if($this->mData['ta_id'] == ''){
               $err[]      = 'Definisikan '.GTFWConfiguration::GetValue('language', 'tahun_anggaran');
            }
            if($this->mData['output_id'] == ''){
               $err[]      = 'Pilih '.GTFWConfiguration::GetValue('language', 'kegiatan').' untuk '.GTFWConfiguration::GetValue('language', 'sub_kegiatan'). ' yang akan Anda simpan';
            }
            if($this->mData['kode'] == ''){
               $err[]      = 'Isikan kode untuk '.GTFWConfiguration::GetValue('language', 'sub_kegiatan');
            }
            if($checkKegiatanRef === false){
               $err[]      = 'Kode '.GTFWConfiguration::GetValue('language', 'sub_kegiatan').' Harus unique';
            }
            if($this->mData['nama'] == ''){
               $err[]      = 'Isikan Nama untuk '.GTFWConfiguration::GetValue('language', 'sub_kegiatan');
            }

            // if(empty($this->mData['unit'])){
            //    $err[]      = 'Definisikan unit kerja '.GTFWConfiguration::GetValue('language', 'sub_kegiatan');
            // }
            if(isset($err)){
               $return['return']    = false;
               $return['message']   = $err[0];
            }else{
               $return['return']    = true;
               $return['message']   = null;
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
   public function Add()
   {
      $check      = $this->Check();
      if($check === false){
         return $this->urlList;
      }

      if($check['return'] === true){
         $process       = $this->mObj->DoSaveKegiatanRef($this->mData);
         if($process === true){
            /**
             * @Description Lib Rest Client
             * @required module/rest/business/RestDb.class.php
             * @result Array
             * @param Send Array $data
             * @param Send String method : POST, GET, PUT, DELETE
             */
            RestDb::Instance()->setApplication(530);
            // COMMENT TOKEN IF NOT NECESSARY
            // RestDb::Instance()->setToken('service token');
            RestDb::Instance()->setModule('rkakl_sub_kegiatan');
            RestDb::Instance()->setSubModule('SaveSubKegiatan');
            RestDb::Instance()->setAction('do');

            $restResult    = RestDb::Instance()->Send(array(
               'kode' => $this->mData['kode'],
               'nama' => $this->mData['nama']
            ), 'POST');

            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'ProgramKegiatan',
               'view',
               'html',
               array(
                  $this->mObj->_POST,
                  'Prosess Penambahan data '.GTFWConfiguration::GetValue('language', 'sub_kegiatan'). ' berhasil di jalankan',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );
            return $this->urlList;
         }else{
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'AddKomponen',
               'view',
               'html',
               array(
                  $this->mObj->_POST,
                  'Prosess Penambahan data '.GTFWConfiguration::GetValue('language', 'sub_kegiatan'). ' Gagal di jalankan',
                  $this->cssFail
               ),
               Messenger::NextRequest
            );
            return $this->urlInput;
         }
      }else{
         Messenger::Instance()->Send(
            Dispatcher::Instance()->mModule,
            'AddKomponen',
            'view',
            'html',
            array(
               $this->mObj->_POST,
               $check['message'],
               $this->cssFail
            ),
            Messenger::NextRequest
         );
         return $this->urlInput;
      }
   }

   public function Update()
   {
      $check      = $this->Check();
      if($check === false){
         return $this->urlList;
      }

      if($check['return'] === true){
         $dataId        = $this->mData['id'];
         $dataKomponen  = $this->mObj->ChangeKeyName($this->mObj->GetKegiatanRefById($dataId));
         $old_kode      = $dataKomponen['data']['kode'];
         $process       = $this->mObj->DoUpdateKegiatanRef($this->mData);
         if($process === true){
            /**
             * @Description Lib Rest Client
             * @required module/rest/business/RestDb.class.php
             * @result Array
             * @param Send Array $data
             * @param Send String method : POST, GET, PUT, DELETE
             */
            RestDb::Instance()->setApplication(530);
            // COMMENT TOKEN IF NOT NECESSARY
            // RestDb::Instance()->setToken('service token');
            RestDb::Instance()->setModule('rkakl_sub_kegiatan');
            RestDb::Instance()->setSubModule('UpdateSubKegiatan');
            RestDb::Instance()->setAction('do');

            $restResult    = RestDb::Instance()->Send(array(
               'id' => $old_kode,
               'kode' => $this->mData['kode'],
               'nama' => $this->mData['nama']
            ), 'POST');

            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'ProgramKegiatan',
               'view',
               'html',
               array(
                  $this->mObj->_POST,
                  'Prosess update data '.GTFWConfiguration::GetValue('language', 'sub_kegiatan'). ' berhasil di jalankan',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );
            return $this->urlList;
         }else{
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'EditKomponen',
               'view',
               'html',
               array(
                  $this->mObj->_POST,
                  'Prosess Update data '.GTFWConfiguration::GetValue('language', 'sub_kegiatan'). ' Gagal di jalankan',
                  $this->cssFail
               ),
               Messenger::NextRequest
            );
            return $this->urlEdit;
         }
      }else{
         Messenger::Instance()->Send(
            Dispatcher::Instance()->mModule,
            'EditKomponen',
            'view',
            'html',
            array(
               $this->mObj->_POST,
               $check['message'],
               $this->cssFail
            ),
            Messenger::NextRequest
         );
         return $this->urlEdit;
      }
   }

   public function Delete()
   {
      if(strtoupper($this->mObj->method) === 'POST'){
         $dataId        = $this->mObj->_POST['idDelete'];
         $dataKomponen  = $this->mObj->ChangeKeyName($this->mObj->GetKegiatanRefById($dataId));
         $old_kode      = $dataKomponen['data']['kode'];
         $process       = $this->mObj->DoDeleteKegiatanRef($this->mObj->_POST['idDelete']);
         if($process === true){
            /**
             * @Description Lib Rest Client
             * @required module/rest/business/RestDb.class.php
             * @result Array
             * @param Send Array $data
             * @param Send String method : POST, GET, PUT, DELETE
             */
            RestDb::Instance()->setApplication(530);
            // COMMENT TOKEN IF NOT NECESSARY
            // RestDb::Instance()->setToken('service token');
            RestDb::Instance()->setModule('rkakl_sub_kegiatan');
            RestDb::Instance()->setSubModule('DeleteSubKegiatan');
            RestDb::Instance()->setAction('do');
            $restResult    = RestDb::Instance()->Send(array(
               'id' => $old_kode
            ), 'POST');
            $message    = 'Proses penghapusan data '.GTFWConfiguration::GetValue('language', 'sub_kegiatan').' Berhasil dijalankan';
            $style      = $this->cssDone;
         }else{
            $message    = 'Proses penghapusan data '.GTFWConfiguration::GetValue('language', 'sub_kegiatan').' Gagal dijalankan';
            $style      = $this->cssFail;
         }
      }else{
         $message       = 'Proses penghapusan data tidak bisa di akses secara langsung';
         $style         = $this->cssFail;
      }

      Messenger::Instance()->Send(
         Dispatcher::Instance()->mModule,
         'ProgramKegiatan',
         'view',
         'html',
         array(
            NULL,
            $message,
            $style
         ),
         Messenger::NextRequest
      );

      return $this->urlList;
   }
}
?>