<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/transaksi_penyusutan/business/AppTransaksiPenyusutanAsper.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewListPenyusutan extends HtmlResponse
{
   var $Pesan;
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/transaksi_penyusutan/template');
      $this->SetTemplateFile('view_list_penyusutan.html');
   }
   function ProcessRequest()
   {
      $Obj = new AppTransaksiPenyusutanAsper();

      $arrKib = $Obj->GetComboJenisKib();

      Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'jenis_kib',array('jenis_kib', $arrKib, $jenis_kib, '', ''),Messenger::CurrentRequest);

      $key = $_POST['key'];
      $jenis_kib = $_POST['jenis_kib'];

      if($_GET['key']!='')
         $key = Dispatcher::Instance()->Decrypt($_GET['key']);

      if($_GET['jenis_kib']!='')
         $jenis_kib = Dispatcher::Instance()->Decrypt($_GET['jenis_kib']);

      #set default pagging
      $limit = 20;
      $page = 0;
      $offset = 0;

      if(isset($_GET['page'])){
         $page = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset = ($page - 1) * $limit;
      }

      #fetch data
      $return['data'] = $Obj->GetListPenyusutan($offset, $limit, $key, $jenis_kib);

      #fethc numrows
      $numrows = $Obj->GetCount();// fetch here;

      #pagging url
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType)
         .'&key='.Dispatcher::Instance()->Encrypt($key).'&jenis_kib='.Dispatcher::Instance()->Encrypt($jenis_kib)
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
      $return['jenis_kib'] = $jenis_kib;
      $return['key'] = $key;

      return $return;
   }
   function ParseTemplate($data = NULL)
   {
      $urlPencarian = Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'listPenyusutan', 'view', 'html');
      $urlBack = Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'transaksiPenyusutan', 'view', 'html');
      $urlExcel = Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'ExcelListPenyusutan', 'view', 'xls').'&key='.Dispatcher::Instance()->Encrypt($data['key']).'&jenis_kib='.Dispatcher::Instance()->Encrypt($data['jenis_kib']);
      $urlCetak = Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'CetakListPenyusutan', 'view', 'html').'&key='.Dispatcher::Instance()->Encrypt($data['key']).'&jenis_kib='.Dispatcher::Instance()->Encrypt($data['jenis_kib']);
      $urlLog = Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'logPenyusutan', 'view', 'html').'&mstPenystnBarangId='.Dispatcher::Instance()->Encrypt($data['mstPenystnBarangId']);


      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlPencarian);
      $this->mrTemplate->AddVar('content', 'URL_BACK', $urlBack);
      $this->mrTemplate->AddVar('content', 'URL_EXPORT', $urlExcel);
      $this->mrTemplate->AddVar('content', 'URL_CETAK', $urlCetak);



      if (empty($data['data'])) {
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

            $dataList[$i]['nilai_buku'] = number_format($dataList[$i]['nilai_buku'],2,',','.');
            $dataList[$i]['mstPenystnNilaiPerolehan'] = number_format($dataList[$i]['mstPenystnNilaiPerolehan'],2,',','.');
            $dataList[$i]['nilai_penyusutan'] = number_format($dataList[$i]['nilai_penyusutan'],2,',','.');
            $dataList[$i]['total_penyusutan'] = number_format($dataList[$i]['total_penyusutan'],2,',','.');
            $dataList[$i]['url_log'] = $urlLog.'&mstPenystnBarangId='.Dispatcher::Instance()->Encrypt($dataList[$i]['mstPenystnBarangId']);

            $this->mrTemplate->AddVars('data_item', $dataList[$i], '');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }
   }
}
?>