<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/spp/business/spp.class.php';

class ViewAddSpp extends HtmlResponse{
function TemplateModule(){
    $this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/spp/template/');
    $this->setTemplateFile('add_spp.html');
}
function ProcessRequest(){
	$obj					= new Spp();
	$sifatPembayaranArray	= $obj->ComboSifatPembayaran();
	$jenisPembayaranArray	= $obj->ComboJenisPembayaran();
	$return['id']			= Dispatcher::Instance()->Decrypt($_GET['id']);
	$return['ta']			= $_GET['ta'];
	$return['unit']			= $_GET['unit'];
	$msg = Messenger::Instance()->Receive(__FILE__);
	$return['Pesan'] 		= $msg[0][1];
	$return['Post'] 		= $msg[0][0];
	$return['css']			= $msg[0][2];
	
	if(isset($return['Post'])){
		$sifat_bayar	= $return['Post']['sifat_bayar']; 
		$jenis_bayar	= $return['Post']['jenis_bayar'];
		$return['id']	= $return['Post']['id_pengeluaran'];
		$return['ta']	= $return['Post']['thn_anggaran'];
		$return['unit']	= $return['Post']['unit'];
	}
	
	Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'jenis_bayar', 
						   array(
								'jenis_bayar',
								$jenisPembayaranArray,
								$jenis_bayar,
								'kosong',
								' style="width:175px;" '
								) , 
						  Messenger::CurrentRequest);
	Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'sifat_bayar', 
						   array(
								'sifat_bayar',
								$sifatPembayaranArray,
								$sifat_bayar,
								'kosong',
								' style="width:175px;" '
								) , 
						  Messenger::CurrentRequest);
	
	$return['data']		= $obj->GetDataById($return['id'],$return['ta'],$return['unit']);
    return $return;
}

function ParseTemplate($data = null){
	if ($data['Pesan']) {
		$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
		$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['Pesan']);
		$this->mrTemplate->AddVar('warning_box', 'CSS', $data['css']);
		$this->mrTemplate->AddVar('content','TXT_KEPERLUAN',$data['Post']['keperluan']);
		$this->mrTemplate->AddVar('content','TXT_JENIS_BELANJA',$data['Post']['jenis_belanja']);
		$this->mrTemplate->AddVar('content','TXT_NAMA',$data['Post']['nama']); 
		$this->mrTemplate->AddVar('content','TXT_ALAMAT',$data['Post']['alamat']); 
		$this->mrTemplate->AddVar('content','TXT_REKENING',$data['Post']['rekening']); 
		$this->mrTemplate->AddVar('content','TXT_NO_SPK',$data['Post']['no_spk']);
		$this->mrTemplate->AddVar('content','TXT_NILAI_SPK',$data['Post']['nilai_spk']); 
	}
	$this->mrTemplate->AddVar('content','THN_ANGGARAN',$data['ta']);
	$this->mrTemplate->AddVar('content','UNIT',$data['unit']);
	$url_add	= Dispatcher::Instance()->GetUrl('spp','AddSpp','do','html');
	$this->mrTemplate->AddVar('content','URL_ACTION',$url_add);
	
	$this->mrTemplate->AddVar('content','ID_PENGELUARAN',$data['id']);
	$this->mrTemplate->AddVar('content','DANA',
							  number_format($data['data']['nominal_approve'],2,',','.'));
	$this->mrTemplate->AddVar('content','DANA_TEXT',
							  number_format($data['data']['nominal_approve'],2,',',''));
	$this->mrTemplate->AddVar('content','PAGU_DIPA', 
							  number_format($data['data']['pagu_dipa'],2,',','.'));
	$sisa_dana		= $data['data']['pagu_dipa'] - $data['data']['nominal_approve'];
	$this->mrTemplate->AddVar('content','SISA_DANA',
							  number_format($sisa_dana,2,',','.'));
    //$this->mrTemplate->AddVar('CONTENT','CONTENT_NAME','CONTENT');
}
}
?>