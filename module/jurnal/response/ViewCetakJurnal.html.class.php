<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/jurnal/business/Jurnal.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/terbilang.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/template/business/PrintTemplate.class.php';

class ViewCetakJurnal extends HtmlResponse
{
   function TemplateModule()
   {
      $template = new PrintTemplate();
      $templateFile = $template->GetTemplateName('cetak_jv');

      if (empty($templateFile))
      {
         $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/' . Dispatcher::Instance()->mModule . '/template');
         $this->SetTemplateFile('view_cetak_transaksi.html');
      }
      else
      {
         $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'template'));
         $this->SetTemplateFile($templateFile);
      }
   }
   function TemplateBase()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print-small.html');
      $this->SetTemplateFile('layout-common-print-custom-header.html');
   }
   function ProcessRequest()
   {
      $Obj = new Jurnal();
      $id = $_GET['id'];
      $sub_account = Dispatcher::Instance()->Decrypt($_GET['sub_account']);
      $prId = $_GET['prid'];

      if($sub_account == '01-00-00-00-00-00-00'){
         $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_yayasan'));
      }elseif($sub_account == '00-00-00-00-00-00-00'){
         $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_perbanas'));
      }else{
         $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_all'));
      }

      $return['transaksi'] = $Obj->GetTransaksiById($id);
      $return['data'] = $Obj->GetJournalById($prId);

      $periodeTgl = explode('-',$return['data']['0']['prTanggal']);
      $return['transaksi']['periode'] = $periodeTgl['1'].'-'.$periodeTgl['0'];

      $return['transaksi']['user'] = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserName();
      $return['transaksi']['tanggal_cetak'] = date('Y-m-d');
      $return['transaksi']['jam_cetak'] = date('H:i:s A');
      $return['header']= $header;

      return $return;
   }
   function ParseTemplate($data = NULL)
   {
      $this->mrTemplate->AddVar('content', 'HEADER_LAPORAN', $data['header']);

      $mNumber    = new Number();
      if (empty($data['data']))
      {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }
      else
      {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $dataList = $data['data'];

         #        print_r($dataList);
         $debet = 0; $kredit = 0;
         for ($i = 0;$i < sizeof($dataList);$i++)
         {
            $no = $i + $data['start'];
            $debit += $dataList[$i]['debet'];
            $kredit += $dataList[$i]['kredit'];

            if ($no % 2 != 0) $dataList[$i]['class_name'] = 'table-common-even';
            else $dataList[$i]['class_name'] = '';
            $dataList[$i]['debet'] = number_format($dataList[$i]['debet'], 2, ',', '.');
            $dataList[$i]['kredit'] = number_format($dataList[$i]['kredit'], 2, ',', '.');
            $this->mrTemplate->AddVars('data_item', $dataList[$i], '');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }
      $data['transaksi']['terbilang'] = $mNumber->Terbilang($data['transaksi']['nominal'], 0);
      $data['transaksi']['total_debet'] = number_format($debit, 2, ',', '.');
      $data['transaksi']['total_kredit'] = number_format($kredit, 2, ',', '.');
      $this->mrTemplate->AddVars('content', $data['transaksi'], '');
   }
}
?>