<?php

/**
 * @package lap_rencana_penerimaan_alokasi_unit
 * @since 24 Februari 2012
 * @copyright (c) 2012 Gamatechno
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/lap_rencana_penerimaan_alokasi_unit_v2/business/AppLapRencanaPenerimaanAlokasiUnit.class.php';
		
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewCetakLapRencanaPenerimaanAlokasiUnit extends HtmlResponse 
{

	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
	  			'module/lap_rencana_penerimaan_alokasi_unit_v2/template');
  		$this->SetTemplateFile('cetak_lap_rencana_penerimaan_alokasi_unit.html');
   	}

	public function TemplateBase() 
	{
 		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
	  	$this->SetTemplateFile('document-print-custom-wide.html');
      	$this->SetTemplateFile('layout-common-print.html');
   	}

	public function ProcessRequest() 
	{
		$Obj = new AppLapRencanaPenerimaanAlokasiUnit();
		$UserUnitKerja = new UserUnitKerja();
		$tahun_anggaran = Dispatcher::Instance()->Decrypt($_GET['tgl']);
		$unitkerja_label = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
		$unitkerja = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
		$kodePenerimaanId = Dispatcher::Instance()->Decrypt($_GET['kode_penerimaan_id']);
		$userId = Dispatcher::Instance()->Decrypt($_GET['id']);
		
		$data = $Obj->GetDataRencanaPenerimaan($tahun_anggaran, $unitkerja,$kodePenerimaanId);
		$unitkerja = $UserUnitKerja->GetUnitKerja($unitkerja);
		$tahunanggaran = $Obj->GetTahunAnggaran($tahun_anggaran);
		
		$return['data'] = $data;
		$return['tahunanggaran_nama'] = $tahunanggaran['name'];
		$return['unitkerja_nama'] = $unitkerja['unit_kerja_nama'];
		
		return $return;
	}

	public function ParseTemplate($data = NULL) 
	{
		$this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', $data['tahunanggaran_nama']);
		$this->mrTemplate->AddVar('content', 'UNIT_KERJA_LABEL', $data['unitkerja_nama']);

		if (empty($data['data'])) {
			 $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
		} else {
			 $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');

			 $data_list = $data['data'];
			 for ($i=0; $i < sizeof($data_list);) {
			 		$send = $data_list[$i];
			 		$send['class_name'] = '';
 					$send['nomor'] = $i +1;
					if($i == 0){
						$send['nama_unit_p']=$data_list[$i]['unit_kerja_sumber_nama'];		
					} else{
						$send['nama_unit_p'] = 
							(($data_list[$i - 1]['unit_kerja_sumber_id'] != $data_list[$i]['unit_kerja_sumber_id']) ? 
										$data_list[$i]['unit_kerja_sumber_nama']: ''); 
						
					}			
				    $send['nama_unit']=$data_list[$i]['unit_kerja_nama'];	
					$send['nama']=$data_list[$i]['kode_penerimaan_nama'];
					$send['kode']=$data_list[$i]['kode_penerimaan'];
					$send['nama']=$data_list[$i]['kode_penerimaan_nama'];
					$send['keterangan']=$data_list[$i]['keterangan'];
					$send['januari']=number_format($data_list[$i]['januari'],0,',','.');	
					$send['februari']=number_format($data_list[$i]['februari'],0,',','.');
					$send['maret']=number_format($data_list[$i]['maret'],0,',','.');
					$send['april']=number_format($data_list[$i]['april'],0,',','.');
					$send['mei']=number_format($data_list[$i]['mei'],0,',','.');
					$send['juni']=number_format($data_list[$i]['juni'],0,',','.');
					$send['juli']=number_format($data_list[$i]['juli'],0,',','.');
					$send['agustus']=number_format($data_list[$i]['agustus'],0,',','.');
					$send['september']=number_format($data_list[$i]['september'],0,',','.');
					$send['oktober']=number_format($data_list[$i]['oktober'],0,',','.');
					$send['november']=number_format($data_list[$i]['november'],0,',','.');
					$send['desember']=number_format($data_list[$i]['desember'],0,',','.');
					$send['total_terima']=number_format($data_list[$i]['total_terima'],0,',','.');
					
					$this->mrTemplate->AddVars('data_item',$send, 'DATA_');
					$this->mrTemplate->parseTemplate('data_item', 'a');
					$i++;
					
			 }
			 
			 /**
			  * total 
			  */
			  
			$total_terima = 0;
			$total_jan = 0;
			$total_feb = 0;
			$total_mar = 0;
			$total_apr = 0;
			$total_mei = 0;
			$total_jun = 0;
			$total_jul = 0;
			$total_ags = 0;
			$total_sep = 0;
			$total_okt = 0;
			$total_nov = 0;
			$total_des = 0;
			foreach($data_list as $key => $value){
			  $total_terima+= $value['total_terima'];
			   $total_jan+= $value['januari'];
			   $total_feb+= $value['februari'];
			   $total_mar+= $value['maret'];
			   $total_apr+= $value['april'];
			   $total_mei+= $value['mei'];
			   $total_jun+= $value['juni'];
			   $total_jul+= $value['juli'];
			   $total_ags+= $value['agustus'];
			   $total_sep+= $value['september'];
			   $total_okt+= $value['oktober'];
			   $total_nov+= $value['november'];
			   $total_des+= $value['desember'];
         }
         $this->mrTemplate->AddVar('data_total', 'T_TOTAL_TERIMA', number_format($total_terima, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_JANUARI', number_format($total_jan, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_FEBRUARI', number_format($total_feb, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_MARET', number_format($total_mar, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_APRIL', number_format($total_apr, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_MEI', number_format($total_mei, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_JUNI', number_format($total_jun, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_JULI', number_format($total_jul, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_AGUSTUS', number_format($total_ags,0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_SEPTEMBER', number_format($total_sep, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_OKTOBER', number_format($total_okt, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_NOVEMBER', number_format($total_nov, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_DESEMBER', number_format($total_des, 0, ',', '.'));
         
		    /**
		     * end total
		     */
			
		}

	}
}
?>