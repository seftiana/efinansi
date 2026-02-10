<?php
/**
* @package Output
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rest/business/RestDb.class.php';

class Output
{
   protected $_POST;
   protected $_GET;
   protected $method;
   protected $mData  = array();
   private $mObj;
   private $urlList;
   private $urlInput;
   private $urlEdit;

   public $cssDone = 'notebox-done';
   public $cssFail = 'notebox-warning';
   function __construct()
   {
      $this->mObj       = new FinansiReferensi();
      $this->method     = $_SERVER['REQUEST_METHOD'];
      if(is_object($_POST)){
         $this->_POST   = $_POST->AsArray();
      }else{
         $this->_POST   = $_POST;
      }
      if(is_object($_GET)){
         $this->_GET    = $_GET->AsArray();
      }else{
         $this->_GET    = $_GET;
      }
      $this->urlList    = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ProgramKegiatan',
         'view',
         'html'
      );
      $this->urlInput   = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'AddOutput',
         'view',
         'html'
      );
      $this->urlEdit    = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'EditOutput',
         'view',
         'html'
      );

      if(strtoupper($this->method) === 'POST'){
         $this->mData['id']            = $this->_POST['data_id'];
         $this->mData['output_id']     = $this->_POST['rkakl_output_id'];
         $this->mData['output']        = $this->_POST['rkakl_output'];
         $this->mData['ta_id']         = $this->_POST['tahun_anggaran'];
         $this->mData['kegiatan_id']   = $this->_POST['kegiatan_id'];
         $this->mData['kegiatan']      = $this->_POST['kegiatan'];
         $this->mData['kegiatan_kode'] = $this->_POST['kegiatan_kode'];
         $this->mData['kode']          = trim($this->_POST['kode']);
         $this->mData['nama']          = trim($this->_POST['nama']);
      }
   }

   public function Check()
   {
      if(strtoupper($this->method) === 'POST'){
         if(isset($this->_POST['btnsimpan'])){
            if(empty($this->mData)){
               $err[]      = 'Tidak ada data yang di proses, Check kembali form input Anda';
            }
            $checkOutput   = $this->mObj->CheckSubprogByKode(
               $this->mData['kode'],
               $this->mData['kegiatan_id'],
               $this->mData['ta_id'],
               $this->mData['id']
            );
            if($this->mData['ta_id'] == ''){
               $err[]      = 'Tidak ada tahun anggaran yang aktif';
            }
            if($this->mData['kegiatan_id'] == ''){
               $err[]      = 'Pilih kegiatan dari popup kegiatan';
            }
            if($this->mData['kode'] == ''){
               $err[]      = 'Isikan Kode Output';
            }
            if($checkOutput === false){
               $err[]      = 'Kode output harus unique';
            }
            if($this->mData['nama'] == ''){
               $err[]      = 'Isikan Nama Output';
            }
            if($err){
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
      $check         = $this->Check();
      if($check === false){
         return $this->urlList;
      }

      if($check['return'] === true){
         $process    = $this->mObj->DoAddSubProgram($this->mData);
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
            RestDb::Instance()->setModule('rkakl_output');
            RestDb::Instance()->setSubModule('SaveOutput');
            RestDb::Instance()->setAction('do');

            $restResult    = RestDb::Instance()->Send(array(
               'kegiatan' => $this->mData['kegiatan_kode'],
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
                  'Proses penyimpanan data '.GTFWConfiguration::GetValue('language', 'kegiatan').' Berhasil dijalankan',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );

            return $this->urlList;
         }else{
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'AddOutput',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Proses penyimpanan data gagal dijalankan',
                  $this->cssFail
               ),
               Messenger::NextRequest
            );

            return $this->urlInput;
         }
      }else{
         Messenger::Instance()->Send(
            Dispatcher::Instance()->mModule,
            'AddOutput',
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
      $check      = $this->Check();
      if($check === false){
         return $this->urlList;
      }

      if($check['return'] === true){
         $dataId        = $this->mData['id'];
         $outputData    = $this->mObj->ChangeKeyName($this->mObj->GetSubProgramById($dataId));
         $old_kode      = $outputData['kode'];
         $process = $this->mObj->DoUpdateSubProgram($this->mData);
         // var_dump($process);
         // exit();
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
            RestDb::Instance()->setModule('rkakl_output');
            RestDb::Instance()->setSubModule('UpdateOutput');
            RestDb::Instance()->setAction('do');

            $restResult    = RestDb::Instance()->Send(array(
               'id' => $old_kode,
               'kode' => $this->mData['kode'],
               'nama' => $this->mData['nama'],
               'kegiatan' => $this->mData['kegiatan_kode']
            ), 'POST');

            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'ProgramKegiatan',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Proses Update data '.GTFWConfiguration::GetValue('language', 'kegiatan'). ' Berhasil dilaksanakan',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );

            return $this->urlList;
         }else{
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'EditOutput',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Proses Update data '.GTFWConfiguration::GetValue('language', 'kegiatan').' Gagal dijalankan',
                  $this->cssFail
               ),
               Messenger::NextRequest
            );
            return $this->urlEdit;
         }
      }else{
         Messenger::Instance()->Send(
            Dispatcher::Instance()->mModule,
            'EditOutput',
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
   }

   public function Delete()
   {
      if(strtoupper($this->method) === 'POST'){
         $dataId        = $this->_POST['idDelete'];
         $outputData    = $this->mObj->ChangeKeyName($this->mObj->GetSubProgramById($dataId));
         $old_kode      = $outputData['kode'];
         $kegiatan      = $outputData['kegiatan_kode'];

         $idDelete      = (array)$this->_POST['idDelete'];
         $process       = $this->mObj->DeleteRelatedSubProgram($idDelete);
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
            RestDb::Instance()->setModule('rkakl_output');
            RestDb::Instance()->setSubModule('DeleteOutput');
            RestDb::Instance()->setAction('do');

            $restResult    = RestDb::Instance()->Send(array(
               'id' => $old_kode,
               'kegiatan' => $kegiatan
            ), 'POST');
            $pesan      = 'Proses penghapusan data '.GTFWConfiguration::GetValue('language', 'kegiatan'). ' Berhasil di jalankan';
            $style      = $this->cssDone;
         }else{
            $pesan      = 'Proses penghapusan data '.GTFWConfiguration::GetValue('language', 'kegiatan'). ' gagal di jalankan';
            $style      = $this->cssFail;
         }
         Messenger::Instance()->Send(
            Dispatcher::Instance()->mModule,
            'ProgramKegiatan',
            'view',
            'html',
            array(
               NULL,
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