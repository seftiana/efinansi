<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/pagu_anggaran_unit_per_mak/business/PaguAnggaranUnitPerMak.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewCopyPaguAnggaranUnitPerMak extends HtmlResponse
{
   public $mObj;
   public $objUserUnit;
   public $_GET;
   public $_POST;
   public $Data   = array();
   public $Pesan;
   public $Style  = 'notebox-warning';
   public $Role;
   protected $mUserId;

   public function __construct()
   {
      $this->mObj          = new PaguAnggaranUnitPerMak();
      $this->objUserUnit   = new UserUnitKerja();
      $this->_GET          = $_GET->AsArray();
      $this->_POST         = $_POST->AsArray();
      $this->mUserId       = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $this->Role          = $this->objUserUnit->GetRoleUser($this->mUserId);
   }

   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/pagu_anggaran_unit_per_mak/template');
      $this->SetTemplateFile('copy_pagu_anggaran_unit_per_mak.html');
   }

   function ProcessRequest()
   {
      $objTahunAnggaran    = $this->mObj->GetDataTahunAnggaran();
      $arrTahunAnggaran    = $this->mObj->GetComboTahunAnggaran();
      $arrPeriodeTahunPagu = $this->mObj->GetPeriodeTahun();
      $arrPeriodeTahun     = $this->mObj->GetPeriodeTahun(false);
      $list_status      = array(
         array(
            'id'=>'Naik',
            'name'=>'Naik'
         ),
         array(
            'id'=>'Turun',
            'name'=>'Turun'
         )
      );
      $dataList                     = array();
      $dataPaguObj                  = array();
      $userUnitKerja                = $this->objUserUnit->GetUnitKerjaUser($this->mUserId);
      $dataList['unitkerja']        = $userUnitKerja['unit_kerja_id'];
      $dataList['unitkerja_label']  = $userUnitKerja['unit_kerja_nama'];

      switch (strtolower($this->Role['role_name'])) {
         case 'administrator':
            $dataList['unitkerja']        = '';
            $dataList['unitkerja_label']  = '';
            break;
         case 'operatorunit':
            $dataList['unitkerja']        = '';
            $dataList['unitkerja_label']  = '';
            break;
         case 'operatorsubunit':
            $dataList['unitkerja']        = $userUnitKerja['unit_kerja_id'];
            $dataList['unitkerja_label']  = $userUnitKerja['unit_kerja_nama'];
         default:
            $dataList['unitkerja']        = '';
            $dataList['unitkerja_label']  = '';
            break;
      }
      // Messenger
      $msg                 = Messenger::Instance()->Receive(__FILE__);

      if($msg){
         $messengerData    = $msg[0][0];
         $messengerPesan   = $msg[0][1];
         $messengerStyle   = $msg[0][2];

         $dataList['id']               = (int)$messengerData['pagu_id'];
         $dataList['src_ta']           = (int)$messengerData['tahun_anggaran_asal'];
         $dataList['dest_ta']          = (int)$messengerData['tahun_anggaran_tujuan'];
         $dataList['unitkerja']        = $messengerData['satker'];
         $dataList['unitkerja_label']  = $messengerData['satker_label'];
         $dataList['persentase']       = $messengerData['perubahan_pagu'];
         if($dataList['src_ta'] != '' && $dataList['dest_ta'] != '' && $dataList['unitkerja'] != ''){
            $dataPaguObj              = $this->mObj->GetListPaguAnggaran(array(
               'srcTaId' => (int)$messengerData['tahun_anggaran_asal'],
               'destTaId' => (int)$messengerData['tahun_anggaran_tujuan'],
               'unitId' => $messengerData['satker']
            ));
            $index      = 0;
            if(!empty($messengerData['pagu'])){
               foreach ($dataPaguObj as $pagu) {
                  $dataPaguObj[$index]['type']     = $messengerData['pagu'][$pagu['id']]['tipe'];
                  $dataPaguObj[$index]['persen']   = $messengerData['pagu'][$pagu['id']]['persen'];
                  $dataPaguObj[$index]['nilai']    = $messengerData['pagu'][$pagu['id']]['nominal'];
                  $index++;
               }
            }
         }
      }


      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tahun_anggaran_asal',
         array(
            'tahun_anggaran_asal',
            $arrPeriodeTahunPagu,
            $dataList['src_ta'],
            false,
            ' style="width:215px;" id="src_periode_tahun"'
         ), Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tahun_anggaran_tujuan',
         array(
            'tahun_anggaran_tujuan',
            $arrPeriodeTahun,
            $dataList['dest_ta'],
            false,
            ' style="width:215px;" id="dest_periode_tahun"'
         ), Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'perubahan_pagu',
         array(
            'perubahan_pagu',
            $list_status,
            $dataList['persentase'],
            false,
            'style="width:85px;"'
         ), Messenger::CurrentRequest);

      $return['obj_periode_tahun']['data']   = json_encode($objTahunAnggaran);
      $return['obj_data_pagu']['data']       = json_encode($dataPaguObj);
      $return['dataList']     = $dataList;
      $return['message']      = $messengerPesan;
      $return['style']        = $messengerStyle;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $messengerPesan         = $data['message'];
      $messengerStyle         = $data['style'];
      if ($messengerPesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $messengerPesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $messengerStyle);
      }
      $objTahunPeriode  = $data['obj_periode_tahun'];
      $objDataPagu      = $data['obj_data_pagu'];
      $role             = $this->Role;
      $dataList         = $data['dataList'];
      $popup_unit       = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         'popupUnitkerja',
         'view',
         'html'
      );
      $urlDataPagu      = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         'DataPaguCopy',
         'view',
         'json'
      );

      $this->mrTemplate->AddVars('content', $objTahunPeriode, 'PERIODETAHUN_');
      $this->mrTemplate->AddVars('content', $objDataPagu, 'PAGU_ANGGARAN_');
      $this->mrTemplate->AddVar('role', 'WHOAMI', strtoupper($role['role_name']));
      $this->mrTemplate->AddVar('role', 'URL_POPUP_UNITKERJA', $popup_unit);
      $this->mrTemplate->AddVar('role', 'UNITKERJA', $dataList['unitkerja']);
      $this->mrTemplate->AddVar('role', 'UNITKERJA_LABEL', $dataList['unitkerja_label']);
      $this->mrTemplate->AddVar('content', 'URL_DATA_PAGU', $urlDataPagu);

      $url     = "copyPaguAnggaranUnitPerMak";
      $tambah  = "Salin";

      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         $url,
         'do',
         'html'
      ) . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));

      $this->mrTemplate->AddVar('content', 'JUDUL', $tambah);

   }
}
?>