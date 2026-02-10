<?php
/**
* @package Kegiatan
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rest/business/RestDb.class.php';

class Kegiatan
{
   protected $_POST;
   protected $_GET;
   protected $method;
   protected $mData  = array();
   private $mObj;
   private $urlList;
   private $urlInput;
   private $urlEdit;

   public $cssDone   = 'notebox-done';
   public $cssFail   = 'notebox-warning';
   function __construct()
   {
      $this->mObj          = new FinansiReferensi();
      $this->method        = $_SERVER['REQUEST_METHOD'];
      if(is_object($_POST)){
         $this->_POST      = $_POST->AsArray();
      }else{
         $this->_POST      = $_POST;
      }
      if(is_object($_GET)){
         $this->_GET       = $_GET->AsArray();
      }else{
         $this->_GET       = $_GET;
      }
      $this->urlList       = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ProgramKegiatan',
         'view',
         'html'
      );
      $this->urlInput      = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'AddKegiatan',
         'view',
         'html'
      );
      $this->urlEdit       = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'EditKegiatan',
         'view',
         'html'
      );

      if(strtoupper($this->method) === 'POST'){
         $this->mData['id']         = $this->_POST['data_id'];
         $this->mData['ta_id']      = $this->_POST['tahun_anggaran'];
         $this->mData['kode']       = trim($this->_POST['kode']);
         $this->mData['nama']       = trim($this->_POST['nama']);
         $this->mData['indikator']  = trim($this->_POST['indikator_program']);
         $this->mData['strategi']   = trim($this->_POST['strategi_program']);
         $this->mData['kebijakan']  = trim($this->_POST['kebijakan_program']);
         $this->mData['rkakl_kegiatan_id']   = $this->_POST['rkakl_kegiatan_id'];
         $this->mData['rkakl_kegiatan']      = trim($this->_POST['rkakl_kegiatan']);
      }
   }

   public function Check()
   {
      if(strtoupper($this->method) === 'POST'){
         if(isset($this->_POST['btnsimpan'])){
            $checkUnique      = $this->mObj->CheckProgramRefByKode($this->mData['kode'], $this->mData['ta_id'], (int)$this->mData['id']);
            if($this->mData['kode'] == ''){
               $err[]         = 'Isikan Kode Kegiatan';
            }
            if($checkUnique === false){
               $err[]         = 'Kode Kegiatan harus unique';
            }
            if($this->mData['nama'] == ''){
               $err[]         = 'Isikan Nama Kegiatan';
            }
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
   }

   public function Add()
   {
      $check      = $this->Check();
      if($check === false){
         return $this->urlList;
      }

      if($check['return'] === true){
         $doSaveData       = $this->mObj->DoSaveProgramRef($this->mData);
         if($doSaveData === true){
            /**
             * @Description Lib Rest Client
             * @required module/rest/business/RestDb.class.php
             * @result Array
             * @param Send Array $data
             * @param Send String method : POST, GET, PUT, DELETE
             */
            RestDb::Instance()->setApplication(530);
            // COMMENT TOKEN IF NOT NECESSARY
            // RestDb::Instance()->setToken('');
            RestDb::Instance()->setModule('rkakl_kegiatan');
            RestDb::Instance()->setSubModule('SaveKegiatan');
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
                  $this->_POST,
                  'Proses penyimpanan data berhasil di jalankan',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );

            return $this->urlList;
         }else{
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'AddKegiatan',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Proses penyimpanan data gagal di jalankan',
                  $this->cssFail
               ),
               Messenger::NextRequest
            );
            return $this->urlInput;
         }
      }else{
         Messenger::Instance()->Send(
            Dispatcher::Instance()->mModule,
            'AddKegiatan',
            'view',
            'html',
            array(
               $this->_POST,
               $check['message'],
               $this->cssFail
            ),
            Messenger::NextRequest
         );

         return $this->urlInput;
      }

      return $this->urlList;
   }

   public function Update()
   {
      $check         = $this->Check();
      if($check === false){
         return $this->urlList;
      }

      if($check['return'] === true){
         $datakegiatan           = $this->mObj->ChangeKeyName($this->mObj->GetProgramRefById($this->mData['id']));
         $old_nama               = $datakegiatan['rkakl_kegiatan_kode'];
         $doUpdateProgramRef     = $this->mObj->DoUpdateProgramRef($this->mData);
         if($doUpdateProgramRef === true){
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
            RestDb::Instance()->setModule('rkakl_kegiatan');
            RestDb::Instance()->setSubModule('UpdateKegiatan');
            RestDb::Instance()->setAction('do');

            $restResult    = RestDb::Instance()->Send(array(
               'kode' => $this->mData['kode'],
               'nama' => $this->mData['nama'],
               'id' => $old_nama
            ), 'POST');

            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'ProgramKegiatan',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Proses update data berhasil di jalankan',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );
            return $this->urlList;
         }else{
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'EditKegiatan',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Proses update data gagal di jalankan',
                  $this->cssFail
               ),
               Messenger::NextRequest
            );
            return $this->urlEdit;
         }
      }else{
         Messenger::Instance()->Send(
            Dispatcher::Instance()->mModule,
            'EditKegiatan',
            'view',
            'html',
            array(
               $this->_POST,
               $check['message'],
               $this->cssFail
            ),
            Messenger::NextRequest
         );
         return $this->urlEdit;
      }

      return $this->urlList;
   }

   public function Delete()
   {
      if(strtoupper($this->method) === 'POST'){
         $idDelete      = (array)$this->_POST['idDelete'];
         $datakegiatan           = $this->mObj->ChangeKeyName($this->mObj->GetProgramRefById($this->_POST['idDelete']));
         $old_nama               = $datakegiatan['rkakl_kegiatan_kode'];
         $process       = $this->mObj->DeleteRelatedProgramRef($idDelete);
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
            RestDb::Instance()->setModule('rkakl_kegiatan');
            RestDb::Instance()->setSubModule('DeleteKegiatan');
            RestDb::Instance()->setAction('do');

            $restResult    = RestDb::Instance()->Send(array(
               'id' => $old_nama
            ), 'POST');
            $pesan      = 'Proses penghapusan data berhasil di jalankan';
            $style      = $this->cssDone;
         }else{
            $pesan      = 'Proses penghapusan data gagal di jalankan';
            $style      = $this->cssFail;
         }

         Messenger::Instance()->Send(
            Dispatcher::Instance()->mModule,
            'ProgramKegiatan',
            'view',
            'html',
            array(
               $this->_POST,
               $pesan,
               $style
            ),
            Messenger::NextRequest
         );
      }

      return $this->urlList;
   }
}
?>