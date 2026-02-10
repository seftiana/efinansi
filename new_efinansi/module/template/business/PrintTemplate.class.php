<?php
/*
	@ClassName : PrintTemplate
	@Copyright : PT Gamatechno Indonesia
	@Analyzed By : Nanang Ruswianto <nanang@gamatechno.com>
	@Author By : Dyan Galih <galih@gamatechno.com>
	@Version : 0.1
	@StartDate : 2009-12-29
	@LastUpdate : 2009-12-29 
	@Description : Class for get dinamic template file 
*/

class PrintTemplate extends Database
{
	protected $mSqlFile;

	function __construct ($connectionNumber=0)
	{
		$this->mSqlFile = 'module/template/business/print_template.sql.php';
		parent::__construct($connectionNumber);
	}
	
	public function GetTemplateName($code){
		$result = $this->Open($this->mSqlQueries['get_template_name'], array($code));
		return $result[0]['templateFile'];
	}
	
}
?>
