<?php
/* class Imghelper
    author : choirul ihwan
    it is not globa use, but just to be used in this modul only
   any one can change the format of workbook n worksheet on purpose
*/

class ImgHelper {
   var $kategori;   
   var $row;
   var $data;
   var $color;  
   var $legend; 
   var $title;
   var $width;
   var $graph;
   var $barplot;
   var $groupBarPlot;
   var $gbplot;
   
   function __construct($value, $kategori, $row = null, $legend = null, $color = null, $judulX=null, $width=null, $height=null){
      //diset apabila untuk fakultas yang diambil dari berbagai sia      
      $this->kategori = $kategori; //dinamis ke kanan
      $this->row = $row;
      $this->value = $value;
      $this->judulX = $judulX;
      
      $this->legend = $legend;     

      $this->width = $width;
      
      $this->height = $height;
      $this->color = array('9697FF', '973367', 'FFFDD4', 'C5FFFE', '5F026A', 'FF7D8A',
                           '0960D3', 'D1C9FF', '01007F', 'FB03F7', 'FFFD08', '01FFFF',
                           '7D0082', '830006', '047F7A', '0202F8', '00CEF6', 'C7FFC8',
                           'FEFC97', '9DC8FD', 'FF98C2', 'C59DFF', 'FCCC9C', '2F67FF',
                           '33CAD5', '9ACB00', 'FFCF00', 'FF9801', '66669A', '96959A',
                           '013463', '2C9E63', '023200', '343201');
      
   }
   
   function createBarGraph(){      
      for($i=0;$i<count($this->value);$i++){         
         for ($j=0;$j<sizeof($this->value[$this->row[$i]]);$j++) {
            for($k=0;$k<count($this->kategori);$k++){
               $jumlah[$i][$k] += $this->value[$this->row[$i]][$j][$this->kategori[$k]];              
            }
         }
      }

      
      for($i=0;$i<count($jumlah);$i++):
         for($k=0;$k<count($this->kategori);$k++){
            $arrResult[$k][] = $jumlah[$i][$k];   
         }
      endfor; 

      $this->drawBarGraph($arrResult);
   }

   function createBarGraphSingleDatabase(){
      for($k=0;$k<count($this->kategori);$k++){
         for($i=0;$i<count($this->value);$i++)
            $arrResult[$k][$i] = $this->value[$i][$this->kategori[$k]];
      }

      $this->drawBarGraph($arrResult);
   }
   
   function drawBarGraph($arrResult){
      if(trim($this->width) == '') $this->width = 1100;
      if(trim($this->height) == '') $this->height = 700;

      $graph = new Graph($this->width, $this->height, "auto");
      $graph->SetScale("textlin");
      $graph->SetShadow();
      $graph->img->SetMargin(50,100,40,150);      
      $graph->yaxis->title->Set("Jumlah");
      $graph->xaxis->title->Set($this->judulX);
      $graph->xaxis->SetTickLabels($this->row);
      $graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
      $graph->xaxis->SetLabelAngle(45);	
      
      $graph->yaxis->scale->SetGrace(20);
      $graph->legend->Pos(0.01, 0.2, "right" ,"center");
      
      foreach($arrResult as $key => $item){
         $barPlot = new BarPlot($item);
         $barPlot->SetFillColor('#'.$this->color[$key]);
         //$barPlot->SetAbsWidth(15); 
         $barPlot->SetLegend($this->legend[$key]);
         $barPlot->value->Show();
         $barPlot->value->SetFormat('%d');
         $barPlot->value->SetAngle(60);         
         $barPlot->value->SetFont(FF_ARIAL,FS_BOLD,8);
         $groupBarPlot[] = $barPlot;
      }
      
      $gbplot = new GroupBarPlot($groupBarPlot);
      
      $graph->Add($gbplot);           
      
      $graph->Stroke();
   }

   
   function createLineGraphSingleDatabase($label, $is_universitas=1){
      
       $color = array('32F8E3', '04FC96', '4AFC04', 'DCFC04', 'FCA204', 'FE5C04', 
         '879C45', '99FF66', 'BBFF44','FC0427', 'FA30DB', 'B030FA','G430FA','ADE2FB');
      $graph = new Graph(1100,700);
      $graph->SetMarginColor('white');
      $graph->SetScale("textlin");
      
      $graph->SetFrame(false);
      $graph->SetMargin(80,250,30,100);
      $graph->yaxis->title->Set("Scala Fakultas");
      $graph->yaxis->HideZeroLabel();
      $graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5');
      $graph->xgrid->Show();
      
      $graph->xaxis->SetTickLabels($label);
      $graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
      $graph->xaxis->SetLabelAngle(45);

      if($is_universitas == 1){
         $jum_baris = sizeof($this->row);
         $graph->SetY2Scale("lin");
      }else
         $jum_baris = sizeof($this->row)-1;

      for ($i=0;$i<=$jum_baris;$i++) {         
            for($k = 0;$k<count($label);$k++){               
               $jum_akhir[$k] += $this->value[$i][$label[$k]]; 
               $jumlah[$i][$k] += $this->value[$i][$label[$k]];
            }            
         
         if(isset($jumlah[$i][0]) and $i<sizeof($this->row)) {
            $p[$i] = new LinePlot($jumlah[$i]);
            $p[$i]->SetColor('#'.$color[$i]);
            $p[$i]->SetWeight(1.5);
            $p[$i]->SetLegend($this->row[$i]);
            //$p[$i]->mark->SetType(MARK_FILLEDCIRCLE);
            //$p[$i]->value-> Show();
            $graph->Add($p[$i]);
         }else {
            $p1 = new LinePlot($jum_akhir);
             if($_SESSION['user_level_id'] == 1)
               $p1->SetLegend('UNIVERSITAS');
            else
               $p1->SetLegend('FAKULTAS');
            $p1->SetColor('black');
            $p1->SetWeight(2);
            $graph->AddY2($p1);
         }
      }
      
      $graph->legend->SetShadow('gray@0.4',5);
      $graph->legend->SetPos(0.01,0.1,'right','top');
      // Output line
      $graph->Stroke();   
   }

