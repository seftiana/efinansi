<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/realisasi_pencairan_2/business/AppPopupSubKegiatan.class.php';

class PopupSubKegiatan extends HtmlResponse
{
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/realisasi_pencairan_2/template');
      $this->SetTemplateFile('popup_subkegiatan.html');
   }

   function TemplateBase()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest() {
      $mObj          = new AppPopupKegiatanRef();
      $requestData   = array();

      if(isset($mObj->_POST['btncari'])) {
         $requestData['kode']          = $mObj->_POST['kode'];
         $requestData['unit_id']       = $mObj->_POST['unit_id'];
         $requestData['ta_id']         = $mObj->_POST['ta_id'];
         $requestData['data_id']       = $mObj->_POST['data_id'];
      } elseif(isset($mObj->_GET['cari'])) {
         $requestData['kode']          = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['unit_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['ta_id']         = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['data_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      } else {
         $requestData['kode']          = "";
         $requestData['unit_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['ta_id']         = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['data_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         $queryString         = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         $query               = array();
         foreach ($requestData as $key => $value) {
            $query[$key]      = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString         = urldecode(http_build_query($query));
      }
     
      $offset         = 0;
      $limit          = 20;
      $page           = 0;
      if(isset($_GET['page'])){
         $page    = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset  = ($page - 1) * $limit;
      }
      #paging url
      $url    = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&cari='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      
      $destination_id   = "popup-subcontent";
      $dataList         = $mObj->GetData($offset, $limit, (array)$requestData);
      $total_data       = $mObj->Count();
      $dataKomponen     = $mObj->GetKomponenAnggaran($offset, $limit, (array)$requestData);

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


      $return['query_string'] = $queryString;
      $return['request_data'] = $requestData;
      $return['data_list']    = $mObj->ChangeKeyName($dataList, 'lower');
      $return['start']        = $offset+1;
      $return['nama_bulan']    = $mObj->indonesianMonth;
      $return['komponen']['data']   = json_encode($dataKomponen['data_grid']);
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'subKegiatan',
         'popup',
         'html'
      ) . '&ta_id='.$_GET['ta_id'].'&unit_id=' . $_GET['unit_id'].'&grp=' . $_GET['unit_id'];
      $requestData   = $data['request_data'];
      $queryString   = $data['query_string'];
      $dataList      = $data['data_list'];
      $start         = $data['start'];
      $dataKomponen  = $data['komponen'];
      $namaBulan     = $data['nama_bulan'];

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);//. $queryString );
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVars('content', $dataKomponen, 'KOMP_');

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_kegiatanref', 'KEGIATANREF_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_kegiatanref', 'KEGIATANREF_EMPTY', 'NO');
         $index         = 0;
         $dataGrid      = array();
         $program       = '';
         $kegiatan      = '';
         $dataAnggaran  = array();
         for ($i=0; $i < count($dataList);) {
            if((int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan === (int)$dataList[$i]['subprog_id']){
               $programKodeSistem   = $program;
               $kegiatanKodeSistem  = $program.'.'.$kegiatan;
               $dataAnggaran[$programKodeSistem]['nominal_anggaran']    += $dataList[$i]['nominal_anggaran'];
               $dataAnggaran[$programKodeSistem]['nominal_realisasi']   += $dataList[$i]['nominal_realisasi'];
               $dataAnggaran[$programKodeSistem]['sisa_dana']   += $dataList[$i]['sisa_dana'];
               $dataAnggaran[$programKodeSistem]['nominal_pencairan']   += $dataList[$i]['nominal_pencairan'];
               $dataAnggaran[$kegiatanKodeSistem]['nominal_anggaran']   += $dataList[$i]['nominal_anggaran'];
               $dataAnggaran[$kegiatanKodeSistem]['nominal_realisasi']  += $dataList[$i]['nominal_realisasi'];
               $dataAnggaran[$kegiatanKodeSistem]['sisa_dana']  += $dataList[$i]['sisa_dana'];
               $dataAnggaran[$kegiatanKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan'];

               $dataGrid[$index]['nomor'] = $start;
               $dataGrid[$index]['id']    = $dataList[$i]['id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['nama'];
               $dataGrid[$index]['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
               $dataGrid[$index]['row_style']   = '';
               $dataGrid[$index]['level']       = 'sub_kegiatan';
               $dataGrid[$index]['nominal_anggaran']  = $dataList[$i]['nominal_anggaran'];
               $dataGrid[$index]['nominal_realisasi'] = $dataList[$i]['nominal_realisasi'];
               $dataGrid[$index]['nominal_pencairan'] = $dataList[$i]['nominal_pencairan'];
               $dataGrid[$index]['sisa_dana']         = $dataList[$i]['sisa_dana'];
               $dataGrid[$index]['program_id']        = $program;
               $dataGrid[$index]['program_kode']   = $dataList[$i]['program_nomor'];
               $dataGrid[$index]['program_nama']   = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kegiatan_id']    = $kegiatan;
               $dataGrid[$index]['kegiatan_kode']  = $dataList[$i]['subprog_nomor'];
               $dataGrid[$index]['kegiatan_nama']  = $dataList[$i]['subprog_nama'];
               $dataGrid[$index]['kegdet_id']      = $dataList[$i]['kegdet_id'];
               $dataGrid[$index]['unit_id']        = $dataList[$i]['keg_unitkerja_id'];
               $dataGrid[$index]['status']        = $dataList[$i]['status_approve'];
               $dataGrid[$index]['bulan']          = $namaBulan[$dataList[$i]['bulan']];
               $i++;
               $start++;
            }elseif((int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan !== (int)$dataList[$i]['subprog_id']){
               $kegiatan      = (int)$dataList[$i]['subprog_id'];
               $kodeSistem    = $program.'.'.$kegiatan;
               $dataAnggaran[$kodeSistem]['nominal_anggaran']  = 0;
               $dataAnggaran[$kodeSistem]['nominal_realisasi'] = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['subprog_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['subprog_nomor'];
               $dataGrid[$index]['nama']  = $dataList[$i]['subprog_nama'];
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = '';
               $dataGrid[$index]['level']       = 'kegiatan';
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['bulan']       = '';
               $dataGrid[$index]['status']      = '';
            }else{
               $program       = (int)$dataList[$i]['program_id'];
               $kodeSistem    = $program;
               $dataAnggaran[$kodeSistem]['nominal_anggaran']  = 0;
               $dataAnggaran[$kodeSistem]['nominal_realisasi'] = 0;
               $dataGrid[$index]['id']          = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']        = $dataList[$i]['program_nomor'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $dataGrid[$index]['level']       = 'program';
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['bulan']       = '';
               $dataGrid[$index]['status']      = '';
            }
            $index++;
         } 
         foreach ($dataGrid as $list) {
            $this->mrTemplate->clearTemplate('select_button');
            switch (strtoupper($list['level'])) {
               case 'PROGRAM':
                  $list['nominal_anggaran_label']  = number_format($dataAnggaran[$list['kode_sistem']]['nominal_anggaran'], 0, ',','.');
                  $list['nominal_pencairan_label'] = number_format($dataAnggaran[$list['kode_sistem']]['nominal_pencairan'], 0, ',','.');
                  break;
               case 'KEGIATAN':
                  $list['nominal_anggaran_label']  = number_format($dataAnggaran[$list['kode_sistem']]['nominal_anggaran'], 0, ',','.');
                  $list['nominal_pencairan_label'] = number_format($dataAnggaran[$list['kode_sistem']]['nominal_pencairan'], 0, ',','.');
                  break;
               case 'SUB_KEGIATAN':
                  if($list['nominal_anggaran'] > 0 && $list['nominal_anggaran'] === $list['nominal_pencairan'] &&  $list['status'] == 'Ya'){
                      $this->mrTemplate->AddVar('select_button', 'VISIBLE', 'NO');
                  } else {
                       $this->mrTemplate->AddVar('select_button', 'VISIBLE', 'YES');
                  }
                  $list['nominal_anggaran_label']  = number_format($list['nominal_anggaran'], 0, ',','.');
                  $list['nominal_realisasi_label'] = number_format($list['nominal_realisasi'], 0, ',','.');
                  $list['nominal_pencairan_label'] = number_format($list['nominal_pencairan'], 0, ',','.');
                 
                  $this->mrTemplate->AddVars('select_button', $list);
                  break;
               default:
                   
                  $list['nominal_anggaran_label']  = number_format($list['nominal_anggaran'], 0, ',','.');
                  $list['nominal_pencairan_label'] = number_format($list['nominal_pencairan'], 0, ',','.');
                  $this->mrTemplate->AddVar('select_button', 'VISIBLE', 'YES');
                  $this->mrTemplate->AddVars('select_button', $list);
                  break;
            }

            $this->mrTemplate->AddVars('data_kegiatanref_item', $list);
            $this->mrTemplate->parseTemplate('data_kegiatanref_item', 'a');
         }
      }
   }
}
?>