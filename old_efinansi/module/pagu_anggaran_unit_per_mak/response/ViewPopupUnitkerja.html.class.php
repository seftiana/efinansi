<?php
#doc
#    classname:    ViewPopupUnitKerja
#    scope:
# extends extends HtmlResponse
# construct: argument
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/pagu_anggaran_unit_per_mak/business/AppPopupUnitKerja.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewPopupUnitKerja extends HtmlResponse
{
   #    internal variables
   public $obj;
   public $userUnitKerja;
   protected $userId;
   protected $post;
   public $data;
   public $Pesan;
   public $css;

   #    Constructor
   function __construct ()
   {
      $this->obj      = new AppPopupUnitKerja();
      $this->userUnitKerja = new UserUnitKerja();
      $this->post     = $_POST->AsArray();
      $this->userId   = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
   }

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/pagu_anggaran_unit_per_mak/template');
      $this->SetTemplateFile('view_popup_unitkerja.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
      'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      $role             = $this->userUnitKerja->GetRoleUser($this->userId);
      $unitkerjaUserId  = $this->userUnitKerja->GetUnitKerjaUser($this->userId);
      $POST             = $_POST->AsArray();
      $GET              = $_GET->AsArray();

      if(isset($POST['btnCari'])){
         $post['kode']     = trim($POST['kode']);
         $post['nama']     = trim($POST['nama']);
         $post['tipe']     = $POST['tipeunit'];
      }elseif(isset($GET['search'])){
         $post['kode']     = Dispatcher::Instance()->Decrypt($GET['kode']);
         $post['nama']     = Dispatcher::Instance()->Decrypt($GET['nama']);
         $post['tipe']     = Dispatcher::Instance()->Decrypt($GET['tipe']);
      }else{
         $post['tipe']     = '';
         $post['kode']     = '';
         $post['nama']     = '';
      }
      foreach ($post as $key => $value) {
         $query[$key]      = Dispatcher::Instance()->Encrypt($value);
      }
      $uri     = urldecode(http_build_query($query));

      if($_POST || isset($_GET['cari'])) {
         if(isset($this->post['kode'])) {
            $kode       = $this->post['kode'];
         } elseif(isset($_GET['kode'])) {
            $kode       = Dispatcher::Instance()->Decrypt($_GET['kode']);
         } else {
            $kode       = '';
         }
         if(isset($this->post['nama'])) {
            $unitkerja  = $this->post['nama'];
         } elseif(isset($_GET['nama'])) {
            $unitkerja  = Dispatcher::Instance()->Decrypt($_GET['nama']);
         } else {
            $unitkerja  = '';
         }

         if($this->post['tipeunit'] != "all") {
            $tipeunit   = $this->post['tipeunit'];
         } elseif(isset($_GET['tipeunit'])) {
            $tipeunit   = Dispatcher::Instance()->Decrypt($_GET['tipeunit']);
         } else {
            $tipeunit   = '';
         }
       if(isset($this->post['btnCari']))
       {
         $satker    = $this->post['satker'];
       }
       elseif(isset($_GET['cari']))
       {
         $satker    = $_GET['satker'];
       }
       else
       {
         $satker    = $_GET['satker'];
       }
      }

      // $totalData        = $this->obj->GetCountDataUnitkerja(
      //    $kode,
      //    $unitkerja,
      //    $tipeunit,
      //    $role,
      //    $unitkerjaUserId
      // );
      $itemViewed       = 20;
      $currPage         = 1;
      $startRec         = 0 ;
      if(isset($_GET['page'])) {
         $currPage      = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec      =($currPage-1) * $itemViewed;
      }

      $dest             = "popup-subcontent";
      $dataList         = $this->obj->GetDataUnitkerja($startRec, $itemViewed, $post);
      $totalData        = $this->obj->Count();
      $url              = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType .
         '&kode=' . Dispatcher::Instance()->Encrypt($kode) .
         '&unitkerja=' . Dispatcher::Instance()->Encrypt($unitkerja) .
         '&tipeunit=' . Dispatcher::Instance()->Encrypt($tipeunit) .
         '&satker='.$satker.
         '&cari=' . Dispatcher::Instance()->Encrypt(1));

      Messenger::Instance()->SendToComponent(
         'paging',
         'Paging',
         'view',
         'html',
         'paging_top',
         array(
            $itemViewed,
            $totalData,
            $url,
            $currPage,
            $dest
         ), Messenger::CurrentRequest);

      $arr_tipeunit     = $this->obj->GetDataTipeunit();

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tipeunit',
         array(
            'tipeunit',
            $arr_tipeunit,
            $post['tipe'],
            'true',
            ' style="width:200px;" '
         ), Messenger::CurrentRequest);

      // $this->data        = $this->obj->getDataUnitkerja(
      //    $startRec,
      //    $itemViewed,
      //    $kode,
      //    $unitkerja,
      //    $tipeunit,
      //    $role,
      //    $unitkerjaUserId
      // );

      $msg              = Messenger::Instance()->Receive(__FILE__);

      $this->Pesan      = $msg[0][1];
      $this->css        = $msg[0][2];
      $return['start']                    = $startRec+1;
      $return['search']['kode']           = $kode;
      $return['search']['unitkerja']      = $unitkerja;
      $return['search']['tipeunit']       = $tipeunit;
      $return['satker']                   = $satker;
      $return['dataList']                 = $this->obj->ChangeKeyName($dataList);
      return $return;
   }

   function ParseTemplate($data = NULL)
   {
      $dataList         = $data['dataList'];
      $this->mrTemplate->AddVar('content', 'SATKER', $data['satker']);
      $url_search = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      );

      $search     = $data['search'];
      $this->mrTemplate->AddVar('content','URL_SEARCH',$url_search);

      $this->mrTemplate->AddVar('content', 'SEARCH_KODE', $search['kode']);
      $this->mrTemplate->AddVar('content', 'SEARCH_NAMA', $search['unitkerja']);

      $dataUnitkerja = $data['dataList'];
      if(empty($dataUnitkerja))
      {
         $this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'YES');
      }
      else
      {
         $this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'NO');
         $nomor      = 0;
         foreach ($dataUnitkerja as $list) {
            $list['number']      = $nomor+$data['start'];
            $list['link']        = str_replace("'", "\'", $list['nama']);
            if($list['parent_id'] == 0){
               $list['class_name']     = 'table-common-even1';
               $list['style']          = 'font-weight: bold;';
            }elseif($nomor %2 <> 0){
               $list['class_name']     = 'table-common-even';
            }else{
               $list['class_name']     = '';
            }
            $this->mrTemplate->AddVars('data_unitkerja_item', $list, 'UNITKERJA_');
            $this->mrTemplate->parseTemplate('data_unitkerja_item', 'a');
            $nomor++;
         }
         // for ($i=0;$i<sizeof($dataUnitkerja);$i++) {
         //    $dataUnitkerja[$i]['enc_unitkerja_id']       = Dispatcher::Instance()->
         //                                       Encrypt($dataUnitkerja[$i]
         //                                       ['unitkerja_id']);
         //    $dataUnitkerja[$i]['enc_unitkerja_nama']  = Dispatcher::Instance()->
         //                                       Encrypt($dataUnitkerja[$i]
         //                                       ['unitkerja_nama']);
         // }
         // for ($i=0;$i<sizeof($dataUnitkerja);$i++) {
         //    $no                     = $i+$data['start'];
         //    $dataUnitkerja[$i]['number']  = $no;

         //    //if ($no % 2 != 0) $dataUnitkerja[$i]['class_name'] = 'table-common-even';
         //    //else $dataUnitkerja[$i]['class_name'] = '';
         //    if($dataUnitkerja[$i]['parentId'] == '0') {
         //       $dataUnitkerja[$i]['class_name']    = 'table-common-even';
         //    }
         //    $dataUnitkerja[$i]['link'] = str_replace("'","\'",$dataUnitkerja[$i]['unit']);
         //    $this->mrTemplate->AddVars('data_unitkerja_item',
         //                   $dataUnitkerja[$i], 'UNITKERJA_');
         //    $this->mrTemplate->parseTemplate('data_unitkerja_item', 'a');
         // }
      }
   }
}
?>