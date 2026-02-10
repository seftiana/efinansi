<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/rincian_perhitungan_rencana_penerimaan/business/AppRencanaPenerimaan.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewCetakRencanaPenerimaan extends HtmlResponse {	

   	
	function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
	  		'module/rincian_perhitungan_rencana_penerimaan/template');
      $this->SetTemplateFile('cetak_rencana_penerimaan.html');
   }
   
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
	  $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

	function ProcessRequest() {	
		
		$Obj = new AppRencanaPenerimaan();
		$tahun_anggaran = Dispatcher::Instance()->Decrypt($_GET['tgl']);
		$unit_id = Dispatcher::Instance()->Decrypt($_GET['unitkerjaid']);
		$unit_label = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
		$userId = Dispatcher::Instance()->Decrypt($_GET['id']);
		$data = $Obj->GetDataUnitkerja($tahun_anggaran,$unit_id); 
		$data_jumlah = $Obj->GetDataForTotal($tahun_anggaran, $userId, $unit_id);
		$jml = count($data_jumlah);
		$tot_jumlah = 0;
		$tot_terima = 0;
		for($i=0;$i<=$jml;$i++){	
			$tot_jumlah += $data_jumlah[$i]['tot_jumlah'];
			$tot_terima += $data_jumlah[$i]['tot_terima'];
		}
		$periode = $Obj->GetTahunAnggaran($tahun_anggaran);
		$return['data'] = $data;
		$return['penerimaan'] = $tot_jumlah;
		$return['jumlah'] = $tot_terima;
		$return['periode'] = $periode['name'];
		$return['unit_label'] = $unit_label;
		return $return;
		}
		
		function ParseTemplate($data = NULL) {
		 $this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', $data['periode']);
		 $this->mrTemplate->AddVar('content', 'UNIT_KERJA_LABEL', $data['unit_label']);
		//print_r($data['data']);
		$date = date('d-m-Y');
		if (empty($data['data'])) {
			 $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
		} else {
			 $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
			 
			 $total='';
			 $jumlah_total='';
			 $idrencana='';
			 $idkode='';
			 $kode='';
			 $nama='';

			 $data_list = $data['data'];
			 $kode_satker = ''; 
			 $kode_unit = ''; 
			 $nama_satker=''; 
			 $nama_unit='';

			 //print_r($data_list);
			 for ($i=0; $i<sizeof($data_list);) {
			 
					if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $number);
					if($i == sizeof($data_list)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $number);
					
			 if(($data_list[$i]['kode_satker'] == $kode_satker) && ($data_list[$i]['kode_unit'] == $kode_unit)) {
					if($data_list[$i]['idrencana'] == "") {
						$i++; continue;
					}
					$send = $data_list[$i];
					$send['total_penerimaan'] = number_format($data_list[$i]['total'], 0, ',', '.');
					$send['volume'] = $data_list[$i]['volume'];
					$send['pagu'] = $data_list[$i]['pagu'];
					$send['tarif'] = number_format($data_list[$i]['tarif'], 0, ',', '.');
					$send['totalterima'] = number_format($data_list[$i]['total_kali'], 0, ',', '.');
					
					$send['class_name'] = "";
					$send['nomor'] = $no;
					$send['class_button'] = "links";


					$this->mrTemplate->AddVar('cekbox', 'data_number', $number);
					$this->mrTemplate->AddVar('cekbox', 'data_idrencana', $data_list[$i]['idrencana']);
					$this->mrTemplate->AddVar('cekbox', 'data_nama', $data_list[$i]['nama']);
					$this->mrTemplate->AddVar('cekbox', 'IS_SHOW', 'YES');
					$i++;$no++;$number++;
				 } elseif($data_list[$i]['kode_satker'] != $kode_satker && $data_list[$i]['nama_satker'] == $data_list[$i]['nama_unit']) {
					 $kode_satker = $data_list[$i]['kode_satker'];
					 $kode_unit = $data_list[$i]['kode_unit'];
					 $nama_satker = $data_list[$i]['nama_satker'];
					 $nama_unit = $data_list[$i]['nama_unit'];
					 $send['kode'] = "<b>".$kode_unit."</b>";
					 $send['nama'] = "<b>".$data_list[$i]['nama_unit']."</b>";
					 
					 $send['total_penerimaan'] = "<b>".number_format($data_list[$i]['jumlah_total'], 0, ',', '.')."</b>";
					 $send['volume'] = "";
					 $send['pagu'] = "";
					 $send['tarif'] = "";
					 $send['totalterima'] = "<b>".number_format($data_list[$i]['totalterima'], 0, ',', '.')."</b>";
					 //print_r($send['jumlah_total']."<br/>");
					 
					 $send['class_name'] = "table-common-even1";
					 $send['nomor'] = "";
					 $send['class_button'] = "toolbar";
						
					 $no=1;
					// }
				 } elseif($data_list[$i]['kode_unit'] != $kode_unit) {
					 $kode_satker = $data_list[$i]['kode_satker'];
					 $kode_unit = $data_list[$i]['kode_unit'];
					 $nama_satker = $data_list[$i]['nama_satker'];
					 $nama_unit = $data_list[$i]['nama_unit'];
					 $send['kode'] = "<b>".$kode_unit."</b>";
					 $send['nama'] = "<b>".$data_list[$i]['nama_unit']."</b>";
					 $send['total_penerimaan'] = "<b>".number_format($data_list[$i]['jumlah_total'], 0, ',', '.')."</b>";
					 $send['class_name'] = "";
					 $send['volume'] = "";
					 $send['pagu'] = "";
					 $send['totalterima'] = "<b>".number_format($data_list[$i]['totalterima'], 0, ',', '.')."</b>";
					 $send['tarif'] = "";
					 $send['nomor'] = "";
					 $send['class_button'] = "toolbar";

					 $no=1;
				 }	
				 	$this->mrTemplate->AddVars('data_item', $send, 'DATA_');
					$this->mrTemplate->parseTemplate('data_item', 'a');
			}
			$jumlah = "<b>".number_format($data['jumlah'], 0, ',', '.')."</b>";
			$terima = "<b>".number_format($data['penerimaan'], 0, ',', '.')."</b>";
			$this->mrTemplate->AddVar('content', 'DATA_JUMLAH',$jumlah);
			$this->mrTemplate->AddVar('content', 'DATA_TERIMA',$terima);
		}
		
      
	} 
}
?>