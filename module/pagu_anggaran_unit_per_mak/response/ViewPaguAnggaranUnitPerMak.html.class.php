<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/pagu_anggaran_unit_per_mak/business/PaguAnggaranUnitPerMak.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewPaguAnggaranUnitPerMak extends HtmlResponse {
   protected $_POST;
   protected $_GET;
   protected $mUserId;
   protected $mRoleUser    = array();
   public $mObj;
   public $mUserUnit;

   public $Pesan;
   public $Data;
   public $Search;
   public $paguanggaranunitObj;

   public function __construct()
   {
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
      $this->mObj          = new PaguAnggaranUnitPerMak();
      $this->mUserUnit     = new UserUnitKerja();
      $this->mUserId       = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $this->mRoleUser     = $this->mUserUnit->GetRoleUser($this->mUserId);
   }
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot')
      .'module/pagu_anggaran_unit_per_mak/template');
      $this->SetTemplateFile('view_pagu_anggaran_unit_per_mak.html');
   }

   function ProcessRequest()
   {
      $messengerMsg = $messengerStyle = $messengerData = NULL;
      $userId                 = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $mObj                   = new PaguAnggaranUnitPerMak();
      $mUnitObj               = new UserUnitKerja();
      $role                   = $mUnitObj->GetRoleUser($userId);
      $post                   = array();
      $unitKerja              = $mUnitObj->GetUnitKerjaUser($userId);
      $arrTahunAnggaran       = $mObj->GetComboTahunAnggaran();
      $tahunAnggaran          = $mObj->GetTahunAnggaranAktif();
      // messagenger
      $msg                    = Messenger::Instance()->Receive(__FILE__);
      if($msg){
         $messengerMsg        = $msg[0][1];
         $messengerStyle      = $msg[0][2];
      }

      if(isset($mObj->_POST['btnTampilkan'])){
         $post['ta']          = $mObj->_POST['tahun_anggaran'];
         $post['unit_id']     = $mObj->_POST['satker'];
         $post['unit']        = $mObj->_POST['satker_label'];
         $post['programId']   = $mObj->_POST['programId'];
         $post['program']     = $mObj->_POST['program'];
         $post['kegiatanId']  = $mObj->_POST['kegiatanId'];
         $post['kegiatan']    = $mObj->_POST['kegiatan'];
         $post['outputId']    = $mObj->_POST['outputId'];
         $post['output']      = $mObj->_POST['output'];
         $post['komponenId']  = $mObj->_POST['komponenId'];
         $post['komponen']    = $mObj->_POST['komponen'];
         $post['makId']       = $mObj->_POST['makId'];
         $post['mak']         = $mObj->_POST['mak'];
      }elseif(isset($mObj->_GET['search'])){
         $post['ta']          = Dispatcher::Instance()->Decrypt($mObj->_GET['ta']);
         $post['unit_id']     = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $post['unit']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit']);
         $post['programId']   = Dispatcher::Instance()->Decrypt($mObj->_GET['programId']);
         $post['program']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program']);
         $post['kegiatanId']  = Dispatcher::Instance()->Decrypt($mObj->_GET['kegiatanId']);
         $post['kegiatan']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kegiatan']);
         $post['outputId']    = Dispatcher::Instance()->Decrypt($mObj->_GET['outputId']);
         $post['output']      = Dispatcher::Instance()->Decrypt($mObj->_GET['output']);
         $post['komponenId']  = Dispatcher::Instance()->Decrypt($mObj->_GET['komponenId']);
         $post['komponen']    = Dispatcher::Instance()->Decrypt($mObj->_GET['komponen']);
         $post['makId']       = Dispatcher::Instance()->Decrypt($mObj->_GET['makId']);
         $post['mak']         = Dispatcher::Instance()->Decrypt($mObj->_GET['mak']);
      }else{
         $post['ta']          = $tahunAnggaran['id'];
         $post['unit_id']     = $unitKerja['unit_kerja_id'];
         $post['unit']        = $unitKerja['unit_kerja_nama'];
         $post['programId']   = '';
         $post['program']     = '';
         $post['kegiatanId']  = '';
         $post['kegiatan']    = '';
         $post['outputId']    = '';
         $post['output']      = '';
         $post['komponenId']  = '';
         $post['komponen']    = '';
         $post['makId']       = '';
         $post['mak']         = '';
      }

      foreach ($arrTahunAnggaran as $ta) {
         if($ta['id'] == $post['ta']){
            $post['ta_label'] = $ta['name'];
         }
      }

      // combobox tahun anggaran
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tahun_anggaran',
         array(
            'tahun_anggaran',
            $arrTahunAnggaran,
            $post['ta'],
            '-',
            ' style="width:200px;"
            id="tahun_anggaran"'),
         Messenger::CurrentRequest
      );

      // build uri
      foreach ($post as $key => $value) {
         $query[$key]   = Dispatcher::Instance()->Encrypt($value);
      }
      $uri              = urldecode(http_build_query($query));

      $offset           = 0;
      $limit            = 20;
      $page             = 0;
      if(isset($_GET['page'])){
         $page          = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset        = ($page - 1) * $limit;
      }
      #paging url
      $url              = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$uri;

      $destination_id   = "subcontent-element";
      $dataList         = $this->mObj->GetData($offset, $limit, $post);
      $total_data       = $this->mObj->Count();
      #send data to pagging component
      Messenger::Instance()->SendToComponent(
         'paging',
         'Paging',
         'view',
         'html',
         'paging_top',
         array(
            $limit,
            $total_data,
            $url,
            $page,
            $destination_id
         ),
         Messenger::CurrentRequest
      );

      #send data to pagging component
      Messenger::Instance()->SendToComponent(
         'paging',
         'Paging',
         'view',
         'html',
         'paging_bottom',
         array(
            $limit,
            $total_data,
            $url,
            $page,
            $destination_id
         ),
         Messenger::CurrentRequest
      );

      $return['dataList']     = $this->mObj->ChangeKeyName($dataList);
      $return['start']        = $offset+1;
      $return['post']         = $post;
      $return['role']         = $role;
      $return['uri']          = $uri;
      return $return;
   }

   function ParseTemplate($data = NULL)
   {
      $role             = $this->mRoleUser;
      $search           = $data['post'];
      $dataList         = $data['dataList'];
      $start            = $data['start'];
      $uri              = $data['uri'];
      $page             = 1;
      if(isset($_GET['page'])){
         $page          = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
      }

      $urlSearch        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      );
      $url_add          = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'inputPaguAnggaranUnitPerMak',
         'view',
         'html'
      ).'&page='.$page.'&'.$uri;

      $popup_unit       = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupUnitkerja',
         'view',
         'html'
      );

      $urlPopupProgram     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupProgram',
         'view',
         'html'
      );

      $urlPopupRkaklOutput = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupRkaklOutput',
         'view',
         'html'
      );

      $urlPopupRkaklSubKegiatan  = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupRkaklSubKegiatan',
         'view',
         'html'
      );

      $urlPopupReferensiMak      = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupReferensiMak',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', $urlPopupProgram);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_RKAKL_OUTPUT', $urlPopupRkaklOutput);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_RKAKL_SUB_KEGIATAN', $urlPopupRkaklSubKegiatan);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_REFERENSI_MAK', $urlPopupReferensiMak);

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('role', 'WHOAMI', strtoupper($role['role_name']));
      $this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN', $search['ta']);
      $this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', $search['ta_label']);
      $this->mrTemplate->AddVar('role', 'SATKER', $search['unit_id']);
      $this->mrTemplate->AddVar('role', 'SATKER_LABEL', $search['unit']);
      $this->mrTemplate->AddVar('role', 'URL_POPUP_UNIT_KERJA', $popup_unit);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $url_add);
      $this->mrTemplate->AddVar('content', 'PROGRAM_ID', $search['programId']);
      $this->mrTemplate->AddVar('content', 'PROGRAM', $search['program']);
      $this->mrTemplate->AddVar('content', 'KEGIATAN_ID', $search['kegiatanId']);
      $this->mrTemplate->AddVar('content', 'KEGIATAN', $search['kegiatan']);
      $this->mrTemplate->AddVar('content', 'OUTPUT_ID', $search['outputId']);
      $this->mrTemplate->AddVar('content', 'OUTPUT', $search['output']);
      $this->mrTemplate->AddVar('content', 'KOMPONEN_ID', $search['komponenId']);
      $this->mrTemplate->AddVar('content', 'KOMPONEN', $search['komponen']);
      $this->mrTemplate->AddVar('content', 'MAK_ID', $search['makId']);
      $this->mrTemplate->AddVar('content', 'MAK', $search['mak']);
      $this->mrTemplate->AddVar(
         'content',
         'URL_COPY',
         Dispatcher::Instance()->GetUrl(
            'pagu_anggaran_unit_per_mak',
            'copyPaguAnggaranUnitPerMak',
            'view',
            'html'
         ).'&page='.$page.'&'.$uri
      );
      // message
      if($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
      }
      // end of message

      // setting multiple delete
      $label      = "Pagu Anggaran Unit Per Mak";
      $urlDelete  = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'deletePaguAnggaranUnitPerMak',
         'do',
         'html'
      );
      $urlReturn = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'paguAnggaranUnitPerMak',
         'view',
         'html'
      );
      Messenger::Instance()->Send(
         'confirm',
         'confirmDelete',
         'do',
         'html',
         array(
            $label,
            $urlDelete,
            $urlReturn
         ),Messenger::NextRequest
      );
      $this->mrTemplate->AddVar(
         'content',
         'URL_DELETE',
         Dispatcher::Instance()->GetUrl(
            'confirm',
            'confirmDelete',
            'do',
            'html'
         )
      );

      $this->mrTemplate->AddVar('content', 'CONTROL_LABEL', $label);
      $this->mrTemplate->AddVar('content', 'CONTROL_ACTION', $urlDelete);
      $this->mrTemplate->AddVar('content', 'CONTROL_RETURN', $urlReturn);
      // end setting multiple delete
      // parse data to template
      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_list', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_list', 'DATA_EMPTY', 'NO');

         // inisialisasi
         $i             = 0;
         $index         = 0;
         $satker        = '';
         $unit          = '';
         $fakultas      = '';
         $jurusan       = '';
         $prodi         = '';
         // inisialisasi total nominal
         $totalPagu        = 0;
         $totalPok         = 0;
         $totalBudget      = 0;
         $totalRealisasi   = 0;

         $dataLists     = array();
         for($i = 0; $i < count($dataList);){
            if($dataList[$i]['satker_id'] == $satker && $dataList[$i]['unit_id'] == $unit && $dataList[$i]['fakultas_id'] == $fakultas && $dataList[$i]['jurusan_id'] == $jurusan && $dataList[$i]['prodi_id'] != null){
               // data program studi
               $totalPagu        += $dataList[$i]['nominal'];
               $totalPok         += $dataList[$i]['nominal_pok'];
               $totalRealisasi   += $dataList[$i]['nominal_realisasi'];
               $totalBudget      += $dataList[$i]['nominal_tersedia'];

               $dataLists[$index]['unit_id']             = $dataList[$i]['prodi_id'];
               $dataLists[$index]['unit_kode']           = $dataList[$i]['prodi_kode'];
               $dataLists[$index]['unit_nama']           = $dataList[$i]['prodi_nama'];
               $dataLists[$index]['nomor']               = $start+$i;
               $dataLists[$index]['id']                  = $dataList[$i]['pagu_angg_unit_id'];
               $dataLists[$index]['program_id']          = $dataList[$i]['program_id'];
               $dataLists[$index]['program_kode']        = $dataList[$i]['program_kode'];
               $dataLists[$index]['program_nama']        = $dataList[$i]['program_nama'];
               $dataLists[$index]['kegiatan_id']         = $dataList[$i]['kegiatan_id'];
               $dataLists[$index]['kegiatan_kode']       = $dataList[$i]['kegiatan_kode'];
               $dataLists[$index]['kegiatan_nama']       = $dataList[$i]['kegiatan_nama'];
               $dataLists[$index]['output_id']           = $dataList[$i]['output_id'];
               $dataLists[$index]['output_kode']         = $dataList[$i]['output_kode'];
               $dataLists[$index]['output_nama']         = $dataList[$i]['output_nama'];
               $dataLists[$index]['sub_kegiatan_id']     = $dataList[$i]['sub_kegiatan_id'];
               $dataLists[$index]['sub_kegiatan_kode']   = $dataList[$i]['sub_kegiatan_kode'];
               $dataLists[$index]['nama_komponen']       = $dataList[$i]['sub_kegiatan_nama'];
               $dataLists[$index]['mak_id']              = $dataList[$i]['mak_id'];
               $dataLists[$index]['mak_kode']            = $dataList[$i]['mak_kode'];
               $dataLists[$index]['mak_nama']            = $dataList[$i]['mak_nama'];
               $dataLists[$index]['thanggar_id']         = $dataList[$i]['thanggar_id'];
               $dataLists[$index]['thanggar_nama']       = $dataList[$i]['thanggar_nama'];
               $dataLists[$index]['sumberdana_nama']     = $dataList[$i]['sumberdana_nama'];
               $dataLists[$index]['nominal']             = $dataList[$i]['nominal'];
               $dataLists[$index]['nominal_tersedia']    = $dataList[$i]['nominal_tersedia'];
               $dataLists[$index]['nominal_pok']         = $dataList[$i]['nominal_pok'];
               $dataLists[$index]['nominal_realisasi']   = $dataList[$i]['nominal_realisasi'];
               $dataLists[$index]['pok']                 = $dataList[$i]['pok'];
               $dataLists[$index]['has_spj']             = $dataList[$i]['has_spj'];
               $dataLists[$index]['pagu_kode']           = $dataList[$i]['pagu_kode'];
               $dataLists[$index]['status']              = $dataList[$i]['status'];
               $dataLists[$index]['class_name']          = ($i % 2 <> 0) ? 'table-common-even' : '';
               $dataLists[$index]['data_type']           = 'child';
               $i++;
            }elseif($dataList[$i]['satker_id'] == $satker && $dataList[$i]['unit_id'] == $unit && $dataList[$i]['fakultas_id'] == $fakultas && $dataList[$i]['jurusan_id'] != null && $dataList[$i]['prodi_id'] == null){
               // data untuk jurusan
               $totalPagu        += $dataList[$i]['nominal'];
               $totalPok         += $dataList[$i]['nominal_pok'];
               $totalRealisasi   += $dataList[$i]['nominal_realisasi'];
               $totalBudget      += $dataList[$i]['nominal_tersedia'];

               $dataLists[$index]['unit_id']             = $dataList[$i]['jurusan_id'];
               $dataLists[$index]['unit_kode']           = $dataList[$i]['jurusan_kode'];
               $dataLists[$index]['unit_nama']           = $dataList[$i]['jurusan_nama'];
               $dataLists[$index]['nomor']               = $start+$i;
               $dataLists[$index]['id']                  = $dataList[$i]['pagu_angg_unit_id'];
               $dataLists[$index]['program_id']          = $dataList[$i]['program_id'];
               $dataLists[$index]['program_kode']        = $dataList[$i]['program_kode'];
               $dataLists[$index]['program_nama']        = $dataList[$i]['program_nama'];
               $dataLists[$index]['kegiatan_id']         = $dataList[$i]['kegiatan_id'];
               $dataLists[$index]['kegiatan_kode']       = $dataList[$i]['kegiatan_kode'];
               $dataLists[$index]['kegiatan_nama']       = $dataList[$i]['kegiatan_nama'];
               $dataLists[$index]['output_id']           = $dataList[$i]['output_id'];
               $dataLists[$index]['output_kode']         = $dataList[$i]['output_kode'];
               $dataLists[$index]['output_nama']         = $dataList[$i]['output_nama'];
               $dataLists[$index]['sub_kegiatan_id']     = $dataList[$i]['sub_kegiatan_id'];
               $dataLists[$index]['sub_kegiatan_kode']   = $dataList[$i]['sub_kegiatan_kode'];
               $dataLists[$index]['nama_komponen']       = $dataList[$i]['sub_kegiatan_nama'];
               $dataLists[$index]['mak_id']              = $dataList[$i]['mak_id'];
               $dataLists[$index]['mak_kode']            = $dataList[$i]['mak_kode'];
               $dataLists[$index]['mak_nama']            = $dataList[$i]['mak_nama'];
               $dataLists[$index]['thanggar_id']         = $dataList[$i]['thanggar_id'];
               $dataLists[$index]['thanggar_nama']       = $dataList[$i]['thanggar_nama'];
               $dataLists[$index]['sumberdana_nama']     = $dataList[$i]['sumberdana_nama'];
               $dataLists[$index]['nominal']             = $dataList[$i]['nominal'];
               $dataLists[$index]['nominal_tersedia']    = $dataList[$i]['nominal_tersedia'];
               $dataLists[$index]['nominal_pok']         = $dataList[$i]['nominal_pok'];
               $dataLists[$index]['nominal_realisasi']   = $dataList[$i]['nominal_realisasi'];
               $dataLists[$index]['pok']                 = $dataList[$i]['pok'];
               $dataLists[$index]['has_spj']             = $dataList[$i]['has_spj'];
               $dataLists[$index]['pagu_kode']           = $dataList[$i]['pagu_kode'];
               $dataLists[$index]['status']              = $dataList[$i]['status'];
               $dataLists[$index]['class_name']          = ($i % 2 <> 0) ? 'table-common-even' : '';
               $dataLists[$index]['data_type']           = 'child';
               $i++;
            }elseif($dataList[$i]['satker_id'] == $satker && $dataList[$i]['unit_id'] == $unit && $dataList[$i]['fakultas_id'] != null && $dataList[$i]['jurusan_id'] == null && $dataList[$i]['prodi_id'] == null){
               // data fakultas
               $totalPagu        += $dataList[$i]['nominal'];
               $totalPok         += $dataList[$i]['nominal_pok'];
               $totalRealisasi   += $dataList[$i]['nominal_realisasi'];
               $totalBudget      += $dataList[$i]['nominal_tersedia'];

               $dataLists[$index]['unit_id']             = $dataList[$i]['fakultas_id'];
               $dataLists[$index]['unit_kode']           = $dataList[$i]['fakultas_kode'];
               $dataLists[$index]['unit_nama']           = $dataList[$i]['fakultas_nama'];
               $dataLists[$index]['nomor']               = $start+$i;
               $dataLists[$index]['id']                  = $dataList[$i]['pagu_angg_unit_id'];
               $dataLists[$index]['program_id']          = $dataList[$i]['program_id'];
               $dataLists[$index]['program_kode']        = $dataList[$i]['program_kode'];
               $dataLists[$index]['program_nama']        = $dataList[$i]['program_nama'];
               $dataLists[$index]['kegiatan_id']         = $dataList[$i]['kegiatan_id'];
               $dataLists[$index]['kegiatan_kode']       = $dataList[$i]['kegiatan_kode'];
               $dataLists[$index]['kegiatan_nama']       = $dataList[$i]['kegiatan_nama'];
               $dataLists[$index]['output_id']           = $dataList[$i]['output_id'];
               $dataLists[$index]['output_kode']         = $dataList[$i]['output_kode'];
               $dataLists[$index]['output_nama']         = $dataList[$i]['output_nama'];
               $dataLists[$index]['sub_kegiatan_id']     = $dataList[$i]['sub_kegiatan_id'];
               $dataLists[$index]['sub_kegiatan_kode']   = $dataList[$i]['sub_kegiatan_kode'];
               $dataLists[$index]['nama_komponen']       = $dataList[$i]['sub_kegiatan_nama'];
               $dataLists[$index]['mak_id']              = $dataList[$i]['mak_id'];
               $dataLists[$index]['mak_kode']            = $dataList[$i]['mak_kode'];
               $dataLists[$index]['mak_nama']            = $dataList[$i]['mak_nama'];
               $dataLists[$index]['thanggar_id']         = $dataList[$i]['thanggar_id'];
               $dataLists[$index]['thanggar_nama']       = $dataList[$i]['thanggar_nama'];
               $dataLists[$index]['sumberdana_nama']     = $dataList[$i]['sumberdana_nama'];
               $dataLists[$index]['nominal']             = $dataList[$i]['nominal'];
               $dataLists[$index]['nominal_tersedia']    = $dataList[$i]['nominal_tersedia'];
               $dataLists[$index]['nominal_pok']         = $dataList[$i]['nominal_pok'];
               $dataLists[$index]['nominal_realisasi']   = $dataList[$i]['nominal_realisasi'];
               $dataLists[$index]['pok']                 = $dataList[$i]['pok'];
               $dataLists[$index]['has_spj']             = $dataList[$i]['has_spj'];
               $dataLists[$index]['pagu_kode']           = $dataList[$i]['pagu_kode'];
               $dataLists[$index]['status']              = $dataList[$i]['status'];
               $dataLists[$index]['class_name']          = ($i % 2 <> 0) ? 'table-common-even' : '';
               $dataLists[$index]['data_type']           = 'child';
               $i++;
            }elseif($dataList[$i]['satker_id'] == $satker && $dataList[$i]['unit_id'] != null && $dataList[$i]['fakultas_id'] == null && $dataList[$i]['jurusan_id'] == null && $dataList[$i]['prodi_id'] == null){
               // data unit kerja
               $totalPagu        += $dataList[$i]['nominal'];
               $totalPok         += $dataList[$i]['nominal_pok'];
               $totalRealisasi   += $dataList[$i]['nominal_realisasi'];
               $totalBudget      += $dataList[$i]['nominal_tersedia'];

               $dataLists[$index]['unit_id']             = $dataList[$i]['unit_id'];
               $dataLists[$index]['unit_kode']           = $dataList[$i]['unit_kode'];
               $dataLists[$index]['unit_nama']           = $dataList[$i]['unit_nama'];
               $dataLists[$index]['nomor']               = $start+$i;
               $dataLists[$index]['id']                  = $dataList[$i]['pagu_angg_unit_id'];
               $dataLists[$index]['program_id']          = $dataList[$i]['program_id'];
               $dataLists[$index]['program_kode']        = $dataList[$i]['program_kode'];
               $dataLists[$index]['program_nama']        = $dataList[$i]['program_nama'];
               $dataLists[$index]['kegiatan_id']         = $dataList[$i]['kegiatan_id'];
               $dataLists[$index]['kegiatan_kode']       = $dataList[$i]['kegiatan_kode'];
               $dataLists[$index]['kegiatan_nama']       = $dataList[$i]['kegiatan_nama'];
               $dataLists[$index]['output_id']           = $dataList[$i]['output_id'];
               $dataLists[$index]['output_kode']         = $dataList[$i]['output_kode'];
               $dataLists[$index]['output_nama']         = $dataList[$i]['output_nama'];
               $dataLists[$index]['sub_kegiatan_id']     = $dataList[$i]['sub_kegiatan_id'];
               $dataLists[$index]['sub_kegiatan_kode']   = $dataList[$i]['sub_kegiatan_kode'];
               $dataLists[$index]['nama_komponen']       = $dataList[$i]['sub_kegiatan_nama'];
               $dataLists[$index]['mak_id']              = $dataList[$i]['mak_id'];
               $dataLists[$index]['mak_kode']            = $dataList[$i]['mak_kode'];
               $dataLists[$index]['mak_nama']            = $dataList[$i]['mak_nama'];
               $dataLists[$index]['thanggar_id']         = $dataList[$i]['thanggar_id'];
               $dataLists[$index]['thanggar_nama']       = $dataList[$i]['thanggar_nama'];
               $dataLists[$index]['sumberdana_nama']     = $dataList[$i]['sumberdana_nama'];
               $dataLists[$index]['nominal']             = $dataList[$i]['nominal'];
               $dataLists[$index]['nominal_tersedia']    = $dataList[$i]['nominal_tersedia'];
               $dataLists[$index]['nominal_pok']         = $dataList[$i]['nominal_pok'];
               $dataLists[$index]['nominal_realisasi']   = $dataList[$i]['nominal_realisasi'];
               $dataLists[$index]['pok']                 = $dataList[$i]['pok'];
               $dataLists[$index]['has_spj']             = $dataList[$i]['has_spj'];
               $dataLists[$index]['pagu_kode']           = $dataList[$i]['pagu_kode'];
               $dataLists[$index]['status']              = $dataList[$i]['status'];
               $dataLists[$index]['class_name']          = ($i % 2 <> 0) ? 'table-common-even' : '';
               $dataLists[$index]['data_type']           = 'child';
               $i++;
            }elseif($dataList[$i]['satker_id'] != null && $dataList[$i]['unit_id'] == null && $dataList[$i]['fakultas_id'] == null && $dataList[$i]['jurusan_id'] == null && $dataList[$i]['prodi_id'] == null){
               // data satuan kerja
               $totalPagu        += $dataList[$i]['nominal'];
               $totalPok         += $dataList[$i]['nominal_pok'];
               $totalRealisasi   += $dataList[$i]['nominal_realisasi'];
               $totalBudget      += $dataList[$i]['nominal_tersedia'];

               $dataLists[$index]['unit_id']             = $dataList[$i]['satker_id'];
               $dataLists[$index]['unit_kode']           = $dataList[$i]['satker_kode'];
               $dataLists[$index]['unit_nama']           = $dataList[$i]['satker_nama'];
               $dataLists[$index]['nomor']               = $start+$i;
               $dataLists[$index]['id']                  = $dataList[$i]['pagu_angg_unit_id'];
               $dataLists[$index]['program_id']          = $dataList[$i]['program_id'];
               $dataLists[$index]['program_kode']        = $dataList[$i]['program_kode'];
               $dataLists[$index]['program_nama']        = $dataList[$i]['program_nama'];
               $dataLists[$index]['kegiatan_id']         = $dataList[$i]['kegiatan_id'];
               $dataLists[$index]['kegiatan_kode']       = $dataList[$i]['kegiatan_kode'];
               $dataLists[$index]['kegiatan_nama']       = $dataList[$i]['kegiatan_nama'];
               $dataLists[$index]['output_id']           = $dataList[$i]['output_id'];
               $dataLists[$index]['output_kode']         = $dataList[$i]['output_kode'];
               $dataLists[$index]['output_nama']         = $dataList[$i]['output_nama'];
               $dataLists[$index]['sub_kegiatan_id']     = $dataList[$i]['sub_kegiatan_id'];
               $dataLists[$index]['sub_kegiatan_kode']   = $dataList[$i]['sub_kegiatan_kode'];
               $dataLists[$index]['nama_komponen']       = $dataList[$i]['sub_kegiatan_nama'];
               $dataLists[$index]['mak_id']              = $dataList[$i]['mak_id'];
               $dataLists[$index]['mak_kode']            = $dataList[$i]['mak_kode'];
               $dataLists[$index]['mak_nama']            = $dataList[$i]['mak_nama'];
               $dataLists[$index]['thanggar_id']         = $dataList[$i]['thanggar_id'];
               $dataLists[$index]['thanggar_nama']       = $dataList[$i]['thanggar_nama'];
               $dataLists[$index]['sumberdana_nama']     = $dataList[$i]['sumberdana_nama'];
               $dataLists[$index]['nominal']             = $dataList[$i]['nominal'];
               $dataLists[$index]['nominal_tersedia']    = $dataList[$i]['nominal_tersedia'];
               $dataLists[$index]['nominal_pok']         = $dataList[$i]['nominal_pok'];
               $dataLists[$index]['nominal_realisasi']   = $dataList[$i]['nominal_realisasi'];
               $dataLists[$index]['pok']                 = $dataList[$i]['pok'];
               $dataLists[$index]['has_spj']             = $dataList[$i]['has_spj'];
               $dataLists[$index]['pagu_kode']           = $dataList[$i]['pagu_kode'];
               $dataLists[$index]['status']              = $dataList[$i]['status'];
               $dataLists[$index]['class_name']          = ($i % 2 <> 0) ? 'table-common-even' : '';
               $dataLists[$index]['data_type']           = 'child';
               $i++;
            }elseif($dataList[$i]['satker_id'] != $satker){
               // inisaialisasi satuan kerja
               $satker                                   = $dataList[$i]['satker_id'];
               $dataLists[$index]['unit_id']             = $dataList[$i]['satker_id'];
               $dataLists[$index]['unit_kode']           = $dataList[$i]['satker_kode'];
               $dataLists[$index]['unit_nama']           = $dataList[$i]['satker_nama'];
               $dataLists[$index]['class_name']          = 'table-common-even1';
               $dataLists[$index]['row_style']           = 'font-weight: bold;';
               $dataLists[$index]['link_style']          = 'display: none;';
               $dataLists[$index]['unit_type']           = 'satker';
               $dataLists[$index]['data_type']           = 'parent';
               //$i++;
            }elseif($dataList[$i]['satker_id'] == $satker && $dataList[$i]['unit_id'] != $unit && $dataList[$i]['unit_id'] != null){
               // inisisalisai unit kerja
               $unit                                     = $dataList[$i]['unit_id'];
               $dataLists[$index]['unit_id']             = $dataList[$i]['unit_id'];
               $dataLists[$index]['unit_kode']           = $dataList[$i]['unit_kode'];
               $dataLists[$index]['unit_nama']           = $dataList[$i]['unit_nama'];
               $dataLists[$index]['class_name']          = 'table-common-even2';
               $dataLists[$index]['row_style']           = 'font-weight: bold;';
               $dataLists[$index]['link_style']          = 'display: none;';
               $dataLists[$index]['unit_type']           = 'unit';
               $dataLists[$index]['data_type']           = 'parent';
               //$i++;
            }elseif($dataList[$i]['satker_id'] == $satker && $dataList[$i]['unit_id'] == $unit && $dataList[$i]['fakultas_id'] != $fakultas && $dataList[$i]['fakultas_id'] != null){
               // inisialisai fakultas
               $fakultas                                 = $dataList[$i]['fakultas_id'];
               $dataLists[$index]['unit_id']             = $dataList[$i]['fakultas_id'];
               $dataLists[$index]['unit_kode']           = $dataList[$i]['fakultas_kode'];
               $dataLists[$index]['unit_nama']           = $dataList[$i]['fakultas_nama'];
               $dataLists[$index]['class_name']          = 'table-common-even1';
               $dataLists[$index]['row_style']           = 'font-weight: bold; font-style: italic;';
               $dataLists[$index]['link_style']          = 'display: none;';
               $dataLists[$index]['unit_type']           = 'fakultas';
               $dataLists[$index]['data_type']           = 'parent';
            }elseif($dataList[$i]['satker_id'] == $satker && $dataList[$i]['unit_id'] == $unit && $dataList[$i]['fakultas_id'] == $fakultas && $dataList[$i]['jurusan_id'] != $jurusan){
               // inisialisasi jurusan
               $jurusan                                  = $dataList[$i]['jurusan_id'];
               $dataLists[$index]['unit_id']             = $dataList[$i]['jurusan_id'];
               $dataLists[$index]['unit_kode']           = $dataList[$i]['jurusan_kode'];
               $dataLists[$index]['unit_nama']           = $dataList[$i]['jurusan_nama'];
               $dataLists[$index]['class_name']          = 'table-common-even2';
               $dataLists[$index]['row_style']           = 'font-weight: bold; font-style: italic;';
               $dataLists[$index]['link_style']          = 'display: none;';
               $dataLists[$index]['unit_type']           = 'jurusan';
               $dataLists[$index]['data_type']           = 'parent';
            }elseif($dataList[$i]['satker_id'] == $satker && $dataList[$i]['unit_id'] == $unit && $dataList[$i]['fakultas_id'] == $fakultas && $dataList[$i]['jurusan_id'] == $jurusan && $dataList[$i]['prodi_id'] != $prodi){
               // inisialisasi program studi
               $prodi                                    = $dataList[$i]['prodi_id'];
               $dataLists[$index]['unit_id']             = $dataList[$i]['prodi_id'];
               $dataLists[$index]['unit_kode']           = $dataList[$i]['prodi_kode'];
               $dataLists[$index]['unit_nama']           = $dataList[$i]['prodi_nama'];
               $dataLists[$index]['class_name']          = 'table-common-even1';
               $dataLists[$index]['row_style']           = 'font-style: italic;';
               $dataLists[$index]['link_style']          = 'display: none;';
               $dataLists[$index]['unit_type']           = 'prodi';
               $dataLists[$index]['data_type']           = 'parent';
            }
            $index++;
         }
         // end inisisalisasi data

         foreach ($dataLists as $list) {
            $list['id']             = Dispatcher::Instance()->Encrypt($list['id']);
            $list['bintang']        = $list['status'] == 'F' ? 'lamp-red' : 'lamp-green';
            if($list['data_type'] != 'parent'){
               $list['nominal']           = number_format($list['nominal'], 0, ',','.');
               $list['nilai_pagu']        = number_format($list['nominal_tersedia'], 0, ',','.');
               $list['nominal_tersedia']  = number_format($list['nominal_tersedia'], 0, ',','.');
               $list['nominal_pok']       = number_format($list['nominal_pok'], 0, ',','.');
               $list['nominal_realisasi'] = number_format($list['nominal_realisasi'], 0, ',','.');

               $list['url_edit']       = $url_add.'&dataId='.$list['id'].'&page='.(string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();


               // $this->mrTemplate->AddVar('pagu_parent', 'DATA_PARENT', 'NO');
               $this->mrTemplate->AddVar('checkbox', 'NOMOR', $list['nomor']);
               $this->mrTemplate->AddVar('checkbox', 'ID', $list['id']);
               $this->mrTemplate->AddVar('checkbox', 'MAK_KODE', $list['mak_kode']);
               $this->mrTemplate->AddVar('checkbox', 'MAK_NAMA', $list['mak_nama']);
               $this->mrTemplate->AddVar('checkbox', 'PAGU_KODE', $list['pagu_kode']);
               $this->mrTemplate->AddVar('checkbox', 'DELETABLE', $list['deletable']);

               if(strtoupper($list['status']) === 'T'){
                  $this->mrTemplate->AddVar('bintang', 'LEVEL', 'TRUE');
               }else{
                  $this->mrTemplate->AddVar('bintang', 'LEVEL', 'FALSE');
               }

               if((bool)$list['pok'] == false OR (bool)$list['has_spj'] == true){
                  $list['deletable']   = 'disabled = true;';
                  $list['editable']    = 'display: none;';
                  $this->mrTemplate->AddVar('links', 'LEVEL', 'DISABLE');
                  $this->mrTemplate->AddVar('checkbox', 'DISABLED', 'TRUE');
               }else{
                  $this->mrTemplate->AddVar('links', 'LEVEL', 'ENABLE');
                  $this->mrTemplate->AddVar('checkbox', 'DISABLED', 'FALSE');
                  $this->mrTemplate->AddVar('links', 'URL_EDIT', $list['url_edit']);
                  $list['deletable']   = '';
                  $list['editable']    = '';
               }
            }

            $this->mrTemplate->AddVar('status', 'LEVEL', strtoupper($list['data_type']));
            $this->mrTemplate->AddVar('aksi', 'LEVEL', strtoupper($list['data_type']));
            $this->mrTemplate->AddVar('pagu', 'LEVEL', strtoupper($list['data_type']));

            $this->mrTemplate->AddVars('data_grid_item', $list, '');
            $this->mrTemplate->parseTemplate('data_grid_item', 'a');
         }

         $this->mrTemplate->AddVar('data_list', 'TOTAL_PAGU', number_format($totalPagu, 0, ',','.'));
         $this->mrTemplate->AddVar('data_list', 'TOTAL_POK', number_format($totalPok, 0, ',','.'));
         $this->mrTemplate->AddVar('data_list', 'TOTAL_PERSEDIAAN', number_format($totalBudget, 0, ',','.'));
      }
   }
}
?>