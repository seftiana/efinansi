<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/transaksi_penerimaan/business/AppTransaksi.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewNo extends HtmlResponse
{
	var $Pesan;
	function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/transaksi_penerimaan/template');
		$this->SetTemplateFile('view_no.html');
	}
	function ProcessRequest() 
	{

		//instance2
		$Obj = new AppTransaksi();
		$userUnitKerjaObj = new UserUnitKerja();

		//menerima pos dan get
		$this->_POST = $_POST->AsArray();
		$this->_GET = $_GET->AsArray();

		//siapa yang login?
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$role = $userUnitKerjaObj->GetRoleUser($userId);
		
		if ($role['role_name'] == "Administrator") 
		{
			$unitId = $_GET['unitkerja']->Integer()->Raw();
			$unit = $userUnitKerjaObj->GetSatkerUnitKerja($unitId);
			$this->Data['unitkerja'] = $unit['unit_kerja_id'];
			$this->Data['unitkerja_label'] = $unit['unit_kerja_nama'];
			
			if (isset($_GET['dataId'])) 
			{
				$this->Data['unitkerja'] = $datadb['satker_id'];
				$this->Data['unitkerja_label'] = $datadb['satker_nama'];
			}
		}
		elseif ($role['role_name'] == "OperatorUnit") 
		{
			$unitId = $_GET['unitkerja']->Integer()->Raw();
			$unit = $userUnitKerjaObj->GetSatkerUnitKerja($unitId);
			$this->Data['unitkerja'] = $unitkerja['satker_id'];
			$this->Data['unitkerja_label'] = $unitkerja['satker_nama'];
			
			if (isset($_GET['dataId'])) 
			{
				$this->Data['unitkerja'] = $datadb['unitkerja'];
				$this->Data['unitkerja_label'] = $datadb['unitkerja_label'];
			}
		}
		else
		{
			$unit = $userUnitKerjaObj->GetSatkerUnitKerjaUserDua($userId);
			$this->Data['unitkerja'] = $unit['unit_kerja_id'];
			$this->Data['unitkerja_label'] = $unit['unit_kerja_nama'];
		}
		
		if ($unit['unit_kerja_id'] == '-') 
		{
			$unit['unit_kerja_id'] = $unit['satker_id'];
			$unit['unit_kerja_kode'] = $unit['satker_kode'];
			$unit['unit_kerja_nama'] = $unit['satker_nama'];
		}
		$count_bukti_trans = $Obj->CountBuktiTrans($this->_GET['tipe_transaksi'], date("m") , $unit['unit_kerja_id']);
		$return['tipe_transaksi'] = $this->_GET['dataId'];
		$return['count_bukti_trans'] = $count_bukti_trans;
		$return['count_bukti_trans']['unit_kerja_kode'] = $unit['unit_kerja_kode'];
		
		return $return;
	}
	function ParseTemplate($data = NULL) 
	{
		switch ($data['tipe_transaksi']) 
		{
		case "1":
			$kode = "BKM";
		break;
		case "2":
			$kode = "BKK";
		break;
		case "3":
			$kode = "BM";
		break;
		}
		if($kode==""){
			$kode = "BKM";
		}
		
		$this->mrTemplate->AddVar('content', 'KODE_TRANS', $kode);
		$this->mrTemplate->AddVar('content', 'COUNT_NO_BUKTI_COUNT', $data['count_bukti_trans']['count_trans']);
		$this->mrTemplate->AddVar('content', 'COUNT_NO_BUKTI_UNIT', $data['count_bukti_trans']['unit_kerja_kode'] . '/' . date("m.Y"));
	}
}
?>
