<?php

class ViewPaging extends HtmlResponse {

   var $mComponentParameters;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot') .
         'module/paging/template');
      $this->SetTemplateFile('view_paging.html');
   }

   function multipage($jmlitem,$itemviewed,$webpage, $page = false, $nav_class = '', $keyword = false, $combo = false){
      $num = $jmlitem;
      $spp = $itemviewed;     
      $multipage = null;
      if($num>0){
         if ($page) {
            $start = ($page-1) * $spp;
         } else {
            $start = 0;
            $page = 1;
         }

         if(($start+$spp) < $num) {
            $end = $start+$spp;
         } else {
            $end = $num;
         }        

         if ($num > $spp) {
            $pages = $num / $spp;
            $pages = ceil($pages);

            $awal = 1;

            if(($page - 1) < 1){
               $pgawal = $page;
               if (($page + 3) < $pages){
                  $pgakhir = $pgawal + 4;
               } elseif (($page + 3) >= $pages){
                  $pgakhir = $pages;
               }

            } else {
               $pgawal = $page - 1;
               if (( $page + 3) < $pages){
                  $pgakhir = $pgawal + 4;
               } elseif (( $page + 3) >= $pages){
                  $pgakhir = $pages;
               }
            }

            //for ($i = $pgawal; $i <= $pgakhir; $i++) {
            //   $fwd_back .= " <a href=\"$webpage&idx=".$i."\">".$i."</a> ";
            //}

            if ($pgakhir>0){
               $prev = $page - 1;
               $next = $page + 1;

               if($page <= $awal) {

                  // first and prev will be not available
                  $first = "";
                  $prev = "";
                  $next = "$webpage&page=$next&keyword=$keyword&combo=$combo";
                  $last = "$webpage&page=$pages&keyword=$keyword&combo=$combo";
               } elseif($page > $awal && $page < $pages) {

                  $first = "$webpage&page=$awal&keyword=$keyword&combo=$combo";
                  $last = "$webpage&page=$pages&keyword=$keyword&combo=$combo";
                  $next = "$webpage&page=$next&keyword=$keyword&combo=$combo";
                  $prev = "$webpage&page=$prev&keyword=$keyword&combo=$combo";
               } else {
                  $first = "$webpage&page=$awal&keyword=$keyword&combo=$combo";
                  $last = "";
                  $next = "";
                  $prev = "$webpage&page=$prev&keyword=$keyword&combo=$combo";
               }              
            } else {               
               $multipage = NULL;
            }
         }
         else{
            $first = "";
            $last = "";
            $next = "";
            $prev = "";           
         }
      
      $multipage = array("first" => $first, "prev" => $prev, "next" => $next, "last" => $last,
                               "info" => array("start" => $start, "end" => $end, "all" => $num),
                               "nav_class" => $nav_class
                              );
      
      }
      return $multipage;
   }

   function ProcessRequest() {
      # if js is enabled      
      if(isset($msg[0]['page'])) {         
         $msg = Messenger::Instance()->Receive(__FILE__);
         $itemsViewed   = $msg[0]['itemsViewed'];
         $url           = $msg[0]['url']; 
         $page          = $msg[0]['page'];
         $nav_class     = $msg[0]['nav_class'];
         $keyword       = $msg[0]['keyword'];
         $combo         = $msg[0]['combo'];
      }
      
      # if js is disabled
      else{
         $page = false;
         $itemsViewed = 10;
         $totItems = 0;
         $url = '';

         // By default fetch param from gtfw-render module
         if (isset($this->mComponentParameters['itemviewed']))
            $itemsViewed = $this->mComponentParameters['itemviewed'];
            
         if (isset($this->mComponentParameters['totitems']))
            $totItems = $this->mComponentParameters['totitems'];
            
         if (isset($this->mComponentParameters['pagingurl']))
            $url = $this->mComponentParameters['pagingurl'];

         if(isset($this->mComponentParameters['page'])) {
            $page = $this->mComponentParameters['page'];
         } else
            $page = false;

         if(isset($this->mComponentParameters['nav_class'])) {
            $nav_class = $this->mComponentParameters['nav_class'];
         }  else
            $nav_class = '';

         // support component messenger
         $msg = Messenger::Instance()->Receive(__FILE__, $this->mComponentName);
         if(!empty($msg)) {
            //SysLog::Log(print_r($msg, true), 'paging');
            $itemsViewed = $msg[0][0];
            $totItems = $msg[0][1];
            $url = $msg[0][2];         
            
            if(isset($msg[0][4])){
				$nav_class = $msg[0][4];
			} else{
				$nav_class = null;
			}
            if(isset($msg[0][3])){ 
               if (is_array($msg[0][3])) {
                  $page = $msg[0][3][0];
                  $keyword = $msg[0][3][1];
                  $combo = $msg[0][3][2];            
               } else {
                  $page = $msg[0][3];
                  $keyword = false;
                  $combo = false;
               }
            }
            else{
               $page = false;     
               $keyword = false;
               $combo = false;
            }
         }

         //SysLog::Log('nav_class: '.$nav_class, 'paging');
      }
      return $this->multipage($totItems, $itemsViewed, $url, $page, $nav_class, $keyword, $combo);
   }

   function ParseTemplate($data = NULL) {
      if(!empty($data)) {
         if(empty($data['nav_class'])) $data['nav_class']='subcontent-element';
         $this->mrTemplate->SetAttribute('page_nav', 'visibility', 'visible');
         foreach($data as $key => $value) {
            if($value != '') {
               if(is_array($value)) {
                  $this->mrTemplate->AddVar("page_nav_$key", "START_REC_INFO", $value['start']+1);
                  $this->mrTemplate->AddVar("page_nav_$key", "END_REC_INFO", $value['end']);
                  $this->mrTemplate->AddVar("page_nav_$key", "ALL_REC_INFO", $value['all']);
                  $this->mrTemplate->AddVar("page_nav_$key", 'NAV_CLASS', $data['nav_class']);
               } else {
                  $this->mrTemplate->AddVar("page_nav_$key", 'IS_AVAIL', 'YES');
                  $this->mrTemplate->AddVar("page_nav_$key", strtoupper($key)."_NAV_URL", $value);
                  $this->mrTemplate->AddVar("page_nav_$key", 'NAV_CLASS', $data['nav_class']);
               }
            } else {
               $this->mrTemplate->AddVar("page_nav_$key", 'IS_AVAIL', 'NO');
            }
         }
      }//$this->mrTemplate->dump();
   }
}
?>