   function createLineGraph($label, $is_universitas=1){
       $color = array('32F8E3', '04FC96', '4AFC04', 'DCFC04', 'FCA204', 'FE5C04', 
         '879C45', '99FF66', 'BBFF44','FC0427', 'FA30DB', 'B030FA','G430FA','ADE2FB');
      $graph = new Graph(1100,700);
      $graph->SetMarginColor('white');
      $graph->SetScale("textlin");
      
      $graph->SetFrame(false);
      $graph->SetMargin(80,250,30,100);
      $graph->yaxis->title->Set("Scala Fakultas");
      $graph->yaxis->HideZeroLabel();
      $graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5');
      $graph->xgrid->Show();
      
      if($is_universitas == 1){
         $jum_baris = sizeof($this->row);
         $graph->SetY2Scale("lin");
      }else
         $jum_baris = sizeof($this->row)-1;

      $graph->xaxis->SetTickLabels($label);
      $graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
      $graph->xaxis->SetLabelAngle(45);

      for ($i=0;$i<=$jum_baris;$i++) {
         for ($j=0;$j<sizeof($this->value[$this->row[$i]]);$j++) {
            for($k = 0;$k<count($label);$k++){               
               $jum_akhir[$k] += $this->value[$this->row[$i]][$j][$label[$k]]; 
               $jumlah[$i][$k] += $this->value[$this->row[$i]][$j][$label[$k]];
            }            
         }
                  
         if (isset($this->value[$this->row[$i]][0]) and $i<sizeof($this->row)) {
            $p[$i] = new LinePlot($jumlah[$i]);
            $p[$i]->SetColor('#'.$color[$i]);
            $p[$i]->SetWeight(1.5);
            $p[$i]->SetLegend($this->row[$i]);
            //$p[$i]->mark->SetType(MARK_FILLEDCIRCLE);
            //$p[$i]->value-> Show();
            $graph->Add($p[$i]);
         } else {
            $p1 = new LinePlot($jum_akhir);
            $p1->SetLegend('UNIVERSITAS');
            $p1->SetColor('black');
            $p1->SetWeight(2);
            $graph->AddY2($p1);
         }
      }

      $graph->legend->SetShadow('gray@0.4',5);
      $graph->legend->SetPos(0.01,0.1,'right','top');
      // Output line
      $graph->Stroke();   
   }

