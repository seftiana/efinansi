// Basic Functions
function include_dom(script_filename) {
    var html_doc = document.getElementsByTagName('head').item(0);
    var js = document.createElement('script');
    js.setAttribute('language', 'javascript');
    js.setAttribute('type', 'text/javascript');
    js.setAttribute('src', script_filename);
    html_doc.appendChild(js);
    return false;
}

function addEvent( obj, type, fn ) {
	if ( obj.attachEvent ) {
		obj['e'+type+fn] = fn;
		obj[type+fn] = function(){obj['e'+type+fn]( window.event );}
		obj.attachEvent( 'on'+type, obj[type+fn] );
	} else
		obj.addEventListener( type, fn, false );
}

function gotoURL (url) {
	window.location.href = url;	
}

// siaPage
function siaPage () {
	var self = this;
	return self.init();
}

siaPage.prototype = {
	init : function () {
		var self = this;
		// hide no-javascript alert 
		//var img_alert = document.getElementById("js-alert")
		//addEvent(window, "load", function() {document.getElementById('js-alert').style.display = "none";});
		
		//create checkbox handler
		addEvent(window, "load", function() {
											input = document.getElementsByTagName("INPUT");
											for (i=0; i<input.length; i++) {			
												if (input[i].className == "checkrow" || input[i].className == "chkbox") {
													if (input[i].checked) {
														var tr_node = input[i].parentNode;
														while (tr_node.tagName != "TR") {
															tr_node = tr_node.parentNode;
														}
														switch (tr_node.className) {
															case 'active'	: tr_node.className = "selected-active"; break;
															case 'even'		: tr_node.className = "selected-even"; break;
															case ''			: tr_node.className = "selected"; break;
														}
													}
									
													addEvent(input[i], "click", function() {self.selectThisRow(this);});
																				
												} else if (input[i].className == "checkallrow") {
													addEvent(input[i], "click", function() {self.selectAllRows(this);});
												}
											}
										});

	},
	
	selectThisRow: function (obj) {
		var tr_node = obj.parentNode;
		
		while (tr_node.tagName != "TR") {
			tr_node = tr_node.parentNode;
		}
		if (obj.checked)
			switch (tr_node.className) {
				case 'active'			: tr_node.className = "selected-active"; break;
				case 'even'				: tr_node.className = "selected-even"; break;
				case ''					: tr_node.className = "selected"; break;
			}
		else
			switch (tr_node.className) {
				case 'selected-active'	: tr_node.className = "active"; break;
				case 'selected-even'	: tr_node.className = "even"; break;
				case 'selected'			: tr_node.className = ""; break;
			}
	},
	
	selectAllRows: function (obj) {
		var self = this;
		var table_node = obj.parentNode;
		
		while (table_node.tagName != "TABLE") {
			table_node = table_node.parentNode;
		}
		
		var input = table_node.getElementsByTagName("INPUT");
		
		for (i=0; i<input.length; i++) {			
			if (input[i].className == "checkrow" || input[i].className == "chkbox") {
				input[i].checked = obj.checked;
				self.selectThisRow(input[i]);
			}
		}
		/*
		switch (tr_node.className) {
			case 'active'			: tr_node.className = "selected-active"; break;
			case 'selected-active'	: tr_node.className = "active"; break;
			case 'even'				: tr_node.className = "selected-even"; break;
			case 'selected-even'	: tr_node.className = "even"; break;
			case ''					: tr_node.className = "selected"; break;
			case 'selected'			: tr_node.className = ""; break;
		}
		*/
	}


}

var newPage = new siaPage();

//Drawer
function simpleDrawer (isOpen, drawerObj, buttonObj) {
	var self = this;
	self.isOpen = isOpen;
	self.drawerObj = drawerObj;
	self.buttonObj = buttonObj;
	return self.init();
}

