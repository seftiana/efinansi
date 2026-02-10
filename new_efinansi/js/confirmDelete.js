/*
@author     gabri.ns@gmail.com
@version    0.2.1
@copyright  2008

Fungsi Global untuk membuat box confirm delete menggunakan JavaScript.
Untuk menggunakannya kita perlu memodifikasi tombol hapus. Terlebih dulu,
hapus nama class yang mengandung xhr, misalnya 'xhr' sendiri, 'xhr_form', dll.
Kemudian, tambahkan attribute:
---------
onClick='return showBoxConfirmDelete(<id>, <name>, <url>, this);'
---------
dimana,
id    : id dari data yang ingin dihapus
name  : nama yang ingin ditampilkan pada interface boxConfirmDelete
url   : alamat modul yang akan memproses penghapusan data

@Modified by Galih(Galih.xp@gmail.com)
@version    0.3
@copyright  2008
Perubahan terletak di tujuan delete box. Langsung di arahkan
ke id content. Ini berguna untuk peletakan yang dinamic box delete nantinya.

@version 0.3.1
- bugfix mengenai container
- memperbaiki performance

@version 0.4
- menambah unsur dinamis pada posisi layout boxConfirmDeleteUI
  berdasarkan letak tombol delete

@version 0.4.1
- add support untuk firefox (dan IE)

@version 0.4.1 - modified by galih
- menggabungkan dengan lib janime
- masih belum support untuk browser IE

@version 0.4.2
- menentukan auto hight document
- memberikan z-index lebih supaya menutup object yang lain

@version 0.4.3
- re-design css untuk ie
*/

function getWidth(){
   return document.getElementById('body-application').clientWidth;
}

function getHeight(){
   return (document.documentElement.scrollHeight > document.body.scrollHeight) ? document.documentElement.scrollHeight : document.body.scrollHeight;
}