   //pie graph dengan index [0][0]
   function createPieGraphArrayIndex(){      
      $k = 0;
      
      for ($i=0;$i<sizeof($this->value);$i++) {
         $arrData[$k]['FAKULTAS'] = $this->value[$i]['FAKULTAS'];
         for($j=0;$j<count($this->kategori);$j++){
            $arrData[$k][$j] = $this->value[$i][$j+1];
         }
         
         
            if($arrData[$k]['FAKULTAS'] == $arrData[$k-1]['FAKULTAS']):
               for($j=0;$j<count($this->kategori);$j++){
                  $arrData[$k][$j] += $arrData[$k-1][$j];
               }
               unset($arrData[$k-1]);
               $arrData[$k-1] = $arrData[$k];
               unset($arrData[$k]);
               $k--;
            endif;
         
            $k++;
      }

      
      if (sizeof($arrData)<3) $pemb = 3; 
      else $pemb = sizeof($arrData)+1;
      
      if($pemb > 9) $jarakY = 0.9;
      else $jarakY = 0.7;

      $graph = new PieGraph(1000,ceil($pemb/3)*200,'auto');
      $temp = round($jarakY/($pemb/3), 2);
      $size=round(1.1/$pemb, 2);
      
      for ($i=0;$i<=sizeof($arrData);$i++) {
      
      if ($i%3==0) {
         if ($i==0) $b = 1.5/$pemb;
         else $b += $temp;
         $a = 0.15;
      } 
      //elseif ($i<4) $a += 0.27;
      else $a += 0.30;
         
         if (isset($arrData[$i][0])) {
                  
            for($k = 0;$k<count($this->kategori);$k++){
               $data_source[$i][$k] = $arrData[$i][$k];
               $pembanding[$i] += $data_source[$i][$k];
               $jum_akhir[$k] += $data_source[$i][$k];
            }
            
            if($pembanding[$i] > 0):
               $p[$i] = new PiePlot3D($data_source[$i]);
               if ($i==0)
                  $p[$i]->SetLegends($this->legend);
                  
               $p[$i]->SetAngle(45);  
               
               $p[$i]->SetSize($size);
               $p[$i]->SetCenter($a, $b);
               $p[$i]->value->SetFont(FF_ARIAL,FS_NORMAL);
               $p[$i]->title->Set($this->row[$i]);
               $p[$i]->title->SetMargin(10);
               //$p[$i]->SetLabelPos(0.6);
               $graph->Add($p[$i]);
                
            endif;
         }else {
            for($k = 0;$k<count($this->kategori);$k++){
               $data_source_akhir[] = $jum_akhir[$k];
            }

            $p1= new PiePlot3D($data_source_akhir);
            $p1->SetSize($size);
            $p1->value->SetFont(FF_ARIAL,FS_NORMAL,10);
            $p1->SetAngle(45);
            if($_SESSION['user_level_id'] == 1)
               $p1->title->Set('UNIVERSITAS');
            else
               $p1->title->Set('FAKULTAS');
            $p1->title->SetMargin(30);

            $p1->SetCenter($a, $b+0.02);

            $graph->Add($p1);
         }
      }  
      
      $graph->SetShadow();
      $graph->legend->SetAbsPos(10,10,'right','top');
      $graph->Stroke();  
      
   }
   
   function createPieGraph(){
      
      if (sizeof($this->value)<3) $pemb = 3; else $pemb = sizeof($this->value)+1;
      $graph = new PieGraph(950,ceil($pemb/3)*250,'auto');
      $temp = round(0.9/($pemb/3), 2);
      $size=round(1.1/$pemb, 2);
      $a = $b = 0;
      
      $temp = round(0.9/((sizeof($this->row)+1)/3), 2);
      $a = $b = 0;

      for ($i=0;$i<=sizeof($this->row);$i++) {
         for ($j=0;$j<sizeof($this->value[$this->row[$i]]);$j++) {
            for($k = 0;$k<count($this->kategori);$k++){
               $jum_akhir[$k] += $this->value[$this->row[$i]][$j][$this->kategori[$k]]; 
               $jumlah[$k][$i] += $this->value[$this->row[$i]][$j][$this->kategori[$k]];
            }            
         }
          
         if ($i%3==0) {
            if ($i==0) $b = 1.5/$pemb;
            else $b += $temp;
            $a = 0.15;
         } //elseif ($i<4) $a += 0.27;
         else $a += 0.32;
         
         if (isset($this->value[$this->row[$i]][0])) {   
            
            for($k = 0;$k<count($this->kategori);$k++){
               $data_source[$i][] = $jumlah[$k][$i];
               $pembanding[$i] += $data_source[$i][$k];
            }
            
            if($pembanding[$i] > 0):
               $p[$i] = new PiePlot3D($data_source[$i]);
               if ($i==0)
                  $p[$i]->SetLegends($this->legend);
               $p[$i]->SetAngle(45);  
               $p[$i]->SetTheme('earth');
               $p[$i]->SetSize($size);
               $p[$i]->SetCenter($a, $b);
               $p[$i]->value->SetFont(FF_ARIAL,FS_NORMAL,10);
               $p[$i]->title->Set($this->row[$i]);
               $p[$i]->title->SetMargin(30);
               
               //$p[$i]->ExplodeSlice(1);  
               $graph->Add($p[$i]);
               //$graph->SetTitlemargin(500); 
            endif;
         } else {            
            for($k = 0;$k<count($this->kategori);$k++){
               $data_source_akhir[] = $jum_akhir[$k];
            }
            
            $p1= new PiePlot3D($data_source_akhir);
            //$p1->SetSize(0.12);
            $p1->SetSize($size + 0.02);
            $p1->SetCenter($a, $b+0.02);
            $p1->value->SetFont(FF_ARIAL,FS_NORMAL,10);
            $p1->title->Set('UNIVERSITAS');
            $p1->title->SetMargin(30); 
            $graph->Add($p1);
         }
      }  
      
      $graph->SetShadow();
      $graph->legend->SetAbsPos(10,10,'right','top');
      $graph->Stroke();  
      
   }

}

?>
