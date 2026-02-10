<?php

/**
 *
 * class ViewDetilKelompokLaporanAnggaran
 * @package kelompok_laporan_anggaran
 * @subpackage business
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since 6 september 2012
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/kelompok_laporan_anggaran/business/KelompokLaporanAnggaran.class.php';

class ViewDetilKelompokLaporanAnggaran extends HtmlResponse
{
	protected $mPesan;
	protected $mCss;
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
		'module/kelompok_laporan_anggaran/template');
		$this->SetTemplateFile('view_detil_kelompok_laporan_anggaran.html');
	}
	
	public function ProcessRequest()
	{
		$Obj = new KelompokLaporanAnggaran();
	
		$idKelLap = Dispatcher::Instance()->Decrypt($_GET['dataId']);
		$dataDetil = $Obj->GetDataDetil($idKelLap);
		
		/**
		 * untuk menampilkan data paguBas/Mas
		 */
		
		$dataPaguBasMak = $Obj->GetDataPaguBasMak($idKelLap);
		
		$return['data_detil'] = $dataDetil;
		$return['data_pagu_bas_mak'] = $dataPaguBasMak;
		$return['start'] = $startRec + 1;
		$return['id_kelompok_laporan'] = $idKelLap;
		
		return $return;
	}
	
	
	public function ParseTemplate($data = NULL)
	{
		/**
		 * get data detail kelompok laporan anggaran
		 */
		 
		$this->mrTemplate->AddVar('detil_kelompok_laporan', 'NAMA',$data['data_detil']['nama']);
		$this->mrTemplate->AddVar('detil_kelompok_laporan', 'BENTUK_TRANSAKSI',$data['data_detil']['bentuk_transaksi']);
		$this->mrTemplate->AddVar('detil_kelompok_laporan', 'JENIS_LAPORAN',$data['data_detil']['jenis_laporan']);
		
		/**
		 * end
		 */
	

		if (empty($data['data_pagu_bas_mak'])){
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
		} else {
			
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
			$dataPaguBasMak = $data['data_pagu_bas_mak'];
			for ($i = 0;$i < sizeof($dataPaguBasMak);$i++) {
				$no = $i + $data['start'];
				$dataPaguBasMak[$i]['number'] = $no;

				if ($no % 2 != 0) $dataPaguBasMak[$i]['class_name'] = 'table-common-even';
				else $dataPaguBasMak[$i]['class_name'] = '';
																	
				$this->mrTemplate->AddVars('data_item', $dataPaguBasMak[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');
			}
		}
	}
}