function createBoxConfirmDelete()
{
   if (document.getElementById('boxConfirmDelete') != undefined) return true;
   var a = new jElement();
   
   var width = getWidth();
   var height = getHeight();
   
   var scrollX = (document.documentElement) ? document.documentElement.scrollLeft : document.body.scrollLeft;
   var scrollY = (document.documentElement) ? document.documentElement.scrollTop : document.body.scrollTop;
   
   var scrollWidth = (document.documentElement) ? document.documentElement.clientWidth : document.body.clientWidth;
   var scrollHeight = (document.documentElement) ? document.documentElement.clientHeight : document.body.clientHeight;
   
   var xpos = scrollX + (scrollWidth/2) - (150);
   var ypos = scrollY + (scrollHeight/2) - (85/2);
   
   a.theElement = 'div';
   a.setElementOn = 'body-application';
   a.attribute = new Array('id|bgApplication','name|bgApplication');
   a.style='background:#000000; width:100%;height:'+height+'px; position:absolute;top:0px;left:0px;z-index:999999';
   a.createElement();
   
   anime = new janime();
   
   anime.element = 'bgApplication';
   anime.alpha(30);
    
      
   // create form
   a.theElement = 'form';
   a.setElementOn = 'body-application';
   a.attribute = new Array('id|boxConfirmDelete','name|formConfirmDelete','method|POST');
   a.style="position: absolute; padding:0px; top: "+ypos+"px; left: "+xpos+"px; width:300px; height:85px; border: 1px solid #EEEBDD; background: url('images/alert.gif') 10px 20px no-repeat #FFFDEF; z-index:9999999;";
   a.createElement();
   a.theElement.className= 'xhr_form std_form';
      
   /*anime.element = 'boxConfirmDelete';
   anime.speed=10;
   anime.alpha(0);
   anime.fadeIn();
   */
   
   // add form field
   a.theElement = 'input';
   a.setElementOn = 'boxConfirmDelete';
   a.attribute = new Array('id|inputIdDelete','name|idDelete','type|hidden');
   a.createElement();
   
   // create UI
   a.theElement = 'div';
   a.setElementOn = 'boxConfirmDelete';
   a.attribute = new Array('id|boxConfirmDeleteUI');
   a.style = "width:290px; height:75px; padding:10px 10px 0px 0px; cursor:move;";
   a.createElement();
   
   // create text
   a.theElement = 'p';
   a.setElementOn = 'boxConfirmDeleteUI';
   a.attribute = new Array('id|boxConfirmDeleteText');
   a.style='margin:0px 0px 0px 40px; padding:0px; font-family:Arial, Tahoma; font-size:12px;';
   a.createElement();
   a.theElement.innerHTML = "Benarkah data dengan nama <span id='boxConfirmDeleteName' style='font-weight: bold'></span> akan dihapus?";
   
   // create toolbar container
   a.theElement = 'div';
   a.setElementOn = 'boxConfirmDeleteUI';
   a.attribute = new Array('id|boxConfirmDeleteUIToolbar','class|toolbar');
   a.style="margin-top: 0px;float: left; margin: 20px 0px 5px 150px; padding: 0px; font-size: 8pt; font-family: Tahoma, Arial Helvetica, sans-serif; text-align:left; height: 20px;";
   a.createElement();
   
   // create Yes button
   a.theElement = 'input';
   a.setElementOn = 'boxConfirmDeleteUIToolbar';
   a.attribute = new Array('class|inputButton','type|button');
   a.style="color: #000000; background: #ffffff;border: 1px #ece9d6  solid;font-size: 8pt;font-family: Tahoma, Arial, Helvetica, sans-serif;height: 22px; overflow: hidden; cursor:hand; padding: 3px 7px; margin: 1px 1px 0px 0px;";
   a.createElement();
   a.theElement.value="Hapus";
   a.theElement.onclick= function (e) {document.getElementById('boxConfirmDelete').onsubmit();hideBoxConfirmDelete()};
   
   //create No button
   a.theElement = 'a';
   a.setElementOn = 'boxConfirmDeleteUIToolbar';
   a.attribute = new Array('id|boxConfirmDeleteNoButton');
   a.style="border: #ece9d6 1px solid; height: 14px;padding: 3px 7px; margin: 1px 1px 0px 0px; display: block; float: left; overflow: hidden; cursor: hand; width:50px ;background: #FFFFFF; color:#000000";
   a.createElement();
   a.theElement.innerHTML = " &nbsp; &nbsp;Batal&nbsp; &nbsp; ";
   a.theElement.onclick= function (e) {hideBoxConfirmDelete()};
   
   $("#boxConfirmDelete").hide();
   $("#boxConfirmDelete").fadeIn(300); 
   
   $(document).ready(function(){
      $("#boxConfirmDeleteUI").mousemove(function(){
         $('#bgApplication').height(document.documentElement.scrollHeight);
         $('#bgApplication').width(document.documentElement.scrollWidth);
      });
      $("#boxConfirmDeleteUI").mousedown(function(){ $("#boxConfirmDelete").draggable();
      });
      $("#boxConfirmDeleteUI").mouseup(function(){$("#boxConfirmDelete").draggable('disable')});
  });
   
   return true;
}

function showBoxConfirmDelete (id, name, url, ndNode)
{
	
   var apply = false;
   if (!document.getElementById("boxConfirmDelete"))
   {
      if (!createBoxConfirmDelete()) return true;
      apply = true;
   }
   
   document.getElementById("inputIdDelete").value = id;
   document.getElementById("boxConfirmDeleteName").innerHTML = name;
   document.getElementById("boxConfirmDelete").action = url;
   
   if (apply) Behaviour.apply(document.getElementById("inputIdDelete"));
   return false;
}

function hideBoxConfirmDelete()
{
   
   var prnt = document.getElementById('body-application');
   var chld = document.getElementById('boxConfirmDelete');
   var chld1 = document.getElementById('bgApplication');
   
   $("#boxConfirmDelete").fadeOut(300,function(){prnt.removeChild(chld);});
   $("#bgApplication").fadeOut(300,function(){prnt.removeChild(chld1);});
	
   return true;
}
