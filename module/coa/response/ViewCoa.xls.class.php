<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/coa/business/Coa.class.php';

class ViewCoa extends XlsResponse
{
   var $mWorksheets = array('COA');
   
   function GetFileName() {
      // name it whatever you want
      return 'COA_'.date('Y-m-d_H.i').'.xls';
   }

   function ProcessRequest() {
		$Obj = new Coa;
      
      // inisialisasi dataGrid
      $i = 0;
      $data = $Obj->GetListCoaExcel();
      if (empty($data)) {$this->mWorksheets['COA']->write(0, 0, 'Data kosong'); return;}
      /**
	    $dataGrid = new SimpleXMLElement("<root/>");
	   */
		$dataGrid = $data; /**
      while (!empty($data) AND $i < 20)
      {
         $tmp = array();
         foreach ($data as $value)
         {
            if ($value['coaParentAkun'] AND !($node = $dataGrid->xPath("//coaId".$value['coaParentAkun'])))
               {$tmp[] = $value; continue;}

            if (!$node) $node[0] = &$dataGrid;
            $child = $node[0]->addChild("coaId".$value['coaId']);
            $child->addAttribute('coaKodeAkun', $value['coaKodeAkun']);
            $child->addAttribute('coaNamaAkun', $value['coaNamaAkun']);
            $child->addAttribute('coaLevelAkun', $value['coaLevelAkun']);
            $child->addAttribute('coaIsDebetPositif', $value['coaIsDebetPositif']);
         }
         $data = $tmp; $i++;
      }
      */
      $cTitle = GTFWConfiguration::GetValue('organization', 'company_name');
      $cSubTitle = GTFWConfiguration::GetValue('organization', 'application_name');
      $cTableTitle = 'Chart of Account';
      // ---------
		
      // Create format for each style
      $fTitle = $this->mrWorkbook->add_format();
      $fTitle->set_border(0);
      $fTitle->set_bold();
      $fTitle->set_size(14);
      $fTitle->set_align('center');
      
      $fSubTitle = $this->mrWorkbook->add_format();
      $fSubTitle->set_border(0);
      $fSubTitle->set_bold();
      $fSubTitle->set_size(12);
      $fSubTitle->set_align('center');
      
      $fTableTitle = $this->mrWorkbook->add_format();
      $fTableTitle->set_border(0);
      $fTableTitle->set_bold();
      $fTableTitle->set_size(11);
      
      $fTableHeader = $this->mrWorkbook->add_format();
      $fTableHeader->set_border(1);
      $fTableHeader->set_bold();
      $fTableHeader->set_size(10);
      $fTableHeader->set_align('center');
      
      $fTableCell = $this->mrWorkbook->add_format();
      $fTableCell->set_border(1);
      $fTableCell->set_bold(0);
      $fTableCell->set_size(10);
      $fTableCell->set_align('left');
      
      $fTableCellCenter = $this->mrWorkbook->add_format();
      $fTableCellCenter->set_border(1);
      $fTableCellCenter->set_bold(0);
      $fTableCellCenter->set_size(10);
      $fTableCellCenter->set_align('center');
      // ---------
      
      // Create layout
      $row = 0; $col = 0;
      
      $this->mWorksheets['COA']->write($row, $col, $cTitle, $fTitle);
      $this->mWorksheets['COA']->merge_cells($row, $col, $row, $col + 2);
      $this->mWorksheets['COA']->write_blank($row, $col + 1, $fTitle);
      $this->mWorksheets['COA']->write_blank($row, $col + 2, $fTitle);
      $row++; $col = 0;
      
      $this->mWorksheets['COA']->write($row, $col, $cSubTitle, $fSubTitle);
      $this->mWorksheets['COA']->merge_cells($row, $col, $row, $col + 2);
      $this->mWorksheets['COA']->write_blank($row, $col + 1, $fSubTitle);
      $this->mWorksheets['COA']->write_blank($row, $col + 2, $fSubTitle);
      $row++; $col = 0;
      $row++; $col = 0;
      
      $this->mWorksheets['COA']->write($row, $col, $cTableTitle, $fTableTitle);
      $this->mWorksheets['COA']->merge_cells($row, $col, $row, $col + 2);
      $this->mWorksheets['COA']->write_blank($row, $col + 1, $fTitle);
      $this->mWorksheets['COA']->write_blank($row, $col + 2, $fTitle);
      $row++; $col = 0;
      
      $this->mWorksheets['COA']->write($row, $col++, 'Kode Akun', $fTableHeader);
      $this->mWorksheets['COA']->write($row, $col++, 'Nama Akun', $fTableHeader);
      $this->mWorksheets['COA']->write($row, $col++, 'Saldo Normal', $fTableHeader);
      $row++; $col = 0;
      // ---------
      
      // dump dataGrid
      /**
	   $dataGrid = $this->CoaXmlAsArray($dataGrid, '.');
	   */
      foreach ($dataGrid as $value)
      {      	
         $this->mWorksheets['COA']->write($row, $col++, $value['coaKodeAkun'], $fTableCell);
         $this->mWorksheets['COA']->write($row, $col++, $value['coaNamaAkun'], $fTableCell);
         $this->mWorksheets['COA']->write($row, $col++, $value['coaIsDebetPositif'] ? 'Debet' : 'Kredit', $fTableCellCenter);
         $row++; $col = 0;
      }
      // ---------
   }
   /**
   function CoaXmlAsArray ($data, $sep = ' - ')
   {
      static $coaKodeAkun = false;
      $return = array();
      
      if ($coaKodeAkun !== false)
      {
         foreach ($data->attributes() as $name => $value)
            $return[0][$name] = (string) $value;
         $coaKodeAkun[] = $return[0]['coaKodeAkun'];
      }
      else $coaKodeAkun = array();
      
      foreach ($data as $child) $return = array_merge($return, $this->CoaXmlAsArray($child, $sep));
      
      if (empty($coaKodeAkun)) $coaKodeAkun = false;
      else array_pop($coaKodeAkun);
      
      return $return;
   }
   */
}
?>
