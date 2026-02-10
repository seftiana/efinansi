<?php
/**
 * @package ViewCetakRencanaPengeluaranRkakl
 * @copyright Copyright (c) PT Gamatechno Indonesia
 * @Analyzed By Dyan Galih <galih@gamatechno.com>
 * @author Dyan Galih <galih@gamatechno.com>
 * @version 0.1
 * @startDate 2011-09-21
 * @lastUpdate 2011-09-21
 * @description View Cetak Rencana Pengeluaran Rkakl
 */
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/rencana_pengeluaran/business/RencanaPengeluaran.class.php';

class ViewCetakRencanaPengeluaranRkakl extends HtmlResponse
{
   function TemplateBase()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/rencana_pengeluaran/template');
      $this->SetTemplateFile('view_cetak_rencana_pengeluaran_rkakl.html');
   }
   function ProcessRequest()
   {
      $obj = new RencanaPengeluaran;

      if (isset($_GET['grp']))
      {

         if (is_object($_GET['grp'])) $this->data['kegiatandetail_id'] = $_GET['grp']->mrVariable;
         else $this->data['kegiatandetail_id'] = $_GET;
      }
      $return['data'] = $obj->GetDataCetak($this->data['kegiatandetail_id']);

      if (isset($_GET['ket']))
      {
         $ket = 'NonBudget';
      }
      else $ket = '';
      $return['ket'] = $ket;

      return $return;
   }
   function ParseTemplate($data = NULL)
   {
      $this->mrTemplate->AddVars('content', $data['data'][0]);
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
            $total += $dataList[$i]['jumlah'];

            $dataList[$i]['kuantitas']=number_format($dataList[$i]['kuantitas'],0,',','.');
            $dataList[$i]['jumlah']=number_format($dataList[$i]['jumlah'],0,',','.');

            if(($dataList[$i]['IsBiayaLangsung']=='Biaya Tidak Langsung') and ($dataList[$i]['IsBiayaRelatif']!='Biaya Variabel')){
               $this->mrTemplate->AddVars('biaya_tidak_langsung_tetap', $dataList[$i], '');
               $this->mrTemplate->parseTemplate('biaya_tidak_langsung_tetap', 'a');
            }

            if(($dataList[$i]['IsBiayaLangsung']=='Biaya Tidak Langsung') and ($dataList[$i]['IsBiayaRelatif']=='Biaya Variabel')){
               $this->mrTemplate->AddVars('biaya_tidak_langsung_variable', $dataList[$i], '');
               $this->mrTemplate->parseTemplate('biaya_tidak_langsung_variable', 'a');
            }

            if(($dataList[$i]['IsBiayaLangsung']!='Biaya Tidak Langsung') and ($dataList[$i]['IsBiayaRelatif']=='Biaya Variabel')){
               $this->mrTemplate->AddVars('biaya_langsung_variable', $dataList[$i], '');
               $this->mrTemplate->parseTemplate('biaya_langsung_variable', 'a');
            }

            if(($dataList[$i]['IsBiayaLangsung']!='Biaya Tidak Langsung') and ($dataList[$i]['IsBiayaRelatif']!='Biaya Variabel')){
               $this->mrTemplate->AddVars('biaya_langsung_tetap', $dataList[$i], '');
               $this->mrTemplate->parseTemplate('biaya_langsung_tetap', 'a');
            }
         }
      }

      $this->mrTemplate->AddVar('content', 'TOTAL_ANGGARAN', number_format($total,0,',','.'));
   }
}
?>