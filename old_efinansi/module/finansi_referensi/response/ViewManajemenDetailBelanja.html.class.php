<?php
/**
* ================= doc ====================
* FILENAME     : ViewManajemenDetailBelanja.html.class.php
* @package     : ViewManajemenDetailBelanja
* scope        : PUBLIC
* @Author      : Eko Susislo
* @Created     : 2014-01-03
* @Modified    : 2014-01-03
* @Analysts    : Dyah Fajar
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';

class ViewManajemenDetailBelanja extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/'.Dispatcher::Instance()->mModule.'/template/');
      $this->SetTemplateFile('view_manajemen_detail_belanja.html');
   }

   function ProcessRequest(){
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $mObj          = new FinansiReferensi();
      $queryString   = $mObj->__getQueryString();
      $komponenId    = Dispatcher::Instance()->Decrypt($mObj->_GET['komponen_id']);
      $dataKomponen  = $mObj->GetKegiatanRefById($komponenId);

      $offset        = 0;
      $limit         = 20;
      $page          = 0;
      if(isset($_GET['page'])){
         $page       = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset     = ($page - 1) * $limit;
      }
      #paging url
      $url           = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "subcontent-element";
      $dataList         = $mObj->GetDataDetailBelanja($offset, $limit, array('ref_id' => $komponenId));
      $total_data       = $mObj->Count();

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


      if($messenger){
         $messengerData          = $messenger[0][0];
         $messengerMsg           = $messenger[0][1];
         $messengerStyle         = $messenger[0][2];
      }

      $return['message']         = $messengerMsg;
      $return['style']           = $messengerStyle;
      $return['query_string']    = $queryString;
      $return['data_komponen']   = $dataKomponen['data'];
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = null){
      $queryString      = $data['query_string'];
      $dataKomponen     = $data['data_komponen'];
      $dataList         = $data['data_list'];
      $start            = $data['start'];
      $message          = $data['message'];
      $style            = $data['style'];

      $urlReturn        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ProgramKegiatan',
         'view',
         'html'
      ).'&search=1&'.$queryString;

      if(empty($dataKomponen)){
         $this->RedirectTo($urlReturn);
      }
      $urlAdd           = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'AddDetailBelanja',
         'view',
         'html'
      ).'&'.$queryString;

      $urlEdit          = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'EditDetailBelanja',
         'view',
         'html'
      ).'&'.$queryString;

      $parseUrl      = parse_url($queryString);
      $urlExploded   = explode('&', $parseUrl['path']);
      // var_dump($urlExploded);
      $urlIndex      = 0;
      foreach ($urlExploded as $url) {
         list($urlKey[$urlIndex], $urlValue[$urlIndex]) = explode('=', $url);
         $patern     = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/';
         $patern1    = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/';
         if((preg_match($patern, $urlValue[$urlIndex]) || preg_match($patern1, $urlValue[$urlIndex])) && strtotime($urlValue[$urlIndex]) !== false){
            $urlValue[$urlIndex]    = date('Y/m/d', strtotime($urlValue[$urlIndex]));
         }
         $urlIndex   += 1;
      }
      unset($urlIndex);
      $keyUrl     = implode('|', $urlKey);
      $valueUrl   = implode('|', $urlValue);
      $this->mrTemplate->AddVar('content', 'URL_REDIRECT', $urlReturn);
      $this->mrTemplate->AddVars('content', $dataKomponen);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      $urlAccept  = Dispatcher::Instance()->mModule.'|DeleteDetailBelanja|do|html-search|'.$keyUrl.'-1|'.$valueUrl;
      $urlReturn  = Dispatcher::Instance()->mModule.'|'.Dispatcher::Instance()->mSubModule.'|view|html-search|'.$keyUrl.'-1|'.$valueUrl;
      $label      = GTFWConfiguration::GetValue('language', 'komponen');
      $message    = 'Data yang sudah di hapus tidak bisa di kembalikan lagi.';

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $this->mrTemplate->clearTemplate('link_delete');
            $list['nomor']       = $start;
            $list['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
            $list['nominal']     = number_format($list['nominal'], 0, ',','.');

            $list['url_edit']    = $urlEdit.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']);
            // url delete
            $list['url_delete']     = Dispatcher::Instance()->GetUrl(
               'confirm',
               'confirmDelete',
               'do',
               'html'
            ).'&urlDelete='. $urlAccept
            .'&urlReturn='.$urlReturn
            .'&id='.$list['id'].'|'.$list['kegref_id']
            .'&label='.$label
            .'&dataName='.$list['kode'].' - '.$list['nama']
            .'&message='.$message;
            if($list['kegiatan'] <> 0){
               $this->mrTemplate->AddVar('link_delete', 'DELETABLE', 'NO');
            }else{
               $this->mrTemplate->AddVar('link_delete', 'DELETABLE', 'YES');
               $this->mrTemplate->AddVar('link_delete', 'URL_DELETE', $list['url_delete']);
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }
   }
}
?>