simpleDrawer.prototype = {
	init : function () {
		var self = this;
		self.slide_drawer(self.drawerObj, self.buttonObj);
		addEvent(document.getElementById(self.buttonObj),'click', function(){ self.slide_drawer(self.drawerObj, self.buttonObj); });
		
		a = document.getElementById(self.drawerObj);
		b = a.childNodes[0];
		
		while (b) {
			if (b.nodeType==1) 
				if (b.className =='closedvis') 
					addEvent(b,'click', function(){ self.slide_drawer(self.drawerObj, self.buttonObj); });
			b = b.nextSibling;
		}
	},
	
	slide_drawer : function (obj, b_obj) {
		var self = this;
		a = document.getElementById(obj);
		b = a.childNodes[0];
		
		while (b) {
			if (b.nodeType==1) {
				if (b.className =='openvis') {
					b.style.display = (self.isOpen ? 'block' : 'none');
				} else if (b.className =='closedvis') {
					b.style.display = (self.isOpen ? 'none' : 'block');
				}
			}
			b = b.nextSibling;
		}
		
		a = document.getElementById(b_obj);
		a.style.backgroundImage = "url('" + DEFAULT_STYLE_PATH + "images/dataquest-button-" + (self.isOpen ? 'shrink' : 'expand') + ".gif')";
		self.isOpen = !self.isOpen;
	}
}

//Menu Selector
function menuSelector (contObj, activeMenu) {
	var self = this;
	self.contObj = contObj;
	self.activeMenuObj = activeMenu;
	
	return self.init();
}

menuSelector.prototype = {
	init : function () {
		var self = this;
		a = document.getElementById(self.contObj);
		b = a.childNodes[0];
	
		while (b) {
			if (b.nodeType==1) {
				if (b.tagName == 'UL' || b.tagName == 'H3') {
					if (b.id != self.activeMenuObj)
						b.style.display = 'none';
				}
			}
			
			b = b.nextSibling;
		}
		
		b = a.getElementsByTagName("SELECT");
		obj_select = b.item(0);
		
		c = b.item(0).getElementsByTagName("OPTION");
		img_ico = a.getElementsByTagName("IMG");

		for (i=0; i<c.length; i++) {
			if (c.item(i).value == self.activeMenuObj) {
				c.item(i).selected = true;
				
				if (img_ico.length > 0) 
					img_ico.item(0).src = DEFAULT_IMAGES_PATH + "icons/" + c.item(i).value.substring(3, c.item(i).value.length) + "-16x16.gif";
				//break;
			}
			
			c.item(i).style.backgroundImage = "url('" + DEFAULT_IMAGES_PATH + "icons/" + c.item(i).value.substring(3, c.item(i).value.length) + "-16x16.gif')";
		}
		
		addEvent(obj_select,'change', function(){ self._changeMenu(); }); 
	},
	
	_changeMenu : function  () {
		var self = this;
		a = document.getElementById(self.contObj);
		b = a.getElementsByTagName("SELECT");
		obj_select = b.item(0);
		
		c = (document.getElementById(obj_select.value)).getElementsByTagName("LI");
		img_ico = a.getElementsByTagName("IMG");
		//alert(img_ico.item(0));
		if (document.getElementById(GLOBAL_ACTIVE_MENU) != null) {
			if (c.item(1) != null) {
				document.getElementById(GLOBAL_ACTIVE_MENU).style.display = 'none';
				GLOBAL_ACTIVE_MENU = obj_select.value;
				document.getElementById(GLOBAL_ACTIVE_MENU).style.display = 'block';
				
				if (img_ico.length > 0)
					img_ico.item(0).src = DEFAULT_IMAGES_PATH + "icons/" + obj_select.value.substring(3, obj_select.value.length) + "-16x16.gif";
			}
			else
			{
				first_li = c.item(0).getElementsByTagName("A");
				anchor = first_li.item(0);
				window.location.href = anchor.getAttribute('HREF');
			}
		}
		else {
		 	a.innerHTML = "<p>Error pada menu selector</p>";
		}
	}
}

//Checkbox Handlers
function checkboxHandlers () {
	var self = this;	
	return self.init();
}

checkboxHandlers.prototype = {
	init: function () {
		alert("Halaman ini menggunakan checkbox handler versi lama, mohon diperbaiki.");
	}
}

// mcSelector
function mcSelector (obj_id) {
	var self = this;
	self.obj_id = obj_id;
	self.obj = document.getElementById(obj_id);
	self.parent = self.obj.parentNode;
	return self.init();
}

