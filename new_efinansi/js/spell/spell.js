/**
copyleft by wahyu@gamatechno.com
**/
function spellangka(par)
{	  
	if (par=='1') {
		spell='satu';
	}
	if (par=='2') {
		spell='dua';
	}
	if (par=='3') {
		spell='tiga';
	}
	if (par=='4') {
		spell='empat';
	}
	if (par=='5') {
		spell='lima';
	}
	if (par=='6') {
		spell='enam';
	}
	if (par=='7') {
		spell='tujuh';
	}
	if (par=='8') {
		spell='delapan';
	}
	if (par=='9') {
		spell='sembilan';
	}
	if (par=='0') {
	   spell='';
	}
	return spell;
}
function spellribu(jml_ribuan)
{	   
	if (jml_ribuan==0){
	   ribu='';
	}
	if (jml_ribuan==1){
	   ribu='ribu';
	}
	if (jml_ribuan==2){
	   ribu='juta';
	}
	if (jml_ribuan==3){
	   ribu='milyar';
	}
	if (jml_ribuan==4){
	   ribu='trilyun';
	}
	if (jml_ribuan==5){
	   ribu='bilyun';
	}
	return ribu;
}	
function exec(angka) 
{   
   //panjang angka
	if ((eval(angka)*1)!=0) {
		var l_angka=angka.length;   
		
		//begin jika panjang angka = 1
		if (l_angka==1)
		{
		   spell=spellangka(angka);			
		}
		//end jika panjang angka = 1
		
		//begin jika panjang angka = 2
		if(l_angka==2)
		{		   
			angka21 = angka.substr(0,1);
			angka22 = angka.substr(1,1);			
			if (angka21==1) {
				if (angka22==0){
					spell='sepuluh';
				}else if(angka22==1){
					spell='sebelas';
				}else{				   
					spell = spellangka(angka22) + ' belas';					
				}							
			}else{
				spell21=spellangka(angka21);
				spell22=spellangka(angka22);				
				spell = spell21 + ' puluh ' + spell22;							
				
			}			
		}
		//end jika panjang angka = 2
		
		//begin jika panjang angka = 3	
		if(l_angka==3)
		{
			var angka3 = new Array(3);
			var spell3 = new Array(3);
			var puluh3 = new Array(3);
			for (i=0;i<3;i++){			   
			   angka3[i]=angka.substr(i,1);				
				spell3[i]=spellangka(angka3[i]);				
			}	
			if (spell3[0]=='satu'){
			   spell3[0]='se';
			}			
			if ((angka3[0]==0)&&(angka3[1]!=0)&&(angka3[2]!=0)){
			   if (angka3[1]==1){
				   if (angka3[2]==1){
					   spell = 'sebelas'
					}else{
					   spell = spellangka(angka3[2]) + ' belas ';
					}
				}else{
				   spell = spell3[1] + ' puluh ' + spell3[2];
				}
			}
			if ((angka3[0]!=0)&&(angka3[1]==0)&&(angka3[2]!=0)){
			   spell = spell3[0] + ' ratus ' + spell3[2];
			}
			if ((angka3[0]!=0)&&(angka3[1]!=0)&&(angka3[2]==0)){
			   spell = spell3[0] + ' ratus ' + spell3[1] + ' puluh ';
			}
			if ((angka3[0]==0)&&(angka3[1]!=0)&&(angka3[2]==0)){
			   if (angka3[1]==1) {
				   spell = 'sepuluh';
				}else{
				   spell = spellangka(angka3[1]) + ' puluh ';
				}
			}			
			if ((angka3[0]!=0)&&(angka3[1]!=0)&&(angka3[2]!=0)){			   	
				if (angka3[1]==1){
				   if (angka3[2]==1) {
					   spell = spell3[0] + ' ratus sebelas ';
					}else{
					   spell = spell3[0] + ' ratus ' + spell3[1] + ' belas ';
					}
				}else{
				   spell = spell3[0] + ' ratus ' + spell3[1] + ' puluh ' + spell3[2];
				}
			}
			if ((angka3[0]!=0)&&(angka3[1]==0)&&(angka3[2]==0)){
				if (angka3[0]==1) {
					spell = "seratus";
				}else{
					spell = spell3[0] + ' ratus '
				}
			}
		}
		//end jika panjang angka = 3
		
		//begin jika panjang angka > 3
		if ((l_angka>3)&&(l_angka<=18))
		{      
			//bagi angka ke satuan ribuan (3)
			var jml_ribuan = Math.floor(l_angka/3);      		
			
			//cari panjang angka setelah dikurangi jumlah ribuan
			l_angka_awal = l_angka-(jml_ribuan*3);
			
			//begin jika ada angka awal
			if (l_angka_awal!=0) {
				l_angka_akhir = jml_ribuan*3;
            
				angka_awal = angka.substr(0,l_angka_awal);		
				angka_akhir = angka.substr(l_angka_awal,(l_angka_akhir));		
            
				//begin membilangkan angka awal		
				if (l_angka_awal==1){
					if (spellangka(angka_awal)=='satu'){
						if (jml_ribuan>1){
						   spell='satu';							
						}else{						   
							spell='se';
						}
					}else{
						spell=spellangka(angka_awal);
					}														
				}
				if (l_angka_awal==2){
					angka_awal1 = angka_awal.substr(0,1);
					angka_awal2 = angka_awal.substr(1,2);			
					if (angka_awal1==1) {
						if (angka_awal2==0){
							spell='sepuluh';
						}else if(angka_awal2==1){
							spell='sebelas';
						}else{				   
							spell = spellangka(angka_awal2) + ' belas ';					
						}							
					}else{
						spell1=spellangka(angka_awal1);
						spell2=spellangka(angka_awal2);						 
						spell = spell1 + ' puluh ' + spell2;
							
					}					
					spell=spell;
				}				
				if (spell!=''){
					var akhiran = spellribu(jml_ribuan);
				}else{
					akhiran='';
				}
				spell_awal= spell.toString() + ' ' + akhiran;				
				//end membilangkan angka awal
				
				//begin membilangkan angka akhir				
				var spell_akhir='';
				var angka_akhirx = new Array(jml_ribuan);
				var spell_akhirx = new Array(jml_ribuan);
				var akhiranx = new Array(jml_ribuan);
				var sisa_ribu = new Array(jml_ribuan);
				var angka_akhirx1 = new Array(jml_ribuan);	
				var angka_akhirx2 = new Array(jml_ribuan);	
				var angka_akhirx3 = new Array(jml_ribuan);
				
				var spell_akhirx1 = new Array(jml_ribuan);
				var spell_akhirx2 = new Array(jml_ribuan);
				var spell_akhirx3 = new Array(jml_ribuan);
									
				for (b=0;b<jml_ribuan;b++){				   
					angka_akhirx[b] = angka_akhir.substr(b*3,3);
					
					angka_akhirx1[b]=angka_akhirx[b].substr(0,1);	
					angka_akhirx2[b]=angka_akhirx[b].substr(1,1);
					angka_akhirx3[b]=angka_akhirx[b].substr(2,1);				
					
					spell_akhirx1[b]=spellangka(angka_akhirx1[b]);	
				  	spell_akhirx2[b]=spellangka(angka_akhirx2[b]);
					spell_akhirx3[b]=spellangka(angka_akhirx3[b]);					
					
					if (spell_akhirx1[b]=='satu'){
			         spell_akhirx1[b]='se';			      
					}
					
					if ((angka_akhirx1[b]==0)&&(angka_akhirx2[b]!=0)&&(angka_akhirx3[b]!=0)){					
						if (angka_akhirx2[b]==1){							
							if (angka_akhirx3[b]==1){
								spell_akhirx[b] = 'sebelas'
							}else{
								spell_akhirx[b] = spellangka(angka_akhirx3[b]) + ' belas ';
							}							
						}else{
							spell_akhirx[b] = spell_akhirx2[b] + ' puluh ' + spell_akhirx3[b];
						}						
			      }
														
					if ((angka_akhirx1[b]!=0)&&(angka_akhirx2[b]==0)&&(angka_akhirx3[b]!=0)){
			         spell_akhirx[b] = spell_akhirx1[b] + ' ratus ' + spell_akhirx3[b];
			      }
					if ((angka_akhirx1[b]!=0)&&(angka_akhirx2[b]!=0)&&(angka_akhirx3[b]==0)){
			         if (angka_akhirx2[b]==1) {						
							spell_akhirx[b] = spell_akhirx1[b] + 'ratus ' + 'sepuluh';
						}else{
						   spell_akhirx[b] = spell_akhirx1[b] + ' ratus ' + spell_akhirx2[b] + ' puluh ';
						}
						//spell_akhirx[b] = spell_akhirx1[b] + ' ratus ' + spell_akhirx2[b] + ' puluh ';
			      }
					if ((angka_akhirx1[b]==0)&&(angka_akhirx2[b]!=0)&&(angka_akhirx3[b]==0)){
					   if (angka_akhirx2[b]==1) {
							spell_akhirx[b] = 'sepuluh';
						}else{
							spell_akhirx[b] = spellangka(angka_akhirx2[b]) + ' puluh ';
						}
			      }
					if ((angka_akhirx1[b]!=0)&&(angka_akhirx2[b]==0)&&(angka_akhirx3[b]==0)){
					   if (angka_akhirx1[b]==1) {						
							spell_akhirx[b] = 'seratus';
						}else{							
							spell_akhirx[b] = spellangka(angka_akhirx1[b]) + ' ratus ';
						}
			      }					
					if ((angka_akhirx1[b]!=0)&&(angka_akhirx2[b]!=0)&&(angka_akhirx3[b]!=0)){			   	
						if (angka_akhirx2[b]==1){
							if (angka_akhirx3[b]==1) {								
								spell_akhirx[b] = spell_akhirx1[b] + 'ratus sebelas';
							}else{								
								spell_akhirx[b] = spell_akhirx1[b] + ' ratus ' + spell_akhirx3[b] + ' belas ';
							}
						}else{
							spell_akhirx[b] = spell_akhirx1[b] + ' ratus ' + spell_akhirx2[b] + ' puluh ' + spell_akhirx3[b];
						}
			      }
					if ((angka_akhirx1[b]==0)&&(angka_akhirx2[b]==0)&&(angka_akhirx3[b]!=0)){
					   spell_akhirx[b] = spell_akhirx3[b];
					}
					sisa_ribu[b]=jml_ribuan-(b+1);
					if ((angka_akhirx1[b]==0)&&(angka_akhirx2[b]==0)&&(angka_akhirx3[b]==0)){					   
						spell_akhirx[b] = '';						
						akhiranx[b]='';
					}else{					
					   akhiranx[b]=spellribu(sisa_ribu[b]);					
					}
					spell_akhir = spell_akhir + ' ' + (spell_akhirx[b].toString() + ' ' + akhiranx[b].toString());								  
				   
				}
				spell = spell_awal+ ' ' + spell_akhir;
				//end for				
				//end membilangkan angka akhir
			}else{   //end jika ada angka awal			  
			   //begin membilangkan angka akhir				
				var spell_akhir2='';
				var angka_akhirx = new Array(jml_ribuan);
				var spell_akhirx = new Array(jml_ribuan);
				var akhiranx = new Array(jml_ribuan);
				var sisa_ribu = new Array(jml_ribuan);
				var angka_akhirx1 = new Array(jml_ribuan);	
				var angka_akhirx2 = new Array(jml_ribuan);	
				var angka_akhirx3 = new Array(jml_ribuan);
				
				var spell_akhirx1 = new Array(jml_ribuan);
				var spell_akhirx2 = new Array(jml_ribuan);
				var spell_akhirx3 = new Array(jml_ribuan);
									
				for (b=0;b<jml_ribuan;b++){				   
					angka_akhirx[b] = angka.substr(b*3,3);
					
					angka_akhirx1[b]=angka_akhirx[b].substr(0,1);	
					angka_akhirx2[b]=angka_akhirx[b].substr(1,1);
					angka_akhirx3[b]=angka_akhirx[b].substr(2,1);				
					
					spell_akhirx1[b]=spellangka(angka_akhirx1[b]);	
				  	spell_akhirx2[b]=spellangka(angka_akhirx2[b]);
					spell_akhirx3[b]=spellangka(angka_akhirx3[b]);					
					
					if (spell_akhirx1[b]=='satu'){
			         spell_akhirx1[b]='se';			      
					}
					
					if ((angka_akhirx1[b]==0)&&(angka_akhirx2[b]!=0)&&(angka_akhirx3[b]!=0)){					
						if (angka_akhirx2[b]==1){							
							if (angka_akhirx3[b]==1){
								spell_akhirx[b] = 'sebelas'
							}else{
								spell_akhirx[b] = spellangka(angka_akhirx3[b]) + ' belas ';
							}							
						}else{
							spell_akhirx[b] = spell_akhirx2[b] + ' puluh ' + spell_akhirx3[b];
						}						
			      }
														
					if ((angka_akhirx1[b]!=0)&&(angka_akhirx2[b]==0)&&(angka_akhirx3[b]!=0)){
			         spell_akhirx[b] = spell_akhirx1[b] + ' ratus ' + spell_akhirx3[b];
			      }
					if ((angka_akhirx1[b]!=0)&&(angka_akhirx2[b]!=0)&&(angka_akhirx3[b]==0)){
			         if (angka_akhirx2[b]==1) {						
							spell_akhirx[b] = spell_akhirx1[b] + ' ratus ' + 'sepuluh';
						}else{
						   spell_akhirx[b] = spell_akhirx1[b] + ' ratus ' + spell_akhirx2[b] + ' puluh ';
						}
			      }
					if ((angka_akhirx1[b]==0)&&(angka_akhirx2[b]!=0)&&(angka_akhirx3[b]==0)){
					   if (angka_akhirx2[b]==1) {						
							spell_akhirx[b] = 'sepuluh';
						}else{							
							spell_akhirx[b] = spellangka(angka_akhirx2[b]) + ' puluh ';
						}
			      }
					if ((angka_akhirx1[b]!=0)&&(angka_akhirx2[b]==0)&&(angka_akhirx3[b]==0)){					   
						if (angka_akhirx1[b]==1) {						
							spell_akhirx[b] = 'seratus';
						}else{							
							spell_akhirx[b] = spellangka(angka_akhirx1[b]) + ' ratus ';
						}
			      }										
					if ((angka_akhirx1[b]!=0)&&(angka_akhirx2[b]!=0)&&(angka_akhirx3[b]!=0)){			   	
						if (angka_akhirx2[b]==1){
							if (angka_akhirx3[b]==1) {
								spell_akhirx[b] = spell_akhirx1[b] + 'ratus sebelas';
							}else{
								spell_akhirx[b] = spell_akhirx1[b] + ' ratus ' + spell_akhirx3[b] + ' belas ';
							}
						}else{
							spell_akhirx[b] = spell_akhirx1[b] + ' ratus ' + spell_akhirx2[b] + ' puluh ' + spell_akhirx3[b];
						}
			      }
					if ((angka_akhirx1[b]==0)&&(angka_akhirx2[b]==0)&&(angka_akhirx3[b]!=0)){
					   spell_akhirx[b] = spell_akhirx3[b];
					}
					sisa_ribu[b]=jml_ribuan-(b+1);
					
					if ((angka_akhirx1[b]==0)&&(angka_akhirx2[b]==0)&&(angka_akhirx3[b]==0)){	
						akhiranx[b] = '';
						spell_akhirx[b] = '';
					}else{					
						akhiranx[b]=spellribu(sisa_ribu[b]);
					}
					
					spell_akhir2 = spell_akhir2 + ' ' + (spell_akhirx[b].toString() + ' ' + akhiranx[b].toString());	  
				   
				}
				spell = spell_akhir2; 
			}			
		}	
		//end jika panjang angka > 3
		if (l_angka>18) {
			spell='undefined';
		}
		}else{
		   spell='';
		}
	return (spell);		
		
}
function Trim(TRIM_VALUE){
	if(TRIM_VALUE.length < 1){
		return"";
	}
	TRIM_VALUE = RTrim(TRIM_VALUE);
	TRIM_VALUE = LTrim(TRIM_VALUE);
	if(TRIM_VALUE==""){
		return "";
	}else{
		return TRIM_VALUE;
	}
} //End Function

