<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/lap_program_kegiatan/business/LapProgramKegiatan.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ProcessLapProgramKegiatan
{
    
	protected $msg;
	
	protected $data;
    protected $komp;
	
	protected $moduleName = 'lap_program_kegiatan';
	
	protected $inputModule;
	
	public $LapProgramKegiatan;
	
	public $UserUnitKerja;
    
	public function ProcessLapProgramKegiatan() 
	{ //constructor		
		if (isset($_POST['data'])) 
		if (is_object($_POST['data'])) $this->data = $_POST['data']->AsArray();
		else $this->data = $_POST['data'];
	}
}