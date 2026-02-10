<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/pembalik_approval/business/AppDetilPembalikApproval.class.php';

class ViewDetilPembalikApproval extends HtmlResponse
{
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/pembalik_approval/template');
      $this->SetTemplateFile('view_detil_pembalik_approval.html');
   }

   function ProcessRequest() {
      $Obj              = new AppDetilPembalikApproval();
      $msg              = Messenger::Instance()->Receive(__FILE__);
      $message          = $messengerData = $style  = NULL;
      $queryString      = $Obj->_getQueryString();
      $queryReturn      = preg_replace('/(dataId=[a-zA-Z0-9\s\w]+)/', '', $queryString);
      $queryReturn      = preg_replace('/^[\&]+/', '', $queryReturn);
      $queryReturn      = preg_replace('/[\&]+$/', '', $queryReturn);
      $queryReturn      = preg_replace('/\&[\&]+/', '', $queryReturn);
      $queryReturn      = '&search=1&'.$queryReturn;
      $dataId           = Dispatcher::Instance()->Decrypt($Obj->_GET['dataId']);
      $info             = $Obj->GetInformasi($dataId);
      $info['unitkerja_label']      = $info['unit_kerja_nama'];

      //view
      $itemViewed    = 40000;
      $currPage      = 1;
      $startRec      = 0 ;
      if(isset($_GET['page'])) {
         $currPage   = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec   = ($currPage-1) * $itemViewed;
      }
      $data          = $Obj->GetData($startRec, $itemViewed, $dataId);
      $totalData     = $Obj->Count();

      $url           = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      ).'&search='.$queryString;
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
            $currPage
         ), Messenger::CurrentRequest);

      if($msg){
         $message    = $msg[0][1];
         $style      = $msg[0][2];
      }

      $return['info']         = $info;
      $return['data']         = $data;
      $return['start']        = $startRec+1;
      $return['query_string'] = $queryString;
      $return['query_return'] = $queryReturn;
      $return['message']      = $message;
      $return['style']        = $style;
      $return['count']        = $totalData;
      return $return;
   }


   function ParseTemplate($data = NULL) {
      $queryString      = $data['query_string'];
      $queryReturn      = $data['query_return'];
      $message          = $data['message'];
      $style            = $data['style'];
      $totalData        = (int)$data['count'];
      $dataList         = $data['data'];
      $urlAction        = Dispatcher::Instance()->GetUrl(
         'pembalik_approval',
         'updateDetilPembalikApproval',
         'do',
         'html'
      ) . '&'.$queryString;
      $urlReturn        = Dispatcher::Instance()->GetUrl(
         'pembalik_approval',
         'pembalikApproval',
         'view',
         'html'
      ) . $queryReturn;

      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'URL_KEMBALI', $urlReturn);

      $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_LABEL', $data['info']['tahun_anggaran_label']);
      $this->mrTemplate->AddVar('content', 'UNITKERJA_LABEL', $data['info']['unitkerja_label']);
      $this->mrTemplate->AddVar('content', 'PROGRAM_LABEL', $data['info']['program_label']);
      $this->mrTemplate->AddVar('content', 'KEGIATAN_LABEL', $data['info']['kegiatan_label']);
      $this->mrTemplate->AddVar('content', 'SUBKEGIATAN_LABEL', $data['info']['subkegiatan_label']);

      if($message) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $start      = $data['start'];
         $approved   = 0;
         foreach ($dataList as $list) {
            $this->mrTemplate->SetAttribute('cekbok', 'visibility', 'visible');
            if(strtoupper($list['approval_realisasi']) === 'YA'){
               $this->mrTemplate->AddVar('cekbok', 'DISABLED', 'disabled');
               $list['class_approval']       = 'error';
               $approved+=1;
            }elseif(strtoupper($list['is_revisi']) == 'Y'){
               $this->mrTemplate->AddVar('cekbok', 'DISABLED', 'disabled');
               $list['class_approval']       = 'isrevisi';
                     $list['class_name'] = 'table-common-even';
               $approved+=1;
            }else{
               switch (strtoupper($list['approval'])) {
                  case 'YA':
                     $this->mrTemplate->AddVar('cekbok', 'DISABLED', '');
                     $list['class_name'] = 'table-common-even';
                     break;
                  case 'BELUM':
                     $approved+=1;
                     $this->mrTemplate->AddVar('cekbok', 'DISABLED', 'disabled');
                     $list['class_name'] = 'table-common-even1';
                     break;
                  case 'TIDAK':
                     $approved+=1;
                     $this->mrTemplate->AddVar('cekbok', 'DISABLED', 'disabled');
                     $list['class_name'] = '';
                     break;
                  default:
                     $approved+=1;
                     $this->mrTemplate->AddVar('cekbok', 'DISABLED', 'disabled');
                     $list['class_name'] = 'table-common-even1';
                     break;
               }
            }
            $list['number']      = $start;
            $list['format_nominal_usulan']   = number_format($list['nominal_usulan'], 2, ',', '.');
            $list['format_jumlah_usulan']    = number_format($list['jumlah_usulan'], 2, ',', '.');
            $list['format_jumlah_setuju']    = number_format($list['jumlah_setuju'], 2, ',', '.');
            $list['format_nominal_setuju']   = number_format($list['nominal_setuju'], 2, ',', '.');
            $list['format_satuan_setuju']    = $list['satuan_setuju'];
            $this->mrTemplate->AddVar('cekbok', 'DATA_ID', $list['id']);
            $this->mrTemplate->AddVar('cekbok', 'DATA_NUMBER', $start);
            $this->mrTemplate->AddVar('cekbok', 'DATA_NAMA', $list['nama']);
            $this->mrTemplate->AddVar('cekbok', 'DATA_KEGDET', $list['kegdet_id']);
            $this->mrTemplate->AddVars('data_items', $list, 'DATA_');
            $this->mrTemplate->parseTemplate('data_items', 'a');
            $start+=1;
         }

         if(strcmp((int)$totalData, (int)$approved) <> 0){
            $this->mrTemplate->SetAttribute('button_action', 'visibility', 'visible');
         }
      }
   }
}
?>