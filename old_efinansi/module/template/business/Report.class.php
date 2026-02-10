<?php

class Report extends Database {

   function Report($connectionNumber = 0) {
      parent::Database($connectionNumber);
      $this->LoadSql('module/template/business/Report.sql.php');
   }
   
   function GetQuery() {
      return $this->GetAllDataAsArray($this->mSqlQueries['get_query'], array());
   }

   function GetQueryById($id) {
      $result = $this->GetAllDataAsArray($this->mSqlQueries['get_query_by_id'], array($id));
      return $result[0];
   }

   function ShowTables() {
      return $this->GetAllDataAsArray($this->mSqlQueries['show_tables'], array());
   }

   function ShowColumsTables($table) {
      return $this->GetAllDataAsArray($this->mSqlQueries['show_colums_tables'], array($table));
   }

   function RunQuery($query, $param) {
      return $this->GetAllDataAsArray($query, $param);
   }

//=======

   function GetTable() {
      return $this->GetAllDataAsArray($this->mSqlQueries['get_table'], array());
   }

   function GetTableById($id) {
      $result = $this->GetAllDataAsArray($this->mSqlQueries['get_table_by_id'], array($id));
      return $result[0];
   }

//====
   
   function HeaderTable($array, $hidden=NULL) {
      //print_r($array);
      $GLOBALS['jum_kolom'] = count($array[0]);
      
      if (isset($array[0][0])) {
         $data = '<table class="table-common" border="1" cellpadding="0" cellspacing="0">';
         for ($i=0;$i<sizeof($array);$i++) {
            $data .= '<tr align="center" bgcolor="#CCCCCC">';
            for ($j=0;$j<sizeof($array[$i]);$j++) {
               if ($array[$i][$j]!=$array[$i-1][$j]) {
                  $a = $b = 0;
                  $row = $col = '';
                  if ($array[$i][$j]==$array[$i+1][$j] and $i!=sizeof($array)-1) {
                     for ($k=0;$k<sizeof($array);$k++) {
                        if ($array[$i][$j]==$array[$i+$k][$j]) $a++;
                        else break;
                     }
                     $row = ' rowspan="'.$a.'"';
                  }
                  if ($array[$i][$j]==$array[$i][$j+1] and $j!=sizeof($array[$i])-1) {
                     for ($k=0;$k<sizeof($array[$i])-$j;$k++) {
                        if ($array[$i][$j]==$array[$i][$j+$k]) $b++;
                        else break;
                     }
                     $col = ' colspan="'.$b.'"';
                  }
                  if($hidden[$j] != 'ya')
                     $data .='<th'.$row.$col.'>'.$array[$i][$j].'</th>';
                  if ($b!=0) $j += ($b-1);
               }
            }
            $data .= '</tr>';
         }
      } else $data = 'Header Tidak Valid';
      return $data;      
   }

   function HeaderTableForRunQuery($array) {
      if (isset($array[0])) {
         if($_SESSION['user_level_id'] != 1 && $array[0]['FAKULTAS'] != '')
            unset($array[0]['FAKULTAS']);

         $kolom = array_keys($array[0]);
         $header = '<table class="table-common">
           <tr align="center">';
         for ($i=0;$i<sizeof($kolom);$i++) {
            $header .= '<th> '.$kolom[$i].' </th>';
         }
         $header .= '</tr>';
      }
      return $header;
   }

   function DataTable($array, $align=NULL, $width=NULL, $format=NULL, $separator=NULL, $rowspan=NULL, $link=NULL, $url_link=NULL, $hidden=NULL) {      
      if (isset($array[0])) {
         $kolom = array_keys($array[0]); 
         $GLOBALS['jum_kolom'] = count(array_keys($array[0]))-count($hidden);

         for ($i=0;$i<sizeof($array);$i++) {
            if ($i%2==0) $class = 'table-common-even'; else $class = '';
            $data .= '<tr class="'.$class.'">';
            for ($j=0;$j<sizeof($kolom);$j++) {
               $nilai = $array[$i][$kolom[$j]];
               if ($format[$j]=='uang') $nilai = $this->FormatCurrency($array[$i][$kolom[$j]]);
               elseif ($separator[$j]=='ya') $nilai = number_format($array[$i][$kolom[$j]], 0, ',', '.');

               //add for link
               if($link[$j] == 'ya'):
                  $tag_link_open = '<a href="'.$url_link[$i][$j].'">';
                  $tag_link_close = '</a>';
               else:
                  $tag_link_open = '';
                  $tag_link_close = '';
               endif;
               
               if ($rowspan[$j]=='ya') {
                  if ($array[$i][$kolom[$j]]!=$array[$i-1][$kolom[$j]]) {
                     $a = 0;
                     for ($k=0;$k<sizeof($array);$k++) {
                        if ($array[$i][$kolom[$j]]==$array[$k][$kolom[$j]]) 
                           $a++;
                        //else break;
                       // echo $array[$i][$kolom[$j]].'=='.$array[$k][$kolom[$j]];
                     }//'" width="'.$width[$j].
                     $data .= '<td rowspan="'.$a.'" align="'.$align[$j].'">'.$tag_link_open.$nilai.$tag_link_close.'</td>';
                  }
               } 
               else
                  if($hidden[$j] != 'ya')//width="'.$width[$j].'" 
                     $data .= '<td align="'.$align[$j].'">'.$tag_link_open.$nilai.$tag_link_close.'</td>';      
            }
            $data .= '</tr>';
         }
//         $data .=  '</tr>';
      } else {         
         //print $GLOBALS['jum_kolom'];
         $data = '<tr align="center"><td colspan="'.$GLOBALS['jum_kolom'].'"><strong>Data tidak ditemukan</strong></td></tr>';
      }
      return $data;
   }

