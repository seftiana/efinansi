<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/pagu_anggaran_unit_per_mak/business/PaguAnggaranUnitPerMak.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';
class ProcessPaguAnggaranUnitPerMak
{

   public $_POST;
   public $_GET;
   protected $Obj;
   public $pageView;
   public $pageInput;
   //css hanya dipake di view
   public $cssDone = "notebox-done";
   public $cssFail = "notebox-warning";

   public $return;
   protected $decId;
   protected $encId;
   protected $method;
   protected $data   = array();

   function __construct()
   {
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
      $this->Obj        = new PaguAnggaranUnitPerMak();
      $this->decId      = Dispatcher::Instance()->Decrypt($this->_GET['dataId']);
      $this->encId      = Dispatcher::Instance()->Encrypt($this->decId);
      $this->pageView   = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         'paguAnggaranUnitPerMak',
         'view',
         'html'
      );
      $this->pageInput  = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         'inputPaguAnggaranUnitPerMak',
         'view',
         'html'
      );
      $this->pageCopy   = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         'copyPaguAnggaranUnitPerMak',
         'view',
         'html'
      );
      $userUnitKerja    = new UserUnitKerja();
      $userId           = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $this->Role       = $userUnitKerja->GetRoleUser($userId);

      if(strtolower($this->method) === 'post'){
         $this->data['id']                = $this->_POST['pagu_id'];
         $this->data['unitkerja']         = $this->_POST['satker'];
         $this->data['unitkerja_label']   = $this->_POST['satker_label'];
         $this->data['tahun_anggaran']    = $this->_POST['tahun_anggaran'];
         $this->data['program_id']        = $this->_POST['program'];
         $this->data['kegiatan_id']       = $this->_POST['kegiatan_id'];
         $this->data['kegiatan']          = $this->_POST['kegiatan'];
         $this->data['output_id']         = $this->_POST['output_id'];
         $this->data['output']            = $this->_POST['output'];
         $this->data['komponen_id']       = $this->_POST['sub_keg_id'];
         $this->data['komponen']          = $this->_POST['sub_keg'];
         $this->data['mak_id']            = $this->_POST['mak_id'];
         $this->data['mak']               = $this->_POST['mak_label'];
         $this->data['sumber_dana']       = $this->_POST['sumber_dana'];
         $this->data['sumber_dana_label'] = $this->_POST['sumber_dana_label'];
         $this->data['nominal']           = $this->_POST['nominal_pagu'];
         $this->data['status']            = $this->_POST['status_bintang'];
      }
   }

   /**
    * @Desc    Check data input sebelum di sumbit
    * @return  Array: Boolean, String Error Message
    * */
   function Check() {
      // jika tidak ada proses penyimpanan
      // tombol cancel yang di click, kembalian false
      if(!isset($this->_POST['btnsimpan'])){
         return false;
      }

      // jika ada proses penyimpanan
      $data                = $this->data;
      $checkPagu           = $this->Obj->CheckAvailabelityPaguAnggaran($data);
      $checkPaguAnggUnit   = $this->Obj->CheckPaguAnggaranUnit($data);
      if($data['tahun_anggaran'] == ''){
         $err[]      = 'Anda belum mendefinisikan Tahun Anggaran';
      }
      if($data['unitkerja'] == ''){
         $err[]      = 'Anda belum mendefinisikan Unit Kerja';
      }
      if($data['program_id'] == ''){
         $err[]      = 'Anda belum mendefinisikan Program RKAKL';
      }
      if($data['kegiatan_id'] == ''){
         $err[]      = 'Anda belum mendefinisikan Kegiatan RKAKl. Kegiatan otomatis mengikuti Output yang di pilih.';
      }
      if($data['output_id'] == ''){
         $err[]      = 'Anda belum belum mendefinisikan Output';
      }
      if($data['komponen_id'] == ''){
         $err[]      = 'Anda belum mendefinisikan Komponen';
      }
      if($data['mak_id'] == ''){
         $err[]      = 'Anda belum mendefinisikan MAK untuk PAGU';
      }
      if($data['nominal'] <= 0){
         $err[]      = 'Isikan nominal Pagu Anggaran Unit per MAK dengan nilai yang sesuai.';
      }
      // jika pagu anggaran sudah tersimpan
      // pengecekan berdasarkan unit kerja, program, kegiatan, output, komponen dan MAK
      // id pagu di perlukan untuk proses edit.
      if($checkPagu === false){
         $err[]      = 'Data Pagu Anggaran sudah ada di dalam sistem. Check kembali unit, program, kegiatan, output, kegiatan dan MAK yang Anda pakai';
      }
      // jika tidak ada pagu anggaran per unit tampilkan pesan error
      /*if((int)$checkPaguAnggUnit['count'] === 0){
         $err[]      = 'Anda belum mendefinisikan unit kerja atau Pagu anggaran belum di set untuk unit kerja yang Anda pilih.';
      }
      if((int)$checkPaguAnggUnit['count'] <> 0 AND $checkPaguAnggUnit['budget'] < $data['nominal']){
         $err[]      = 'Nominal Pagu Anggaran Unit per MAK yang Anda set melebihi batas nilai Pagu Anggaran Unit untuk Unit Kerja yang Anda Definisikan. Uang Persediaan tersisa <br /><strong>Rp. '.number_format($checkPaguAnggUnit['budget'], 0, ',','.').',-</strong>';
      }*/

      // generate pesan error untuk di kirim ke halaman view
      if(isset($err)){
         $result['return']    = false;
         $result['message']   = $err[0];
      }else{
         $result['return']    = true;
         $result['message']   = null;
      }

      return (array)$result;
   }

   function Add() {
      $cek           = $this->Check();
      $data          = $this->data;

      if($cek === false){
         return $this->pageView;
      }

      // jika tidak ada error
      if($cek['return'] === true){
         $process    = $this->Obj->DoAddPaguAnggaranUnit(
            $data['tahun_anggaran'],
            $data['unitkerja'],
            $data['nominal'],
            $data['sumber_dana'],
            $data['mak_id'],
            $data['program_id'],
            $data['kegiatan_id'],
            $data['output_id'],
            $data['komponen_id'],
            $data['status']
         );

         if($process === true){
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'paguAnggaranUnitPerMak',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Penambahan data Berhasil Dilakukan',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );
            return $this->pageView;
         }else{
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'inputPaguAnggaranUnitPerMak',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Penambahan data gagal dilakukan',
                  $this->cssFail
               ),
               Messenger::NextRequest
            );
            $this->pageInput;
         }
      }else{
         // jika ada error parse error message to input page
         Messenger::Instance()->Send(
            Dispatcher::Instance()->mModule,
            'inputPaguAnggaranUnitPerMak',
            'view',
            'html',
            array(
               $this->_POST,
               $cek['message'],
               $this->cssFail
            ),
            Messenger::NextRequest
         );

         return $this->pageInput;
      }
   }

   function Update() {
      $cek           = $this->Check();
      $data          = $this->data;

      if($cek === false){
         return $this->pageView;
      }

      if($cek['return'] === true){
         $process    = $this->Obj->DoUpdatePaguAnggaranUnit(
            $data['tahun_anggaran'],
            $data['unitkerja'],
            $data['mak_id'],
            $data['nominal'],
            $data['sumber_dana'],
            $data['program_id'],
            $data['kegiatan_id'],
            $data['output_id'],
            $data['komponen_id'],
            $data['status'],
            $data['id']
         );

         if($process === true){
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'paguAnggaranUnitPerMak',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Proses Update Data berhasil di jalankan',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );
            return $this->pageView;
         }else{
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
               'inputPaguAnggaranUnitPerMak',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Proses Update Data gagal di jalankan',
                  $this->cssFail
               ),
               Messenger::NextRequest
            );
            return $this->pageInput.'&dataId='.Dispatcher::Instance()->Encrypt($data['id']);
         }
      }else{
         // jika ada error parse error message to input page
         Messenger::Instance()->Send(
            Dispatcher::Instance()->mModule,
            'inputPaguAnggaranUnitPerMak',
            'view',
            'html',
            array(
               $this->_POST,
               $cek['message'],
               $this->cssFail
            ),
            Messenger::NextRequest
         );

         return $this->pageInput.'&dataId='.Dispatcher::Instance()->Encrypt($data['id']);
      }
   }

   function Delete() {
      $arrId   = $this->_POST['idDelete'];
      $result  = true;
      $this->Obj->StartTrans();
      $count   = sizeof($arrId);

      for($i=0;$i<$count;$i++){
         $result    &= $this->Obj->DoDeletePaguAnggaranUnitById($arrId[$i]);
      }

      if($this->Obj->EndTrans($result) === true) {
         Messenger::Instance()->Send(
            'pagu_anggaran_unit_per_mak',
            'paguAnggaranUnitPerMak',
            'view',
            'html',
            array(
               $this->_POST,
               'Penghapusan Data Berhasil Dilakukan',
               $this->cssDone
            ),Messenger::NextRequest);
      } else {
         Messenger::Instance()->Send(
            'pagu_anggaran_unit_per_mak',
            'paguAnggaranUnitPerMak',
            'view',
            'html',
            array(
               $this->_POST,
               ' Data Tidak Dapat Dihapus.'.$deleteArrData,
               $this->cssFail
            ),Messenger::NextRequest);
      }
      return $this->pageView;
   }

   function Copy()
   {
      $dataList         = array();
      $dataPagu         = array();
      if(!strtolower($this->method) === 'post'){
         return $this->pageView;
      }

      if(isset($this->_POST['btnsimpan'])){
         $dataList['srcTaId']    = $this->_POST['tahun_anggaran_asal'];
         $dataList['destTaId']   = $this->_POST['tahun_anggaran_tujuan'];
         $dataList['unitId']     = $this->_POST['satker'];
         $dataPagu               = (array)$this->_POST['pagu'];

         if($this->_POST['tahun_anggaran_asal'] == ''){
            $err[]               = 'Definisikan Periode Tahun Asal Pagu Anggaran Unit per MAK yang akan di copy';
            Messenger::Instance()->Send(
               'pagu_anggaran_unit_per_mak',
               'copyPaguAnggaranUnitPerMak',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Definisikan Periode Tahun Asal Pagu Anggaran Unit per MAK yang akan di copy',
                  $this->cssFail
               ),Messenger::NextRequest);
            return $this->pageCopy;
         }elseif($this->_POST['tahun_anggaran_tujuan'] == ''){
            Messenger::Instance()->Send(
               'pagu_anggaran_unit_per_mak',
               'copyPaguAnggaranUnitPerMak',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Definisikan Periode Tahun Tujuan Pagu Anggaran Unit per MAK yang akan di copy',
                  $this->cssFail
               ),Messenger::NextRequest);
            return $this->pageCopy;
         }elseif($this->_POST['satker'] == ''){
            Messenger::Instance()->Send(
               'pagu_anggaran_unit_per_mak',
               'copyPaguAnggaranUnitPerMak',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Definisikan Periode Tahun Tujuan Pagu Anggaran Unit per MAK yang akan di copy',
                  $this->cssFail
               ),Messenger::NextRequest);
            return $this->pageCopy;
         }else{
            $process          = $this->Obj->DoCopyPaguAnggaranUnitPerMak($dataList, $dataPagu);
            if($process === true){
               Messenger::Instance()->Send(
                  'pagu_anggaran_unit_per_mak',
                  'paguAnggaranUnitPerMak',
                  'view',
                  'html',
                  array(
                     $this->_POST,
                     'Proses copy pagu anggaran unit berhasil di laksanakan',
                     $this->cssDone
                  ),Messenger::NextRequest);
               return $this->pageView;
            }else{
               Messenger::Instance()->Send(
                  'pagu_anggaran_unit_per_mak',
                  'copyPaguAnggaranUnitPerMak',
                  'view',
                  'html',
                  array(
                     $this->_POST,
                     'Proses copy pagu anggaran unit gagal di laksanakan',
                     $this->cssFail
                  ),Messenger::NextRequest);
               return $this->pageCopy;
            }
         }
      }else{
         return $this->pageView;
      }
      /*
     if (isset($_POST['btnsimpan'])) {
      if($this->_POST['unitkerja']) {
         if ($this->_POST['perubahan_pagu'] == 'Naik'){
            $copyPaguAnggaranUnit = $this->Obj->DoCopyPaguAnggaranUnitNaik(
               $this->_POST['tahun_anggaran_tujuan'],
               $this->_POST['persen_perubahan'],
               $this->_POST['tahun_anggaran_asal'],
               $this->_POST['unitkerja']
            );
         }else{
            $copyPaguAnggaranUnit = $this->Obj->DoCopyPaguAnggaranUnitTurun(
               $this->_POST['tahun_anggaran_tujuan'],
               $this->_POST['persen_perubahan'],
               $this->_POST['tahun_anggaran_asal'],
               $this->_POST['unitkerja']
            );
         }
         if ($copyPaguAnggaranUnit === true) {
            Messenger::Instance()->Send(
               'pagu_anggaran_unit_per_mak',
               'paguAnggaranUnitPerMak',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Proses Salin Pagu Anggaran Berhasil Dilakukan',
                  $this->cssDone
               ),Messenger::NextRequest);
         } else {
            Messenger::Instance()->Send(
               'pagu_anggaran_unit_per_mak',
               'paguAnggaranUnitPerMak',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Proses Salin Pagu Anggaran Gagal',
                  $this->cssFail
               ),Messenger::NextRequest);
         }
      } else{
         Messenger::Instance()->Send(
            'pagu_anggaran_unit_per_mak',
            'copyPaguAnggaranUnitPerMak',
            'view',
            'html',
            array(
               $this->_POST,
               'Lengkapi Isian Data'
            ),Messenger::NextRequest);
         return $this->pageCopy;
      }
     }
      return $this->pageView;*/
   }
}
?>