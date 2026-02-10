<?php

/**
 * class ViewLapProgramKegiatanParent
 * @package lap_program_kegiatan
 * @subpackage response
 * @todo untuk menampilkan program kegiatan
 * @since mei 2012
 * @copyright 2012 Gamatechno Indonesia
 * @author noor hadi <noor.hadi@gamatechno.com>
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/lap_program_kegiatan/business/LapProgramKegiatan.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLapProgramKegiatanParent extends HtmlResponse
{

	protected $mLapProgramKegiatan;
    protected $mUserUnitKerja;

	protected $data;
    
	public function ViewLapProgramKegiatanParent()
	{
		$this->mLapProgramKegiatan = new LapProgramKegiatan();
        $this->mUserUnitKerja = new UserUnitKerja();
	}
	
    public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
				'module/lap_program_kegiatan/template');
		$this->SetTemplateFile('view_program_kegiatan_parent.html');
	}
    
	public function ProcessRequest()
	{
		$ta_id_selected = ''; //inisialisasi
		
		$userid      = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
		$unitkerja   = $this->mUserUnitKerja->GetUnitKerjaUser($userid);
		
		$this->data  = $_GET->AsArray();
		$totalSubUnit = $this->mLapProgramKegiatan->GetTotalSubUnitKerja(
								$unitkerja['unit_kerja_id']);
		
		$this->data['unit_id']    = $unitkerja['unit_kerja_id'];
		$this->data['unit_nama']  = $unitkerja['unit_kerja_nama'];
		
		if (isset($_POST['btnTampilkan']))
		{
			if (is_object($_POST['data']))
			{
				$this->data = $_POST['data']->AsArray();
			}
			else
			{
				$this->data = $_POST['data'];
			}
			$ta_id_selected = $this->data['ta_id'];

			if(!isset($this->data['unit_id']))
			{
				$this->data['unit_id'] = $unitkerja['unit_kerja_id'];
				$this->data['unit_nama'] = $unitkerja['unit_kerja_nama'];
			}
		}elseif(isset($_GET['page'])){
			$this->data['unit_id']		= Dispatcher::Instance()->Decrypt($_GET['unit_id']);
			$this->data['unit_nama']	= Dispatcher::Instance()->Decrypt($_GET['unit_nama']);
			$ta_id_selected				= Dispatcher::Instance()->Decrypt($_GET['ta_id']);
			$this->data['ta_id']		= Dispatcher::Instance()->Decrypt($_GET['ta_id']);
		}

		//############################ start combo box#################################3
		$arr_ta 	= $this->mLapProgramKegiatan->GetDataTahunAnggaran($ta_id_selected);
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'data[ta_id]',
		array(
			'data[ta_id]',
			$arr_ta,
			$ta_id_selected,
			'kosong',
			' style="width:150px;" '
		) , Messenger::CurrentRequest);
		
			//############################ end combo box#################################3

		if ($this->data['ta_id'] == '')
		{
			$this->data['ta_id'] = $ta_id_selected;
		}
		
		
		$dataList = $this->mLapProgramKegiatan->GetListProgramKegiatan(
                                                    0,$ta_id_selected,$this->data['unit_id']);
		$return['data']			= $dataList;
		$return['search']		= $search;
		$return['unitkerja']	= $unitkerja;
		$return['totalSubUnit'] = $totalSubUnit;
        $return['ta'] = $ta_id_selected;
        $return['ta_nama'] = $this->mLapProgramKegiatan->GetDataTahunAnggaranNama($ta_id_selected);
		return $return;
	}
	public function ParseTemplate($data = NULL)
	{
	   
		$url_search		= Dispatcher::Instance()->GetUrl('lap_program_kegiatan', 
                                                         'lapProgramKegiatanParent', 
														 'view', 'html');
		$url_cetak		= Dispatcher::Instance()->GetUrl('lap_program_kegiatan',
														'cetakProgramKegiatan','view', 'html');
        $url_pk_tree		= Dispatcher::Instance()->GetUrl('lap_program_kegiatan',
														'lapProgramKegiatanAsyn','view', 'html').
                                                        '&ascomponent=1'.
                                                        '&ta='.$data['ta'].
                                                        '&unit_id='. $this->data['unit_id'];
		$popup_unit_kerja	= Dispatcher::Instance()->GetUrl('lap_program_kegiatan', 'PopupUnitKerja', 
															'view', 'html');
                                                            
        $url_excel_x    = Dispatcher::Instance()->GetUrl('lap_program_kegiatan',
                                                        'excelLapProgramKegiatan','view', 'xlsx');
		
		
		if($data['totalSubUnit'] > 0 ){
			$this->mrTemplate->AddVar('cek_unit_kerja_parent', 'IS_PARENT', 'YES');
		} else {
			$this->mrTemplate->AddVar('cek_unit_kerja_parent', 'IS_PARENT', 'NO');
		}
        $this->mrTemplate->AddVar('content', 'URL_PK_TREE', $url_pk_tree);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', $url_search);
		$this->mrTemplate->AddVar('content', 'URL_CETAK', $url_cetak);
		$this->mrTemplate->AddVar('content', 'TH_ANGGAR', $data['ta']);
        $this->mrTemplate->AddVar('content', 'TH_ANGGAR_NAMA', $data['ta_nama']);
        $this->mrTemplate->AddVar('content', 'UNIT_NAMA', $this->data['unit_nama']);
        
        $this->mrTemplate->AddVar('content', 'URL_EXCEL_X', $url_excel_x);
        
		$this->mrTemplate->AddVar('content', 'POPUP_UNIT_KERJA', $popup_unit_kerja);
		$this->mrTemplate->AddVar('content', 'URL_RESET', $url_search);
		$this->mrTemplate->AddVar('cek_unit_kerja_parent', 'SEARCH_UNIT_NAMA', 
				str_replace(chr(92),'',$this->data['unit_nama']));
		$this->mrTemplate->AddVar('cek_unit_kerja_parent', 'SEARCH_UNIT_ID', $this->data['unit_id']);
		$this->mrTemplate->AddVar('content', 'SEARCH_UNIT_NAMA', 
				str_replace(chr(92),'',$this->data['unit_nama']));
		$this->mrTemplate->AddVar('content', 'SEARCH_UNIT_ID', $this->data['unit_id']);
		$this->mrTemplate->AddVar('content', 'SEARCH_NAME', $this->data['nama']);
		$this->mrTemplate->AddVar('content', 'SEARCH_KODE', $this->data['kode']);

		if (isset($data['msg']))
		{
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);

			if ($data['msg']['action'] == 'msg') $class = 'notebox-done';
			else $class = 'notebox-warning';
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);
		}
        
		if (empty($data['data']))
		{
			$this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
			$dataGrid = $data['data'];
			for ($j = 0;$j < sizeof($dataGrid);$j++)
			{
			    $dataGrid[$j]['nomor']= $j + 1;
                
                    $c = $this->mLapProgramKegiatan->CountNominalProgram(
                                                            $dataGrid[$j]['id'],
                                                            $data['ta'],
                                                            $this->data['unit_id']);
               
                    $dataGrid[$j]['langsung_tetap'] = $c['b_langsung_tetap'];
                    $dataGrid[$j]['langsung_tak_tetap']=  $c['b_langsung_tak_tetap'];
                    $dataGrid[$j]['tak_langsung_tetap']=  $c['b_tak_langsung_tetap'];
                    $dataGrid[$j]['tak_langsung_tak_tetap']=  $c['b_tak_langsung_tak_tetap'];
                    /**
                     * total
                     */ 
                    $total_b_langsung_tetap += $dataGrid[$j]['langsung_tetap'];
                    $total_b_langsung_tak_tetap += $dataGrid[$j]['langsung_tak_tetap'];
                    $total_b_tak_langsung_tetap += $dataGrid[$j]['tak_langsung_tetap'];
                    $total_b_tak_langsung_tak_tetap += $dataGrid[$j]['tak_langsung_tak_tetap'];
                    /**
                    * end
                    */
                    $dataGrid[$j]['f_langsung_tetap'] = 
                                            number_format($c['b_langsung_tetap'],0,',','.');
                    $dataGrid[$j]['f_langsung_tak_tetap']=  
                                            number_format($c['b_langsung_tak_tetap'],0,',','.');
                    $dataGrid[$j]['f_tak_langsung_tetap']=  
                                            number_format($c['b_tak_langsung_tetap'],0,',','.');
                    $dataGrid[$j]['f_tak_langsung_tak_tetap']=  
                                            number_format($c['b_tak_langsung_tak_tetap'],0,',','.');
                                            
			     	$this->mrTemplate->AddVars('data_item', $dataGrid[$j], 'DATA_');
				    $this->mrTemplate->parseTemplate('data_item', 'a');
                    
			}
            
            $this->mrTemplate->AddVar('data_grid', 'TOTAL_B_LANGSUNG_TETAP', 
                                                $total_b_langsung_tetap);
            $this->mrTemplate->AddVar('data_grid', 'TOTAL_B_LANGSUNG_TAK_TETAP',
                                                $total_b_langsung_tak_tetap);
            $this->mrTemplate->AddVar('data_grid', 'TOTAL_B_TAK_LANGSUNG_TETAP',
                                                $total_b_tak_langsung_tetap);
            $this->mrTemplate->AddVar('data_grid', 'TOTAL_B_TAK_LANGSUNG_TAK_TETAP',
                                                $total_b_tak_langsung_tak_tetap);
            
            $this->mrTemplate->AddVar('data_grid', 'F_TOTAL_B_LANGSUNG_TETAP', 
                        number_format($total_b_langsung_tetap,0,',','.'));
            $this->mrTemplate->AddVar('data_grid', 'F_TOTAL_B_LANGSUNG_TAK_TETAP', 
                        number_format($total_b_langsung_tak_tetap,0,',','.'));
            $this->mrTemplate->AddVar('data_grid', 'F_TOTAL_B_TAK_LANGSUNG_TETAP', 
                        number_format($total_b_tak_langsung_tetap,0,',','.'));
            $this->mrTemplate->AddVar('data_grid', 'F_TOTAL_B_TAK_LANGSUNG_TAK_TETAP', 
                        number_format($total_b_tak_langsung_tak_tetap,0,',','.'));

		}        
	}
}