   function FooterTable($array, $colspan=NULL, $separator=NULL, $format=NULL, $hidden=NULL) {
      if (isset($array)) {
         $kolom = array_keys($array); 
         $data .= '<tr align="right" bgcolor="#CCCCCC">';
         for ($i=0;$i<sizeof($array);$i++) {
            $col = '';
            $nilai = $array[$kolom[$i]];
            if ($colspan[$i]!='' or $colspan[$i]!=0) $col = ' colspan="'.$colspan[$i].'"';
            if ($separator[$i]=='ya') $nilai = $this->FormatSeparator($array[$kolom[$i]]);
            if ($format[$i]=='uang') $nilai = $this->FormatCurrency($array[$kolom[$i]]);
            if($hidden[$i] != 'ya') $data .='<th'.$col.'>'.$nilai.'</th>';
         }
         $data .= '</tr>';
      } else $data = 'Footer tidak valid';
      return $data;
   }

   function CloseTable() {
      $footer = '</table>';
      return $footer;
   }


   function SetGrafikLine() {
      $ydata  = array(11,3, 8,12,5 ,1,9, 13,5,7 );
      
      // Create the graph. These two calls are always required
      $graph  = new Graph(350, 250,"auto");    
      $graph->SetScale( "textlin");
      
      // Create the linear plot
      $lineplot =new LinePlot($ydata);
      $lineplot ->SetColor("blue");
      
      // Add the plot to the graph
      $graph->Add( $lineplot);
      
      // Display the graph
      $graph->Stroke();
    }

   function SetGrafik($type, $panjang, $lebar, $pieData, $pieLegend) {
      if ($type == "pie") {
         $graph = new PieGraph($panjang,$lebar,"auto");
         $graph->SetShadow();
         
         $p1 = new PiePlot3D($pieData);
         $p1->SetLegends($pieLegend);
         $p1->SetTheme("earth");
         $p1->SetCenter(0.28);
         $p1->SetSize(0.5);
         $p1->SetHeight(2);
         $p1->SetAngle(45);
         $p1->Explode(array(0,20,0,30));
         
         $graph->legend->Pos(0.02,0.02);
         $graph->Add($p1);
      } elseif ($gtype == "garis") {
      
      } else {
         $graph = new Graph($panjang,$lebar,"auto");
         
         $markType = array(MARK_SQUARE, MARK_UTRIANGLE, MARK_DTRIANGLE, MARK_DIAMOND, MARK_FILLEDCIRCLE, MARK_CROSS, MARK_STAR, MARK_X);
         $colorType = array('red', 'green', 'blue', 'orange', 'yellow', 'black', "cyan", "magenta");
         
         $len = sizeof($data);
         $loopMark = 0;
         $loopColor = 0;
         for ($i=0; $i<$len; $i++) {
            $yData= array($data[$i]['jan'], $data[$i]['feb'], $data[$i]['mar'], $data[$i]['apr'],$data[$i]['mei'],
               $data[$i]['jun'],$data[$i]['jul'],$data[$i]['agus'],$data[$i]['sept'],$data[$i]['okt'],
               $data[$i]['nov'], $data[$i]['des']);
            $p[$i] = new LinePlot($yData);
            $p[$i]->mark->SetType($markType[$loopMark]);
            $p[$i]->mark->SetFillColor($colorType[$loopColor]);
            $p[$i]->mark->SetWidth(4);
            $p[$i]->SetColor($colorType[$loopColor]);
            $strlegend = str_replace(array('PERIJINAN', 'PERIZINAN', 'IZIN', 'IJIN'), '', strtoupper($data[$i][$keys[0]]));
            $p[$i]->SetLegend($strlegend);
            $p[$i]->SetCenter();
            $graph->Add($p[$i]);
            $loopColor++;
            if ($loopColor == 8){
               $loopColor = 0;
               $loopMark++;
            }
         }
         $graph->SetScale("textlin");
         $graph->xaxis->title->Set("Bulan");
         $graph->xaxis->SetTickLabels(array('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agus', 'Sept', 'Okt', 'Nov', 'Des'));
         $graph->yaxis->title->Set("Total");
         $graph ->legend->Pos( 0.01,0.4,"right" ,"center");     
         $graph->img->SetMargin(40,200,20,40);
      }
      $graph->legend->SetFont(FF_FONT1,FS_NORMAL, '1');
      $graph->Stroke();
   }
   
//=======

   function GetLayout() {
      return $this->GetAllDataAsArray($this->mSqlQueries['get_layout'], array());
   }