function RTrim(VALUE){
	var w_space = String.fromCharCode(32);
	var v_length = VALUE.length;
	var strTemp = "";
	if(v_length < 0){
		return"";
	}
	var iTemp = v_length -1;

	while(iTemp > -1){
		if(VALUE.charAt(iTemp) == w_space){
	}else{
		strTemp = VALUE.substring(0,iTemp +1);
		break;
	}
	iTemp = iTemp-1;
	} //End While
	return strTemp;
} //End Function

function LTrim(VALUE){
	var w_space = String.fromCharCode(32);
	if(v_length < 1){
	return"";
	}
	var v_length = VALUE.length;
	var strTemp = "";

	var iTemp = 0;

	while(iTemp < v_length){
	if(VALUE.charAt(iTemp) == w_space){
	}
	else{
	strTemp = VALUE.substring(iTemp,v_length);
	break;
	}
	iTemp = iTemp + 1;
	} //End While
	return strTemp;
} //End Function


function splitangka(element_tujuan,angka){	
	angka=eval(angka);
	angka=angka.toString();	
	pj_angka=angka.length;
	titik=angka.indexOf(".");
	if(titik>=0){
		depan_koma=angka.substr(0,titik);
		belakang_koma=angka.substr(titik+1,pj_angka);		
		spell_depan=exec(depan_koma);
		
		belakang_angka=eval(belakang_koma);
		pj_belakang_angka=belakang_koma.length;		
		idx_belakang_angka=belakang_koma.indexOf(belakang_angka);		
		belakang_nol=belakang_koma.substr(0,idx_belakang_angka);
		
		pj_belakang_nol=belakang_nol.length;				
		belakang_bukan_nol=belakang_koma.substr(idx_belakang_angka,pj_belakang_angka);
		var nol = '';
		for (idnol=0;idnol<pj_belakang_nol;idnol++){
			nol = nol + ' nol ';
		}
		
		spell_belakang = nol + exec(belakang_bukan_nol);
		//form.spell.value=spell_depan + ' koma ' + spell_belakang;
		//form.spell.value=Trim(spell_depan) + ' koma ' + Trim(spell_belakang) + ' rupiah';
		document.getElementById(element_tujuan).innerHTML = '" ' + Trim(spell_depan) + ' koma ' + Trim(spell_belakang) + ' rupiah "';
	}else{
		//form.spell.value=Trim(exec(angka)) + ' rupiah';
		document.getElementById(element_tujuan).innerHTML = '" ' + Trim(exec(angka)) + ' rupiah "';
	}
}