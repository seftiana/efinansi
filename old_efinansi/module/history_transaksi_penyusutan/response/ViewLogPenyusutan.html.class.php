<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_penyusutan/business/AppTransaksiPenyusutanAsper.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewLogPenyusutan extends HtmlResponse {

   var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/transaksi_penyusutan/template');
      $this->SetTemplateFile('view_log_penyusutan.html');
   }

   function ProcessRequest() {
      $Obj = new AppTransaksiPenyusutanAsper();

      $mstPenystnBarangId = Dispatcher::Instance()->Decrypt($_GET['mstPenystnBarangId']);

      #set default pagging
      $limit = 20;
      $page = 0;
      $offset = 0;

      if(isset($_GET['page'])){
         $page = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset = ($page - 1) * $limit;
      }

      #fetch data
      $return['data'] = $Obj->GetLogPenyusutan($offset, $limit, $mstPenystnBarangId);
#      print_r($return['data']);
      #fethc numrows
      $numrows = $Obj->GetCount();// fetch here;

      if($return['data']['0']['penyusutanMstPeriode']=='')
         $numrows='';

      #pagging url
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType)
         .'&mstPenystnBarangId='.Dispatcher::Instance()->Encrypt($mstPenystnBarangId)
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

      return $return;
   }

   function ParseTemplate($data = NULL) {

      $data['data']['0']['mstPenystnNilaiTotalPenyusutan'] = number_format($data['data']['0']['mstPenystnNilaiTotalPenyusutan'],2,',','.');
      $data['data']['0']['mstPenystnDisusutkan'] = number_format($data['data']['0']['mstPenystnDisusutkan'],2,',','.');

      $this->mrTemplate->AddVar('content', 'URL_BACK', Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'ListPenyusutan', 'view', 'html'));
      $this->mrTemplate->AddVars('content', $data['data']['0'], '');

      if ($data['data']['0']['penyusutanMstPeriode']=='') {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         } else {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

            $dataList = $data['data'];

         for ($i=0; $i<sizeof($dataList); $i++) {
            $no = $i+$data['start'];
            $dataList[$i]['nomor'] = $no;

            if ($no % 2 != 0)
               $dataList[$i]['class_name'] = 'table-common-even';
            else
               $dataList[$i]['class_name'] = '';


            $dataList[$i]['penyusutanDetNilaiAkhir'] = number_format($dataList[$i]['penyusutanDetNilaiAkhir'],2,',','.');
            $dataList[$i]['penyusutanDetNilaiPenyusutan'] = number_format($dataList[$i]['penyusutanDetNilaiPenyusutan'],2,',','.');

            $this->mrTemplate->AddVars('data_item', $dataList[$i], '');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }

   }

}
