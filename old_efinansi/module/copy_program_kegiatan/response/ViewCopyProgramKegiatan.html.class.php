<?php

/**
 * class ViewCopyProgram
 * @package copy_program
 * @subpackage response
 * @todo untuk menampilkan tampilan daftar data laporan RDP
 * @since juni 2012
 * @copyright 2012 Gamatechno Indonesia
 * @author noor hadi <noor.hadi@gamatechno.com>
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/copy_program_kegiatan/business/CopyProgramKegiatan.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewCopyProgramKegiatan extends HtmlResponse
{

	protected $mCopyProgram;
    protected $mUserUnitKerja;

	protected $data;
    
	public function __construct()
	{
		$this->mCopyProgram = new CopyProgramKegiatan();
        $this->mUserUnitKerja = new UserUnitKerja();
	}
	
    public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
				'module/copy_program_kegiatan/template');
		$this->SetTemplateFile('view_copy_program_kegiatan.html');
	}
    
	public function ProcessRequest()
	{
	   $_POST = $_POST->AsArray();
       
       if(isset($_POST['btncalculate'])){
            $th_anggar_awal_selected = $_POST['th_anggar_awal'];
            $th_anggar_akhir_selected = $_POST['th_anggar_akhir'];
        } elseif(isset($_GET['cari'])){
            $th_anggar_awal_selected = Dispatcher::Instance()->Decrypt($_GET['th_anggar_awal']);
            $th_anggar_akhir_selected = Dispatcher::Instance()->Decrypt($_GET['th_anggar_akhir']);
        } else{
            if(empty($th_anggar_awal_selected)){
                $th_anggar_awal_selected = $this->mCopyProgram->GetTahunAnggaranAktif();
            }
            if(empty($th_anggar_akhir_selected)){
                $th_anggar_akhir_selected = $this->mCopyProgram->GetTahunAnggaranAktif();
            }
        }           
       
       
        $msg = Messenger::Instance()->Receive(__FILE__);
		$return['pesan']	= $msg[0][1];
		$return['css'] 		= $msg[0][2];
       /**
        * membuat combobox tahun anggaran
        */
        
        $arrTahunAnggaran = $this->mCopyProgram->GetDataTahunAnggaran();
        Messenger::Instance()->SendToComponent(
                                                'combobox', 
                                                'Combobox', 
                                                'view', 
                                                'html', 
                                                'th_anggar_awal', 
                                                array(
                                                        'th_anggar_awal', 
                                                        $arrTahunAnggaran, 
                                                        $th_anggar_awal_selected, 
                                                        '', 
                                                        ' style="width:150px;" '
                                                    ), 
                                                Messenger::CurrentRequest);
        
        
        //$arrTahunAnggaran = $this->mCopyProgram->GetDataTahunAnggaran();
        Messenger::Instance()->SendToComponent(
                                                'combobox', 
                                                'Combobox', 
                                                'view', 
                                                'html', 
                                                'th_anggar_akhir', 
                                                array(
                                                        'th_anggar_akhir', 
                                                        $arrTahunAnggaran, 
                                                        $th_anggar_akhir_selected, 
                                                        '', 
                                                        ' style="width:150px;" '
                                                    ), 
                                                Messenger::CurrentRequest);                                                
        
       /**
        * end
        */
  		$totalData = $this->mCopyProgram->GetCountProgramKegiatan(
                                                            $th_anggar_awal_selected,
                                                            $th_anggar_akhir_selected);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
        
        $url = Dispatcher::Instance()->GetUrl(
	  										Dispatcher::Instance()->mModule, 
				  							Dispatcher::Instance()->mSubModule, 
				  							Dispatcher::Instance()->mAction, 	
				  							Dispatcher::Instance()->mType . 
		  									'&th_anggar_awal=' . 
                                            Dispatcher::Instance()->Encrypt($th_anggar_awal_selected) . 
										  	'&th_anggar_akhir=' . 
                                            Dispatcher::Instance()->Encrypt($th_anggar_akhir_selected) .
										  	'&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent(
												'paging', 
												'Paging', 
												'view', 
												'html', 
												'paging_top', 
												array(
														$itemViewed,
														$totalData, 
														$url, 
														$currPage), 
												Messenger::CurrentRequest);
        
        $return['data_program'] = $this->mCopyProgram->GetProgramKegiatan(
                                                                    $th_anggar_awal_selected,
                                                                    $th_anggar_akhir_selected,
                                                                    $startRec,
                                                                    $itemViewed);
        $return['th_awal'] = $th_anggar_awal_selected;
        $return['th_akhir'] = $th_anggar_akhir_selected;
        $return['startRec'] = $startRec;
		return $return;
	}
	public function ParseTemplate($data = NULL)
	{
	    if($data['pesan']) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['pesan']);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $data['css']);
		}
        $data_program = $data['data_program'];
        $url_action	= Dispatcher::Instance()->GetUrl('copy_program_kegiatan', 
                                                         'copyProgramKegiatan', 
														 'view', 
                                                         'html');
                                                         
        $url_action_copy = Dispatcher::Instance()->GetUrl('copy_program_kegiatan', 
                                                         'copyProgramKegiatan', 
														 'do', 
                                                         'html');
        
        $this->mrTemplate->AddVar('content', 'URL_ACTION', $url_action);
        $this->mrTemplate->AddVar('content', 'URL_ACTION_COPY', $url_action_copy);

        $this->mrTemplate->AddVar('content', 'TH_SUMBER', $data['th_awal']);
        $this->mrTemplate->AddVar('content', 'TH_TUJUAN', $data['th_akhir']);
        
		if(empty($data['data_program'])){
			$this->mrTemplate->AddVar('data_program', 'IS_DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_program', 'IS_DATA_EMPTY', 'NO');
            
                
                /**
                 * inisialisasi
                 */
                 $kodeProgram = '';
                 $kodeKegiatan ='';
                 $kodeSubKegiatan = '';
                 $kodeKomponen = '';
                 $x = 1;
                 $this->mrTemplate->AddVar('content', 'FIRST_NUMBER',$x );
                /**
                 * end
                 */
            for($i = 0 ;$i < sizeof($data_program);$i++){
                /**
                if($kodeProgram != $data_program[$i]['kodeProgram']){
                    $data_pk[$i]['nomor'] = $x;
                    $data_pk[$i]['kode'] ='<b>'. $data_program[$i]['kodeProgram'].'</b>';
                    $data_pk[$i]['nama'] ='<b>'. $data_program[$i]['namaProgram'].'</b>';
                    $data_pk[$i]['cek_box']='<input type="checkbox" name="kode[]" id="program_id_'.
                                            $x.'" value="'.$data_program[$i]['kodeProgram'].'"/>';
                   	
                    $data_pk[$i]['url_detail']= Dispatcher::Instance()->GetUrl(
                                                         'copy_program_kegiatan', 
                                                         'popupDetailProgram', 
														 'view', 
                                                         'html') . 
                                                         '&programId='.$data_program[$i]['idProgram'];
                    $this->mrTemplate->AddVars('data_program_item', $data_pk[$i], '');
			        $this->mrTemplate->parseTemplate('data_program_item', 'a');
                    $kodeProgram = $data_program[$i]['kodeProgram'];
                    $x++;
                    
                }
                if(($kodeKegiatan != $data_program[$i]['kodeKegiatan']) && 
                        (!empty($data_program[$i]['kodeKegiatan']))){
                    $data_pk[$i]['kode'] = '<b><i>'. $data_program[$i]['kodeKegiatan'].'</i></b>';
                    $data_pk[$i]['nama'] = '<b><i>'.$data_program[$i]['namaKegiatan'].'</i></b>';
                    $data_pk[$i]['nomor'] = '';
                    $data_pk[$i]['cek_box']='';
                   	$this->mrTemplate->AddVars('data_program_item', $data_pk[$i], '');
			        $this->mrTemplate->parseTemplate('data_program_item', 'a');
                    $kodeKegiatan = $data_program[$i]['kodeKegiatan'];
                }
                if(($kodeSubKegiatan != $data_program[$i]['kodeSubKegiatan']) && 
                       !empty($data_program[$i]['kodeSubKegiatan']) ){
                    $data_pk[$i]['kode'] = $data_program[$i]['kodeSubKegiatan'].'</i>';
                    $data_pk[$i]['nama'] = '<i>'.$data_program[$i]['namaSubKegiatan'].'</i>';
                    $data_pk[$i]['nomor'] = '';
                    $data_pk[$i]['cek_box']='';
                   	$this->mrTemplate->AddVars('data_program_item', $data_pk[$i], '');
			        $this->mrTemplate->parseTemplate('data_program_item', 'a');
                    $kodeSubKegiatan = $data_program[$i]['kodeSubKegiatan'];                        
                }        
                if(($kodeKomponen != $data_program[$i]['kodeKomponen']) && 
                    !empty($data_program[$i]['kodeKomponen'])){
                    $data_pk[$i]['kode'] = $data_program[$i]['kodeKomponen'];
                    $data_pk[$i]['nama'] = $data_program[$i]['namaKomponen'];
                    $data_pk[$i]['nomor'] = '';
                    $data_pk[$i]['cek_box']='';
                   	$this->mrTemplate->AddVars('data_program_item', $data_pk[$i], '');
			        $this->mrTemplate->parseTemplate('data_program_item', 'a');
                }         
                */
                    $data_program[$i]['nomor'] = $data['startRec'] + $x;
                    $data_program[$i]['cek_box']='<input type="checkbox" name="kode[]" id="program_id_'.
                                            $x.'" value="'.$data_program[$i]['kodeProgram'].'"/>';
                    $data_program[$i]['url_detail']= Dispatcher::Instance()->GetUrl(
                                                         'copy_program_kegiatan', 
                                                         'popupDetailProgram', 
														 'view', 
                                                         'html') . 
                                                         '&programId='.$data_program[$i]['idProgram'];
                    $this->mrTemplate->AddVars('data_program_item', $data_program[$i], '');
			        $this->mrTemplate->parseTemplate('data_program_item', 'a');
                    $kodeProgram = $data_program[$i]['kodeProgram'];
                    $x++;
                
            }
            $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $x -1);
		} 
	}
}