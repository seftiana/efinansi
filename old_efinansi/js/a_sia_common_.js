	var global_sidebarContents;
	var global_sidebarIsHidden = 0;
	var global_filterIsHidden = 0;
	var global_activeMenu_state = 0;
	
	function MM_findObj(n, d) 
	{
		var p,i,x;  
		if(!d)
			d=document; 
			
		if((p=n.indexOf("?"))>0&&parent.frames.length) 
		{
		    d=parent.frames[n.substring(p+1)].document; 
			n=n.substring(0,p);
		}

  		if(!(x=d[n])&&d.all)
			x=d.all[n]; 
			
		for (i=0;!x&&i<d.forms.length;i++) 
			x=d.forms[i][n];
			
		for(i=0;!x&&d.layers&&i<d.layers.length;i++) 
			x=MM_findObj(n,d.layers[i].document);
		
		if(!x && d.getElementById) x=d.getElementById(n);
		
		return x;
}

	function toggleFilterbarVisibility(obj) 
	{
			var sidebar = MM_findObj ('datafilter');
			var sidebar_height = sidebar.clientHeight;
			if (global_sidebarIsHidden == 0)
			{
				global_sidebarIsHidden = 1;
				sidebar.style.visibility = 'hidden';
				subcontent.style.left= "0px";
				content.style.backgroundImage = "";
				obj.innerHTML = "&raquo;";

			}
			else
			{
				global_sidebarIsHidden = 0;
				sidebar.style.visibility = 'visible';
				subcontent.style.left= "181px";
				obj.innerHTML = "&laquo;";
			}

	}

	// Drawer Related Functions
	function initDrawer(drawer_obj, min_size) {
		document.getElementById(drawer_obj).style.height = min_size + "px";
		//document.getElementById(snippets_obj).style.display = 'block';
	}
	
	function toggle_drawer(drawer_obj, min_size, max_size, button_obj, eImg_src, cImg_src, snippets_obj, table_obj) {
		if (document.getElementById(drawer_obj).style.height == max_size + "px") {
			document.getElementById(drawer_obj).style.height = min_size + "px";
			document.getElementById(button_obj).style.backgroundImage = "url('styles/sia-mocca/images/" + eImg_src + "')";
			document.getElementById(table_obj).style.display = 'none';
			document.getElementById(snippets_obj).style.display = 'block';
		}
		else {
			document.getElementById(drawer_obj).style.height = max_size + "px";
			document.getElementById(button_obj).style.backgroundImage = "url('styles/sia-mocca/images/" + cImg_src + "')";
			document.getElementById(table_obj).style.display = 'block';
			document.getElementById(snippets_obj).style.display = 'none';
		}
	}
	
	//simpleDrawer = new Object();
	/*
	function slideDrawer(drawer_obj, drawer_state, min_obj_arr, max_obj_arr, button_obj, eImg_src, cImg_src) {
		
	}
	*/
	//Menuselector Related Functions
	function initMenuSelector (cont_obj) {
		a = document.getElementById(cont_obj);
		b = a.childNodes[0];

		while (b) {
			if (b.nodeType==1) {
				if (b.tagName == 'UL' || b.tagName == 'H3') {
					if (b.id != GLOBAL_ACTIVE_MENU)
						b.style.display = 'none';
				}
			}
			
			b = b.nextSibling;
		}

	}
	
	function changeMenu (obj_select) {
		if (obj_select.value != 'mn_home') {
			document.getElementById(GLOBAL_ACTIVE_MENU).style.display = 'none';
			GLOBAL_ACTIVE_MENU = obj_select.value;
			document.getElementById(GLOBAL_ACTIVE_MENU).style.display = 'block';
		}
		else
		{
			document.getElementById(GLOBAL_ACTIVE_MENU).style.display = 'none';
			GLOBAL_ACTIVE_MENU = obj_select.value;
			//document.URL = 'home.html';	
		}
	}

	/*
	function changeMenu (menu_obj, menu_icon_obj) {
		if (global_activeMenu_state == 0) {
			document.getElementById(menu_obj).innerHTML = "<li><a href=\"\">Daftar Pejabat</a></li>"
					+ "<li> <a href=\"\">Pejabat Pengesah</a> </li>"
					+ "<li> <a href=\"\">Dokumen Disahkan</a></li>"
					+ "<li> <a href=\"\">Dokumen Tipe Pengesah</a></li>";
			document.getElementById(menu_icon_obj).src = "images/icon-kurikulum-16x16.gif";
			global_activeMenu_state = 1;
		} 
		else {
			document.getElementById(menu_obj).innerHTML = "	<li> <a href=\"\">Daftar Mahasiswa</a> </li>"
					+ "<li> <a href=\"\">Syarat Kelulusan</a> </li>"
					+ "<li> <a href=\"\">Mata Kuliah</a></li>"
					+ "<li> <a href=\"\">Kesetaraan Mata Kuliah</a> </li>";
					global_activeMenu_state = 0;
			document.getElementById(menu_icon_obj).src = "images/icon-jadwal-16x16.gif";
		}
	}
	*/