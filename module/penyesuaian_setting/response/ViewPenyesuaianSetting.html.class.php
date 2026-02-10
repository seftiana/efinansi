<?php
/**
* @package ViewPenyesuaianSetting
* @copyright Copyright (c) PT Gamatechno Indonesia
* @Analyzed By Dyan Galih <galih@gamatechno.com>
* @author Dyan Galih <galih@gamatechno.com>
* @version 0.1
* @startDate 2011-09-09
* @lastUpdate 2011-09-09
* @description View Penyesuaian Setting
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/penyesuaian_setting/business/PenyesuaianSetting.class.php';

class ViewPenyesuaianSetting extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').'module/penyesuaian_setting/template');
      $this->SetTemplateFile('view_penyesuaian_setting.html');
   }

   function ProcessRequest() {

      if(isset($_GET['kode']))
         $kode =  Dispatcher::Instance()->Decrypt($_GET['kode']);
      else
         $kode = $_POST['kode'];

      $msg = Messenger::Instance()->Receive(__FILE__);
      if(!empty($msg)){
         $return['msg']['css'] = $msg[0][2];
         $return['msg']['message'] = $msg[0][1];
      }

      $obj = new PenyesuaianSetting;

      #set default pagging
      $limit = 20;
      $page = 0;
      $offset = 0;

      if(isset($_GET['page'])){
         $page = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset = ($page - 1) * $limit;
      }

      #fetch data
      $return['data'] = $obj->GetListPenyesuaian($offset, $limit, $kode);

      #fethc numrows
      $numrows = $obj->GetCount();// fetch here;

      #pagging url
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType)
         .'&kode='.Dispatcher::Instance()->Encrypt($kode)
         ;

      $destination_id = "subcontent-element"; # options: {popup-subcontent,subcontent-element}

      #send data to pagging component
      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top',
      array($limit,$numrows, $url, $page, $destination_id),
      Messenger::CurrentRequest);

      #send data to parse method
      $return['start'] = $offset+1;
      $return['page'] = $page;
      $return['numrows'] = $numrows;
      $return['kode'] = $kode;

      return $return;
   }

   function ParseTemplate($data = NULL) {

      $urlSearch = Dispatcher::Instance()->GetUrl('penyesuaian_setting', 'penyesuaianSetting', 'view', 'html');
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);

      $this->mrTemplate->AddVar('content', 'KODE', $data['kode']);

      $urlTambah = Dispatcher::Instance()->GetUrl('penyesuaian_setting', 'inputPenyesuaian', 'view', 'html');

      if (empty($data['data'])) {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         } else {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

            $dataList = $data['data'];

         for ($i=0; $i<sizeof($dataList); $i++) {
            $no = $i+$data['start'];
            $dataList[$i]['no'] = $no;

            if ($no % 2 != 0)
               $dataList[$i]['class_name'] = 'table-common-even';
            else
               $dataList[$i]['class_name'] = '';

            $dataList[$i]['setPenyesuaianNilaiPenyesuaian'] = number_format($dataList[$i]['setPenyesuaianNilaiPenyesuaian'],0,',','.');
            $dataList[$i]['url_edit'] = $urlTambah.'&id='.Dispatcher::Instance()->Encrypt($dataList[$i]['setPenyesuaianId']);
            $urlAccept = 'penyesuaian_setting|deletePenyesuaian|do|json-cari-'.$cari;
            $urlReturn = 'penyesuaian_setting|penyesuaianSetting|view|html-'.$cari;
            $label = 'Menghapus Setting Jurnal Penyesuaian';
            $dataName = $dataList[$i]['setPenyesuaianNama'];
            $idEnc = Dispatcher::Instance()->Encrypt($dataList[$i]['setPenyesuaianId']);
            $dataList[$i]['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').
            '&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataName;

            $this->mrTemplate->AddVars('data_item', $dataList[$i], '');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }

      if(!empty($data['msg'])){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $data['msg']['css']);
      }

      $urlTambah = Dispatcher::Instance()->GetUrl('penyesuaian_setting', 'inputPenyesuaian', 'view', 'html');

      $this->mrTemplate->AddVar('content', 'URL_TAMBAH', $urlTambah);
   }
}
?>