mcSelector.prototype = {
	init : function() {
		var self = this;
		var i = 0;
		var mc_root = document.createElement('DIV');
		var l1_cont = document.createElement('DIV');
		var l2_cont = document.createElement('DIV');
		var l1_head = document.createElement('DIV');
		var l2_head = document.createElement('DIV');
		l1_cont.id = self.obj_id + "col1";
		l2_cont.id = self.obj_id + "col2";
		mc_root.className ="mc-root";
		l1_head.className = "mc-container";
		l2_head.className = "mc-container";
		l1_cont.className = "mc-column";
		l2_cont.className = "mc-column";
		l1_head.innerHTML = "Program Studi:";
		l2_head.innerHTML = "Kurikulum:";
		self.parent.insertBefore(mc_root, self.obj);
		mc_root.appendChild(l1_head);
		mc_root.appendChild(l2_head);
		l1_head.appendChild(l1_cont);
		l2_head.appendChild(l2_cont);
		l2_cont.style.borderLeft = "none";
		
		//self.parent.insertBefore(l1_head, self.obj);
		//self.parent.insertBefore(l2_head, self.obj);
		//self.parent.insertBefore(l1_cont, self.obj);
		//self.parent.insertBefore(l2_cont, self.obj);
		
		l2_cont.style.backgroundColor = "#ffffff";
		
		l1_cont = l1_cont.appendChild(document.createElement('UL'));
		
		ul1_node = self.obj.childNodes[0];
		
		while (ul1_node) {
			if (ul1_node.nodeType == 1) {
				ul1_node.id = self.obj_id + "_l1_" + i;
				a = ul1_node.getElementsByTagName("ul");
				if (a.length > 0) {
					a.item(0).style.display = "none";
					a.item(0).id = ul1_node.id + "_l2";
					l2_cont.appendChild(a.item(0));
				}
				
				//alert(self.obj.className);
				if (self.obj.className == "mcSel-excl")
				addEvent(ul1_node, 'click', function() {
													 name = this.id + "_l2";
													 ul_l2 = document.getElementById(name);
													 
													 li = this.parentNode.childNodes[0];
													 
													 while (li) {
														 if (li.nodeType == 1) {
														 	if (li == this)
																li.className = "selected";
															else
																li.className = "";
														 }
														li = li.nextSibling;
													 }
													 
													 li = ul_l2.parentNode.childNodes[0];
 													 while (li) {
														 if (li.nodeType == 1) {
														 	if (li.id == name)
																li.style.display = "block"
															else
																li.style.display = "none"
														 }
														li = li.nextSibling;
													 }
							  });
				else
				addEvent(ul1_node, 'click', function() {
													 name = this.id + "_l2";
													 ul_l2 = document.getElementById(name);
 
													 //if ()
													 if (ul_l2.style.display == "none") {
													 	ul_l2.style.display = "block"
														this.className = "selected";
														
														checkboxes = ul_l2.getElementsByTagName("INPUT");
														for (j=0; j<checkboxes.length; j++)
															checkboxes.item(j).removeAttribute("disabled");
													 }
													 else {
													 	ul_l2.style.display = "none"
														this.className = "";
														
														checkboxes = ul_l2.getElementsByTagName("INPUT");
														for (j=0; j<checkboxes.length; j++)
															checkboxes.item(j).setAttribute("disabled", "disabled");
													 }
							  });
				i = i + 1;
			}
			_temp = ul1_node.nextSibling;
			if (ul1_node.nodeType == 1)
				l1_cont.appendChild(ul1_node);
			ul1_node = _temp;
			_temp = null;
		}
		
		self.parent.removeChild(self.obj);
		
	}
}

// tabs 
function tabsObject (obj_id) {
	var self = this;
	self.obj_id = obj_id;
	self.obj = document.getElementById(obj_id);
	self.parent = self.obj.parentNode;
	return self.init();
}

