<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/'.Dispatcher::Instance()->mModule.'/business/PaguAnggaranUnitPerMak.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewInputPaguAnggaranUnitPerMak extends HtmlResponse {
   public $pagu_anggaran_unitObj;
   public $mObj;
   public $mUserUnit;
   protected $mUserId;
   protected $_POST;
   protected $_GET;
   protected $Data   = array();
   public $Pesan;
   public $css;
   protected $Role;

   public function __construct()
   {
      $this->mObj          = new PaguAnggaranUnitPerMak();
      $this->mUserUnit     = new UserUnitKerja();
      $this->mUserId       = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $this->Role          = $this->mUserUnit->GetRoleUser($this->mUserId);
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
      $this->pagu_anggaran_unitObj     = new PaguAnggaranUnitPerMak();
   }
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
      'module/'.Dispatcher::Instance()->mModule.'/template');
      $this->SetTemplateFile('input_pagu_anggaran_unit_per_mak.html');
   }

   function ProcessRequest()
   {
      $dataList                     = array();
      $unitKerjaUser                = $this->mUserUnit->GetUnitKerjaUser($this->mUserId);
      $program_combo                = $this->mObj->GetProgram();
      $arr_tahun_anggaran           = $this->mObj->GetComboTahunAnggaran(TRUE);
      $tahun_anggaran               = $this->mObj->GetTahunAnggaranAktif();
      $idDec                        = Dispatcher::Instance()->Decrypt($this->_GET['dataId']);

      $msg                          = Messenger::Instance()->Receive(__FILE__);
      if($msg){
         $this->Pesan               = $msg[0][1];
         $this->css                 = $msg[0][2];
      }

      if($idDec === NULL){
         // jika tidak ada data id, Form INPUT
         $this->Data['tahun_anggaran']    = $tahun_anggaran['id'];
         $this->Data['unit_kerja']        = $unitKerjaUser['unit_kerja_id'];
         $this->Data['unit_kerja_label']  = $unitKerjaUser['unit_kerja_nama'];
      }else{
         // jika edit data
         // ambil data detail pagu anggaran berdasar ID pagu yang di pilih
         $dataPagu                        = $this->mObj->GetDataPaguAnggaranUnitById($idDec);
         $this->Data['tahun_anggaran']    = $dataPagu['tahun_anggaran'];
         $this->Data['unit_kerja']        = $dataPagu['unitkerja'];
         $this->Data['unit_kerja_label']  = $dataPagu['unitkerja_label'];
         $this->Data['program_id']        = $dataPagu['program_id'];
         $this->Data['program_nama']      = $dataPagu['program_nama'];
         $this->Data['kegiatan_id']       = $dataPagu['kegiatan_id'];
         $this->Data['kegiatan']          = $dataPagu['kegiatan_nama'];
         $this->Data['output_id']         = $dataPagu['output_id'];
         $this->Data['output']            = $dataPagu['output_nama'];
         $this->Data['komponen_id']       = $dataPagu['sub_keg_id'];
         $this->Data['komponen']          = $dataPagu['sub_keg_nama'];
         $this->Data['sumber_dana']       = $dataPagu['sumber_dana'];
         $this->Data['sumber_dana_label'] = $dataPagu['sumber_dana_label'];
         $this->Data['mak_id']            = $dataPagu['mak_id'];
         $this->Data['mak']               = $dataPagu['mak_label'];
         $this->Data['nominal']           = $dataPagu['nominal'];
         $this->Data['pagu_id']           = $dataPagu['id'];
         $this->Data['status']            = $dataPagu['bintang'];
         $this->Data['status_checked']    = strtoupper($dataPagu['bintang']) == 'T' ? 'checked = "checked"' : '';
      }

      if(isset($msg[0][0])){
         // $this->Data                      = $msg[0][0];
         $this->Data['pagu_id']           = $msg[0][0]['pagu_id'];
         $this->Data['unit_kerja']        = $msg[0][0]['satker'];
         $this->Data['unit_kerja_label']  = $msg[0][0]['satker_label'];
         $this->Data['tahun_anggaran']    = $msg[0][0]['tahun_anggaran'];
         $this->Data['program_id']        = $msg[0][0]['program'];
         $this->Data['program_nama']      = $msg[0][0]['program_nama'];
         $this->Data['kegiatan_id']       = $msg[0][0]['kegiatan_id'];
         $this->Data['kegiatan']          = $msg[0][0]['kegiatan'];
         $this->Data['output_id']         = $msg[0][0]['output_id'];
         $this->Data['output']            = $msg[0][0]['output'];
         $this->Data['komponen_id']       = $msg[0][0]['sub_keg_id'];
         $this->Data['komponen']          = $msg[0][0]['sub_keg'];
         $this->Data['mak_id']            = $msg[0][0]['mak_id'];
         $this->Data['mak']               = $msg[0][0]['mak_label'];
         $this->Data['nominal']           = $msg[0][0]['nominal_pagu'];
         $this->Data['sumber_dana']       = $msg[0][0]['sumber_dana'];
         $this->Data['sumber_dana_label'] = $msg[0][0]['sumber_dana_label'];
         $this->Data['status']            = $msg[0][0]['status_bintang'];
         $this->Data['status_checked']    = strtoupper($msg[0][0]['status_bintang']) == 'T' ? 'checked = "checked"' : '';
      }

      foreach ($arr_tahun_anggaran as $ta) {
         if($this->Data['tahun_anggaran'] === $ta['id']){
            $this->Data['tahun_anggaran_label']    = $ta['name'];
         }
      }

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tahun_anggaran',
         array(
            'tahun_anggaran',
            $arr_tahun_anggaran,
            $this->Data['tahun_anggaran'],
            FALSE,
            ' style="width:200px;" id="tahun_anggaran"'
         ), Messenger::CurrentRequest
      );

      $return['decDataId'] = $idDec;

      return $return;
   }

   function ParseTemplate($data = NULL)
   {
      // messenger
      if ($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         $this->mrTemplate->AddVar('warning_box', 'STYLE_PESAN', $this->css);
      }
      $role                   = $this->Role;
      $dataList               = $this->Data;
      // popup
      $popup_satker           = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         'popupSatker',
         'view',
         'html'
      );
      $popup_unit       = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         'popupUnitkerja',
         'view',
         'html'
      );
      $popup_sumber     = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         'popupSumberDana',
         'view',
         'html'
      );
      $popup_mak        = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         'popupMak',
         'view',
         'html'
      );
      $popup_kegiatan   = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         'PopupKegiatan',
         'view',
         'html'
      );
      $popup_sub_keg    = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         'PopupSubKegiatan',
         'view',
         'html'
      );
      $popup_output     = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         'popupOutput',
         'view',
         'html'
      );
      $popup_program    = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupProgram',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content','URL_KEGIATAN',$popup_kegiatan);
      $this->mrTemplate->AddVar('content','URL_SUB_KEGIATAN',$popup_sub_keg);
      $this->mrTemplate->AddVar('content','URL_OUTPUT',$popup_output);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_MAK', $popup_mak);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_SUMBER_DANA', $popup_sumber);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', $popup_program);

      $this->mrTemplate->AddVar('role', 'WHOAMI', strtoupper($role['role_name']));
      $this->mrTemplate->AddVar('role', 'UNITKERJA', $dataList['unit_kerja']);
      $this->mrTemplate->AddVar('role', 'UNITKERJA_LABEL', $dataList['unit_kerja_label']);
      $this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN', $dataList['tahun_anggaran']);
      $this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', $dataList['tahun_anggaran_label']);
      $this->mrTemplate->AddVar('role', 'URL_POPUP_UNITKERJA', $popup_unit);
      $this->mrTemplate->AddVars('content', $dataList, '');

      if ($this->_GET['dataId'] == '' OR $this->Data['pagu_id'] == '') {
         $url     = "addPaguAnggaranUnitPerMak";
         $tambah  = "Tambah";
      } else {
         $url     = "updatePaguAnggaranUnitPerMak";
         $tambah  = "Ubah";
      }

      $this->mrTemplate->AddVar(
         'content',
         'URL_ACTION',
         Dispatcher::Instance()->GetUrl(
            'pagu_anggaran_unit_per_mak',
            $url,
            'do',
            'html'
         ) . "&dataId=".Dispatcher::Instance()->Encrypt($data['decDataId'])
      );
      $this->mrTemplate->AddVar('content', 'JUDUL', $tambah);

      $this->mrTemplate->AddVar(
         'content',
         'DATAID',
         Dispatcher::Instance()->Decrypt($_GET['dataId'])
      );
      $this->mrTemplate->AddVar(
         'content',
         'PAGE',
         Dispatcher::Instance()->Decrypt($_GET['page'])
      );

      # buat tombol balik
      $url_return       = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         'paguAnggaranUnitPerMak',
         'view',
         'html'
      );
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $url_return);
   }
}
?>