   function GetLayoutById($id) {
      $result = $this->GetAllDataAsArray($this->mSqlQueries['get_layout_by_id'], array($id));
      return $result[0];
   }

   function GetBridgeById($id) {
      $result = $this->GetAllDataAsArray($this->mSqlQueries['get_bridge_by_id'], array($id));
      return $result[0];
   }

   function GetSubMenu() {
      return $this->GetAllDataAsArray($this->mSqlQueries['get_sub_menu'], array());
   }

   function GetSubMenuById($id) {
      $result = $this->GetAllDataAsArray($this->mSqlQueries['get_sub_menu_by_id'], array($id));
      return $result[0];
   }

	function FormatCurrency($nilai) {
		$formatted = sprintf("%01.2f", $nilai);
		$koma	   = str_replace(".", ",", $formatted);

		$nilai_x  = explode(",", $koma);
		$dpn_koma = $nilai_x[0];
		if ($dpn_koma < 0) {
			$sign	  = '-';
			$dpn_koma = substr($dpn_koma, 1);
		}
		else {
			$sign = '';
		}

		$pj_nilai = strlen($dpn_koma);

		if ($pj_nilai > 3) {
			$pj_depan_koma = $pj_nilai;

			$blk_koma = $nilai_x[1];

			$pj_awal_depan_koma  = $pj_depan_koma % 3;
			$pj_akhir_depan_koma = $pj_depan_koma - $pj_awal_depan_koma;

			$awal_depan_koma  = substr($dpn_koma, 0, $pj_awal_depan_koma);
			$akhir_depan_koma = substr($dpn_koma, $pj_awal_depan_koma, $pj_akhir_depan_koma);

			if ($awal_depan_koma <> '') {
				$bil .= $awal_depan_koma . ".";
			}
			else {
				$bil .= '';
			}

			$jml_ttk_akhir_depan_koma = $pj_akhir_depan_koma / 3;

			for ($i = 0; $i < $jml_ttk_akhir_depan_koma; $i++) {
				$awal = $i * 3;
				$akhir_depan_koma_ke[$i] = substr($akhir_depan_koma, $awal, 3);
			}

			for ($i = 0; $i < $jml_ttk_akhir_depan_koma; $i++) {
				if ($i <> ($jml_ttk_akhir_depan_koma - 1)) {
					$bil .= $akhir_depan_koma_ke[$i] . ".";
				}
				else {
					$bil .= $akhir_depan_koma_ke[$i];
				}
			}

			$bil .= "," . $blk_koma;
		}
		else {
			$bil = $koma;
		}

		$bil = $sign . $bil;

		return $bil;
	}
	
   function FormatSeparator($nilai) {
      $nilai = $this->FormatCurrency($nilai);
      return substr($nilai, 0, strlen($nilai)-3); 
   }
   
   function GetGraphicByIdLayout($id) {
      $result = $this->GetAllDataAsArray($this->mSqlQueries['get_graphic_by_id_layout'], array($id));
      return $result[0];
   }



//added by choirul to form array header
//header sia
   function FormatHeaderTableSiaWithProdi($arrField, $jml_baris = 1){
      if($_SESSION['user_level_id'] != '1'):
         unset($arrField[0]);
         $j = 0;
         for($i=1;$i<=count($arrField);$i++):
            $arrData[$j] = $arrField[$i];
            $j++;
         endfor;
         $arrHeader[0] = $arrData;
      
      else:
         for($i=$awal;$i<$jml_baris;$i++):
            $arrHeader[0] = $arrField;
         endfor;
      endif;
      
      return $arrHeader;
   }

   function formatDataTableSiaWithProdi($array, $col){   
      for($i=0; $i<count($array); $i++):
         if($_SESSION['user_level_id'] != '1'):
            unset($array[$i]['FAKULTAS']);
            $k = 0;
            for($j=1;$j<count($col);$j++):
               $arrData[$i][$k] = $array[$i][$col[$j]];
               $k++;
            endfor;
            
         else:
            
            for($j=0;$j<count($col);$j++):
               if(is_null($array[$i][$col[$j]]) || trim($array[$i][$col[$j]]) == '')
                  $arrData[$i][$j] = '-';
               else
                  $arrData[$i][$j] = $array[$i][$col[$j]];
            endfor;
         endif;
   
      endfor; 
      
      return $arrData;
   }

   function GetParamById($id) {
      return $this->GetAllDataAsArray($this->mSqlQueries['get_param_by_id'], array($id));
   }
   
   function SetDropdown($label, $nama, $value, $nilai, $select) {
      $url = $_SERVER['REQUEST_URI'];
      $data = '<a><form method="POST" ACTION="'.$url.'">'.$label.' <select onChange="this.form.submit()" name="'.$nama.'">';
      for ($i=0;$i<sizeof($value);$i++) {
         if ($value[$i]==$select) $selected = 'selected'; else $selected = '';
         $data .= '<option value="'.$value[$i].'" '.$selected.'>'.$nilai[$i];
      }
      $data .= '</select></form></a>';
      return $data;
   }
   
}

?>
