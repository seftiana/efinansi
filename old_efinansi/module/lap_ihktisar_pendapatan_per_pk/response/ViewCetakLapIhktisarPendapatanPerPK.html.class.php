<?php

/**
 * 
 * class ViewCetakLapIhktisarPendapatanPerPK
 * @package lap_ihktisar_perndapatan_per_pk
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_ihktisar_pendapatan_per_pk/business/LapIhktisarPendapatanPerPK.class.php';


class ViewCetakLapIhktisarPendapatanPerPK extends HtmlResponse
{
	
	/**
	 * untuk menginstanskan class database object
	 */
	protected $mDBObj; 
	protected $mModulName;
	protected $mData;
	
	public function __construct()
	{
		parent::__construct();
		$this->mDBObj = new LapIhktisarPendapatanPerPK();
		$this->mModulName = 'lap_ihktisar_pendapatan_per_pk';
	}
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .     
                'module/'.$this->mModulName.'/template');
		$this->SetTemplateFile('view_cetak_lap_ihktisar_pendapatan_per_pk.html');
	}

    public function TemplateBase() 
    {
       $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
		'main/template/');
	   $this->SetTemplateFile('document-print.html');
       $this->SetTemplateFile('layout-common-print.html');
    }
   	
	public function ProcessRequest()
	{
		
		$tahun_anggaran = Dispatcher::Instance()->Decrypt($_GET['tgl']);
		$unitkerja_label = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
		$unitkerja = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
		$userId = Dispatcher::Instance()->Decrypt($_GET['id']);
		
		$return['unit_kerja_id'] = $unitkerja;
		$return['unit_kerja_nama'] = $unitkerja_label;
		$return['penerimaan'] = $tot_jumlah;
		$return['jumlah'] = $tot_terima;
		$return['ta'] = $this->mDBObj->GetTahunAnggaran($tahun_anggaran);
		$return['get_data'] = $this->mDBObj->GetDataLaporan($tahun_anggaran,$unitkerja);
		
		return $return;
   }
   
   public function ParseTemplate($data = NULL)
   {
	   		
		$this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', ($data['ta']['name']));
		$this->mrTemplate->AddVar('content', 'UNIT_KERJA_LABEL', ($data['unit_kerja_nama']));
		$this->mrTemplate->AddVar('content', 'TAHUN_SEKARANG_TH_ANGGAR', ($data['ta']['name']));
		$this->mrTemplate->AddVar('content', 'TAHUN_DEPAN_TH_ANGGAR', ($data['ta']['tahun_tutup'] + 1));
		$dataLaporan = $data['get_data'];
		if(empty($dataLaporan)){
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'YES');		
	    } else {
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'NO');		
		
			for($k = 0;$k < sizeof($dataLaporan);$k++){
				$total += $dataLaporan[$k]['target'];
				$dataLaporan[$k]['target'] = $this->mDBObj->SetFormatAngka($dataLaporan[$k]['target']);
				$this->mrTemplate->AddVars('list_data_item', $dataLaporan[$k], '');
				$this->mrTemplate->parseTemplate('list_data_item', 'a');
			}
			$total = $this->mDBObj->SetFormatAngka($total);
			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL', $total);
		}
   }
   
}
