<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/copy_program_kegiatan/business/CopyProgramKegiatan.class.php';

class ViewPopupDetailProgram extends HtmlResponse 
{
    protected $mCopyProgram;
    
    public function __construct()
    {
        $this->mCopyProgram = new CopyProgramKegiatan;    
    }
    
    public function TemplateModule() 
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
            'module/copy_program_kegiatan/template');
        $this->SetTemplateFile('view_popup_detail_program.html');
    }
   
    public function TemplateBase() 
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }
   
    public function ProcessRequest() 
    {
        if(isset($_GET['programId']))
		  $programId = $_GET['programId'];
          
        $totalData = $this->mCopyProgram->GetCountProgramKegiatanById($programId);

        $itemViewed = 10;
        $currPage = 1;
        $startRec = 0 ;
        if(isset($_GET['page'])) {
            $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
            $startRec =($currPage-1) * $itemViewed;
        }
		$dataProgram = $this->mCopyProgram->GetProgramKegiatanById($programId,$startRec,$itemViewed);
        $dataProgramDetail = $this->mCopyProgram->GetProgramDetail($programId);
		//done
        $url = Dispatcher::Instance()->GetUrl(
                    Dispatcher::Instance()->mModule, 
                    Dispatcher::Instance()->mSubModule, 
                    Dispatcher::Instance()->mAction, 
                    Dispatcher::Instance()->mType
                    ). 
                    "&programId=$programId";
        $dest = "popup-subcontent";	   
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
                                                    $currPage, 
                                                    $dest), 
                                            Messenger::CurrentRequest);       
        
		$return['programId'] = $programId;
        $return['data_program'] = $dataProgram;
        $return['data_program_detail'] = $dataProgramDetail;
        $return['startRec'] = $startRec+1;
        return $return;
    }
    
    public function ParseTemplate($data = NULL) 
    {      
        $data_program_detail = $data['data_program_detail'];
        $this->mrTemplate->AddVar('content', 'THANGGARAN', $data_program_detail['thAnggaran']);
        $this->mrTemplate->AddVar('content', 'KODEPROGRAM', $data_program_detail['kodeProgram']);
        $this->mrTemplate->AddVar('content', 'NAMAPROGRAM', $data_program_detail['namaProgram']);
        
		if(empty($data['data_program'])){
			$this->mrTemplate->AddVar('data_program', 'IS_DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_program', 'IS_DATA_EMPTY', 'NO');
            $data_program = $data['data_program'];
                /**
                 * inisialisasi
                 */
                 $kodeProgram = '';
                 $kodeKegiatan ='';
                 $kodeSubKegiatan = '';
                 $kodeKomponen = '';
                 $x = 0;
                 $this->mrTemplate->AddVar('content', 'FIRST_NUMBER',$x );
                /**
                 * end
                 */
            for($i = 0 ;$i < sizeof($data_program);$i++){
                    //$data_program[$i]['nomor'] = $x;
                    //$this->mrTemplate->AddVars('data_program_item', $data_program[$i], '');
			        //$this->mrTemplate->parseTemplate('data_program_item', 'a');
                    //$x++;
                /**
                if($kodeProgram != $data_program[$i]['kodeProgram']){
                    $data_pk[$i]['nomor'] = $x;
                    $data_pk[$i]['kode'] ='<b>'. $data_program[$i]['kodeProgram'].'</b>';
                    $data_pk[$i]['nama'] ='<b>'. $data_program[$i]['namaProgram'].'</b>';
                   	
                    $this->mrTemplate->AddVars('data_program_item', $data_pk[$i], '');
			        $this->mrTemplate->parseTemplate('data_program_item', 'a');
                    $kodeProgram = $data_program[$i]['kodeProgram'];
                    $x++;
                    
                }
                */
                if(($kodeKegiatan != $data_program[$i]['kodeKegiatan']) && 
                        (!empty($data_program[$i]['kodeKegiatan']))){
                    $data_pk[$i]['nomor'] = $data['startRec'] + $x;                            
                    $data_pk[$i]['kode'] = '<b>'. $data_program[$i]['kodeKegiatan'].'</b>';
                    $data_pk[$i]['nama'] = '<b>'.$data_program[$i]['namaKegiatan'].'</b>';
                    $data_pk[$i]['level'] = GTFWConfiguration::GetValue('language','kegiatan');
                   	$this->mrTemplate->AddVars('data_program_item', $data_pk[$i], '');
			        $this->mrTemplate->parseTemplate('data_program_item', 'a');
                    $kodeKegiatan = $data_program[$i]['kodeKegiatan'];
                    $x++;
                }
                if(($kodeSubKegiatan != $data_program[$i]['kodeSubKegiatan']) && 
                       !empty($data_program[$i]['kodeSubKegiatan']) ){
                    $data_pk[$i]['kode'] = $data_program[$i]['kodeSubKegiatan'].'</i>';
                    $data_pk[$i]['nama'] = '<i>'.$data_program[$i]['namaSubKegiatan'].'</i>';
                    $data_pk[$i]['nomor'] = '';
                    $data_pk[$i]['level'] = GTFWConfiguration::GetValue('language','sub_kegiatan');
                   	$this->mrTemplate->AddVars('data_program_item', $data_pk[$i], '');
			        $this->mrTemplate->parseTemplate('data_program_item', 'a');
                    $kodeSubKegiatan = $data_program[$i]['kodeSubKegiatan'];                        
                }        
                if(($kodeKomponen != $data_program[$i]['kodeKomponen']) && 
                    !empty($data_program[$i]['kodeKomponen'])){
                    $data_pk[$i]['kode'] = $data_program[$i]['kodeKomponen'];
                    $data_pk[$i]['nama'] = $data_program[$i]['namaKomponen'];
                    $data_pk[$i]['nomor'] = '';
                    $data_pk[$i]['level'] =  GTFWConfiguration::GetValue('language','komponen');
                   	$this->mrTemplate->AddVars('data_program_item', $data_pk[$i], '');
			        $this->mrTemplate->parseTemplate('data_program_item', 'a');
                }         
                
            }
            $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $x -1);
		} 

    } 
}