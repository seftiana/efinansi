<?php
/**
* @package ViewPopupSubKegiatan
* @copyright Copyright (c) PT Gamatechno Indonesia
* @Analyzed By Dyan Galih <galih@gamatechno.com>
* @author Dyan Galih <galih@gamatechno.com>
* @version 0.1
* @startDate 2011-01-01
* @lastUpdate 2011-01-01
* @description View Popup SubKegiatan
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/pagu_anggaran_unit_per_mak/business/RkaklSubKegiatan.class.php';

class ViewPopupSubKegiatan extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').'module/pagu_anggaran_unit_per_mak/template');
      $this->SetTemplateFile('view_popup_sub_kegiatan.html');
   }

   function ProcessRequest() {
      $objSubKegiatan   = new RkaklSubKegiatan();
      $_POST            = $_POST->AsArray();
      $GET              = $_GET->AsArray();

      if(isset($_POST['action'])){
         $post['kode']  = trim($_POST['nama']);
      }elseif(isset($GET['search'])){
         $post['kode']  = trim(Dispatcher::Instance()->Decrypt($GET['kode']));
      }else{
         $post['kode']  = '';
      }

      foreach ($post as $key => $value) {
         $query[$key]   = Dispatcher::Instance()->Encrypt($value);
      }
      $uri              = urldecode(http_build_query($query));

      #set default pagging
      $limit   = 20;
      $page    = 0;
      $offset  = 0;

      if(isset($_GET['page'])){
         $page = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset = ($page - 1) * $limit;
      }

      #fetch data
      $return['data'] = $objSubKegiatan->GetRkaklSubKegiatan(
         $post['kode'], 
         $post['kode'], 
         $offset, 
         $limit
      );

      #fethc numrows
      $numrows          = $objSubKegiatan->GetCount();// fetch here;

      #pagging url
      $url              = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType)
      .'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$uri;

      $destination_id   = "popup-subcontent"; # options: {popup-subcontent,subcontent-element}

      #send data to pagging component
      Messenger::Instance()->SendToComponent(
         'paging', 
         'Paging', 
         'view', 
         'html', 
         'paging_top',
         array(
            $limit,
            $numrows, 
            $url, 
            $page, 
            $destination_id
         ), Messenger::CurrentRequest);

      #send data to parse method
      $return['post']      = $post;
      $return['start']     = $offset+1;
      $return['page']      = $page;
      $return['numrows']   = $numrows;

      return $return;
   }

   function ParseTemplate($data = NULL) {
      $post             = $data['post'];
      $urlSubKegiatan   = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak', 
         'popupSubKegiatan', 
         'view', 
         'html'
      );
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSubKegiatan);
      $this->mrTemplate->AddVar('content', 'NAMA', $post['kode']);

      if (empty($data['data'])) {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

         $dataList = $data['data'];

         for ($i=0; $i<sizeof($dataList); $i++) {
            $no = $i+$data['start'];
            $dataList[$i]['nomor'] = $no;

            if ($no % 2 != 0):
               $dataList[$i]['class_name'] 	= 'table-common-even';
            else:
               $dataList[$i]['class_name'] 	= '';
            endif;
            
			   $dataList[$i]['link']			= str_replace("'","\'",$dataList[$i]['nama']);
            $this->mrTemplate->AddVars('data_item', $dataList[$i], '');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }
   }
}
?>