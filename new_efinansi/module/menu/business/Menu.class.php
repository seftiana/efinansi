<?php
class Menu extends Database {

   function ListAvailableMenu($userName, $flagShow="") {
      if ($flagShow != "") {
         $result = $this->GetAllDataAsArray($this->mSqlQueries['nav'], 
		 array($userName, $flagShow));
		 
         $result_report = $this->Open($this->mSqlQueries['nav_report'], 
		 array($userName, $flagShow));
		 
         $result = array_merge($result, $result_report);
      } else {
         $result = $this->GetAllDataAsArray($this->mSqlQueries['list_available_menu'], 
		 array($userName));
		 
         $result_report = $this->Open($this->mSqlQueries['list_available_menu_report'], 
		 array($userName));
		 
         $result = array_merge($result, $result_report);
      }

      return $result;
   }

   function ListAvailableSubMenu($parentId, $flagShow="") {
      if ($flagShow != "") {
         $result = $this->GetAllDataAsArray
		 ($this->mSqlQueries['list_available_submenu_with_flag_show'], 
		 array($parentId, $flagShow));
		 
         $result_report = $this->Open
		 ($this->mSqlQueries['list_available_submenu_with_flag_show_report'], 
		 array($parentId, $flagShow));
		 
         $result = array_merge($result, $result_report);
      } else {
         $result = $this->GetAllDataAsArray
		 ($this->mSqlQueries['list_available_submenu'], array($parentId));
		 
         $result_report = $this->Open
		 ($this->mSqlQueries['list_available_submenu_report'], 
		 array($parentId));
		 
         $result = array_merge($result, $result_report);
      }

      return $result;
   }

   function ListAllAvailableSubMenuForGroup($userId,$menuId) {
   	//die('tet');
      #printf($this->mSqlQueries['list_all_available_submenu_for_group'], $userId,$menuId);

      if (strpos($menuId,'report') === false)
      {

         $result = $this->Open
		 			($this->mSqlQueries['list_all_available_submenu_for_group'], 
					 array($userId,$menuId));

#         if ($menuId == '')
#         {
#            $result_report = $this->Open($this->mSqlQueries['list_all_available_submenu_for_group_report'], array('%','report0'));
#            $result = array_merge($result, $result_report);
#         }
      }
      else $result = $this->Open
	  				 ($this->mSqlQueries['list_all_available_submenu_for_group_report'], 
					  array($userId,$menuId));
      return $result;
   }
}
?>