tabsObject.prototype = {
	init : function () {
		var self = this;
		var i = 0;
		var prevIsSelected = 0;
		var div = self.obj.childNodes[0];
		var tabs_bar = document.createElement('DIV');
		tabs_bar.className = "buttons";
		
		self.obj.className = "tabs";
		//start tabsbutton
		var _temp = document.createElement('DIV');
		_temp.className = "start";
		tabs_bar.appendChild(_temp);
		
		while (div) {
			if (div.nodeType == 1 && div.tagName == "DIV")	{
				var tab_btn = document.createElement('DIV');
				
				if ( div.className == "selected") {
					tab_btn.className = "tab-on";
					tab_btn.innerHTML = div.title;
					tab_btn.id = self.obj_id + i;
					tabs_bar.appendChild(tab_btn);
					
					div.className = "tab";
					div.id = tab_btn.id + "tab";
					prevIsSelected = 1;
				}
				else if ( div.className == "disabled"){
					tab_btn.className = "tab-dis";
					tab_btn.innerHTML = div.title;
					tab_btn.id = self.obj_id + i;
					tabs_bar.appendChild(tab_btn);
					div.style.display = "none";
				}
				else {
					
					tab_btn.className = "tab-off";
					tab_btn.innerHTML = div.title;
					tab_btn.id = self.obj_id + i;
					tabs_bar.appendChild(tab_btn);
									
					div.className = "tab";
					div.id = tab_btn.id + "tab";
					div.style.display = "none";
				}
				
				if (div.className != "disabled") {
					addEvent(tab_btn, "click", function() {
															var tab = this.parentNode.nextSibling;
															while (tab) {
																if (tab.nodeType == 1){
																	if (tab.id == (this.id + "tab"))
																		tab.style.display = "block";
																	else 
																		tab.style.display = "none";
																}
																tab = tab.nextSibling;
															}
																										
															var btn = this.parentNode.getElementsByTagName("DIV");
															for (i=1; i < (btn.length-1); i++) {														
																if (btn.item(i).id != this.id)
																	btn.item(i).className = "tab-off";
																else
																	btn.item(i).className = "tab-on";
		
															}
															
															if (btn.item(btn.length-2).id != this.id)
																btn.item(btn.length-2).style.borderRight = "1px solid #E0E0E0";
													  });
				}
				i = i + 1;
			}
			div = div.nextSibling;
		}
		
		//end tabsbutton
		_temp = document.createElement('DIV');
		_temp.className = "end";
		tabs_bar.appendChild(_temp);
		
		var btn = tabs_bar.getElementsByTagName("DIV");
		btn.item(btn.length-2).style.borderRight = "1px solid #E0E0E0";

		self.obj.insertBefore(tabs_bar, self.obj.childNodes[0]);
	}
}
//add by Galih(galih_xp@yahoo.com)
function numberValidation(field){   
   var Char;   
   Char = "";
   for (i = 0; i < (field.value.length); i++){
      if(isNaN(field.value.charAt(i))){
         break;
      }else {
         Char = Char + field.value.charAt(i);
      }
   }
   field.value = Char;
}

function hexaNumber(character){
   var Char;
   var charArray;
   Char = "0123456789ABCDEF";
   charArray = Char.split("");
   for(j=0; j<(charArray.length); j++){
      if(charArray[j]==character){
         return true;
      }
   }
   
   return false;
}

function hexaValidation(field){   
   var Char;   
   Char = "";
   for (i = 0; i < (field.value.length); i++){
      if(hexaNumber(field.value.charAt(i))===false){
         break;
      }else {
         Char = Char + field.value.charAt(i);
      }
   }
   field.value = Char;
}

function showHideFunction(element,status) {      
      element = element.split('|');            
      for(i=0;i<element.length;i++){
          var namaElement = document.getElementById(element[i]);            
          if (status) {
             namaElement.style.display='';
          } else {
             namaElement.style.display='none';          
         }
      }
}

function hideAndShowForm(element,strData){
   arrData = strData.value.split('|');
   elementArray = element.split('|');
   element = elementArray[2];   
   charCode = arrData[1].split('');
   if(charCode[0]=='T'){
                        
            status = true;
   }else{
            status = false;
   }
         
   showHideFunction(element,status);
   element1 = elementArray[0]+'|'+elementArray[1];
   if((arrData[1]=='POS') || (arrData[1]=='TMC')){                       
      status = true;
   }else{        
      status = false;      
   }
            
   showHideFunction(element1,status);
}

function hideAndShowLokasiForm(element,strData){   
   if(strData.value=="1"){
      status = true;
   }else{
      status = false;
   }
   showHideFunction(element,status);
}

function toggle_halte_text(cb){
  if (cb.options[cb.selectedIndex].value== "Part"){
    document.getElementById("div_part").style.display = "";
  }else{
    document.getElementById("div_part").style.display = "none";
     //document.formshift.halte_text.disabled= true;
  } 
}

//added by choirul untuk mengeset checkbox 
function setCheckBox(id){
   document.getElementById(id).setAttribute('checked', true);;
}
//include_dom ('scripts/jquery.js');

function showGateCode(id,kode){
		document.getElementById("kode_gate").innerHTML = id.value+kode.value;
}
