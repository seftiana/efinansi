<?php

/**
 * Class ViewCetakLapKertasKerjaKonversiRupiahMurni
 * @package lap_kertas_kerja_konversi_rupiah_murni
 * @copyright 2011 Gamatechno
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/lap_kertas_kerja_konversi_rupiah_murni/business/'.
	'LapKertasKerjaKonversiRupiahMurni.class.php';

/**
 * Class ViewCetakLapKertasKerjaKonversiRupiahMurni
 * @todo untuk menyetak Lap Kertas Kerja Konversi Rupiah Murni
 */
class ViewCetakLapKertasKerjaKonversiRupiahMurni extends HtmlResponse
{
   public function TemplateBase(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
	  	'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

   public function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
	  	'module/lap_kertas_kerja_konversi_rupiah_murni/template');
      $this->SetTemplateFile('view_cetak_lap_kertas_kerja_konversi_rupiah_murni.html');
   }
   public function ProcessRequest()
   {
		$LapKertarKerjaKonversiRupiahMurni = new LapKertasKerjaKonversiRupiahMurni();

      	if (isset($_GET['idTa'])) 
			$idTahunAnggaran = Dispatcher::Instance()->Decrypt($_GET['idTa']);
        
        if (isset($_GET['start_rec']))            
        $start_rec = Dispatcher::Instance()->Decrypt($_GET['start_rec']);
        
        if (isset($_GET['item_viewed']))
        $item_viewed = Dispatcher::Instance()->Decrypt($_GET['item_viewed']);  
      	
      	/**
      	 * membuat tanggal
      	 */
      	if(isset($_GET['trans_tanggal'])){
			$trans_tanggal_selected = Dispatcher::Instance()->Decrypt($_GET['trans_tanggal']);
		} else {
			$trans_tanggal_selected= date('Y').'-'.date('m').'-'.date('d');
		}
		$return['tanggal_sekarang']= $trans_tanggal_selected;
      	$return['tahun_anggaran'] = $LapKertarKerjaKonversiRupiahMurni->GetTahunAnggaranById($_GET['idTa']);
 	$return['data'] = 
	  	$LapKertarKerjaKonversiRupiahMurni->GetDataLapKertasKerjaKonversiRupiahMurni($idTahunAnggaran,
		  	$trans_tanggal_selected,$start_rec,$item_viewed);
      
      return $return;
   }
   
   public function ParseTemplate($data = NULL)
   {
		$today = getdate();
      	$this->mrTemplate->AddVar('content', 'tanggal_sekarang',$data['tanggal_sekarang']);
      	$this->mrTemplate->AddVars('content', $data['tahun_anggaran'][0], '');
      	$this->mrTemplate->AddVar('content', 'SALDO', $data['saldo']);
      	if (empty($data['data']))
      	{
         	$this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      	}
      	else
      	{
         	$this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         	$dataList = $data['data'];

         	for ($i = 0;$i < sizeof($dataList);$i++)
         	{
            	$no = $i + $data['start'];
            	if(!is_null($dataList[$i]['sak_jumlah'])){
					$dataList[$i]['sak_jumlah']=number_format($dataList[$i]['sak_jumlah'], 0, ',', '.');
				}
				if(!is_null($dataList[$i]['sap_jumlah'])){
					$dataList[$i]['sap_jumlah']=number_format($dataList[$i]['sap_jumlah'], 0, ',', '.');
				}
            	if ($no % 2 != 0) $dataList[$i]['class_name'] = 'table-common-even';
            	else $dataList[$i]['class_name'] = '';
            	$this->mrTemplate->AddVars('data_item', $dataList[$i], '');
            	$this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }
   }
}