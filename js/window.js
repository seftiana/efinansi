/*
@author     galih.xp@gmail.com
@version    0.0.1
@copyright  2010
*/
function jxWindow(location,jTitle,jValue,jMenu,jwNewId,jTop,idBlock){


   var jwObj = new Object();
   jwObj.location=location;
   jwObj.jTitle=jTitle;
   jwObj.jValue=jValue;
   jwObj.jMenu=jMenu;
   jwObj.idBlock=idBlock;

   var width;
   var height;
   var top;
   var left;
   var isFullScreen = false;
   var offsetleft;
   var offsettop;

   if(jTop=="")
      jTop=0;

   var jwNewId = jwNewId;

   if(jwObj.location=="")
      return false;

   var jWindow = '    <div class="jWindow" id="'+jwNewId+'"> '+
                 '    <div class="jWBorder"> '+
                 '       <div class="jWlborder"> '+
                 '          <div class="jWqIcon"><img src="images/popup/help.png" /></div> '+
                 '       </div> '+
                 '       <div class="jWtitle">'+jTitle+'</div> '+
                 '       <div class="jWrborder"> '+
                 '          <div class="jWfIcon"><img src="images/popup/fullscreen.png" /></div><div class="jWxIcon"><img src="images/popup/close.png" /></div> '+
                 '       </div> '+
                 '    </div> '+
                 '    <div class="jWMenu"></div> '+
                 '    <div class="jWContent">'+jValue+'</div> '+
                 '    </div>';

   $('#'+jwObj.location).append(jWindow);

   var jwBorder = $("#"+jwNewId).find('.jWBorder');
   var jWxIcon = $("#"+jwNewId).find('.jWxIcon');
   var jWfIcon = $("#"+jwNewId).find('.jWfIcon');

//   $("#"+jwNewId).css('left',($("#"+jwNewId).parent().width()-$("#"+jwNewId).width())/2);
//   $("#"+jwNewId).css('top',($("#"+jwNewId).parent().height()-$("#"+jwNewId).height())/2);
   $("#"+jwNewId).css('right',10);
   //$("#"+jwNewId).css('left',$("#"+jwNewId).parent().width()-($("#"+jwNewId).width()+10));
   $("#"+jwNewId).css('top',0-$("#"+jwNewId).height());
   $('#'+jwNewId).animate({top:jTop},500);
   $("#"+jwNewId).width(390);

   if (!parseInt(offsetleft)) offsetleft = 0;
   else offsetleft = parseInt(offsetleft);

   if (!parseInt(offsettop)) offsettop = 0;
   else offsettop = parseInt(offsettop);

   var scrollX = (document.documentElement) ? document.documentElement.scrollLeft : document.body.scrollLeft;
   var scrollY = (document.documentElement) ? document.documentElement.scrollTop : document.body.scrollTop;

   var screenWidth = (document.documentElement) ? document.documentElement.clientWidth : document.body.clientWidth;
   var screenHeight = (document.documentElement) ? document.documentElement.clientHeight : document.body.clientHeight;

   var xpos = scrollX + offsetleft + (screenWidth/2) - (width/2);
   var ypos = scrollY + offsettop + (screenHeight/2) - (height/2);

   $("#"+jwNewId).css('left',xpos+'px');
   $("#"+jwNewId).css('top',ypos+'px');

   jwBorder.mousedown(function(){
      $(this).parent().removeClass('firstWindow');
      $(this).parent().addClass('firstWindow');
      $(this).parent().draggable();
   });

   jwBorder.mouseup(function(){
      $(this).parent().draggable('disable');
   });

   jWxIcon.click(function(){
      $(this).parent().parent().parent().remove();

      $('#'+jwObj.idBlock).remove();
   });

   jWfIcon.click(function(){
      if(isFullScreen==false){
         width=$(this).parent().parent().next().next().css('width');
         height=$(this).parent().parent().next().next().css('height');
         top=$(this).parent().parent().parent().css('top');
         left=$(this).parent().parent().parent().css('left');

         $(this).parent().parent().next().next().animate({width:$(this).parent().parent().parent().parent().width()-10,height:$(this).parent().parent().parent().parent().height()-59});
         $(this).parent().parent().parent().animate({top:$(this).parent().parent().parent().parent().css('top'),left:$(this).parent().parent().parent().parent().css('left')});
         isFullScreen=true;
      }else{
         $(this).parent().parent().next().next().animate({width:width,height:height});
         $(this).parent().parent().parent().animate({top:top,left:left});
         isFullScreen=false;
      }

   });

   $(document).ready(Behaviour.apply(jwNewId));
   return jwObj;
}

function jsWindow(location,jTitle,jValue,jwId,jMenu){
   if(jwId==undefined){
      jwNewDate = new Date();
      jwNewId = jwNewDate.getTime();
   }else{
      jwNewId = jwId;
   }

   jWindow = new jxWindow(location,jTitle,jValue,jMenu,jwNewId,110);
}

function jsWindowAutoHide(location,jTitle,jValue,jwId,jMenu){
   if(jwId==undefined){
      jwNewDate = new Date();
      jwNewId = jwNewDate.getTime();
   }else{
      jwNewId = jwId;
   }

   setTimeout("hideWindow("+jwNewId+")", 5000);

   jWindow = new jxWindow(location,jTitle,jValue,jMenu,jwNewId,10);
}

function hideWindow(idWindow){
   wtop = 0-$("#"+idWindow).height();
   $('#'+idWindow).animate({top:wtop},500,'',function(){$('#'+idWindow).remove();});
}

function jsWindowModal(location,jTitle,jValue,jwId,jMenu){
      var body = document.body,
          html = document.documentElement;


      var height = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );
      var Width = Math.max( body.scrollWidth, body.offsetWidth, html.clientWidth, html.scrollWidth, html.offsetWidth );

      var idBlock = 'jwBlock';
      $(document.body).append('<div id="jwBlock"></div>');
      $('#jwBlock').css('width',Width);
      $('#jwBlock').css('height',height);
      $('#jwBlock').css('background','#000000');
      $('#jwBlock').css('top','0');
      $('#jwBlock').css('left','0');
      $('#jwBlock').css('position','absolute');
      $('#jwBlock').css('opacity','0.5');

   if(jwId==undefined){
      jwNewDate = new Date();
      jwNewId = jwNewDate.getTime();
   }else{
      jwNewId = jwId;
   }

   jWindow = new jxWindow(location,jTitle,"<div class='subcontent-element'>"+jValue+'</div>',jMenu,jwNewId,10,idBlock);
   //createNewWindow(location,jTitle,jValue,jMenu);
}

function jsWindowGetUrl(location,jTitle,url,jwId,jMenu){
   $.get(url,function(data){
      if(jwId==undefined){
      jwNewDate = new Date();
      jwNewId = jwNewDate.getTime();
   }else{
      jwNewId = jwId;
   }
      jWindow = new jxWindow(location,jTitle,"<div class='subcontent-element'>"+data+'</div>',jMenu,jwNewId,110);
   });
}

function jsWindowModalGetUrl(location,jTitle,url,jwId,jMenu){
   $.get(url,function(data){
      $('#content').append('<div id="jwBlock"></div>');
      $('#jwBlock').css('width',$('#content').width());
      $('#jwBlock').css('height',$('#content').height());
      $('#jwBlock').css('background','#000000');
      $('#jwBlock').css('top','0');
      $('#jwBlock').css('left','0');
      $('#jwBlock').css('position','absolute');
      $('#jwBlock').css('opacity','0.5');
      if(jwId==undefined){
         jwNewDate = new Date();
         jwNewId = jwNewDate.getTime();
      }else{
         jwNewId = jwId;
      }
         jWindow = new jxWindow(location,jTitle,"<div class='subcontent-element'>"+data+'</div>',jMenu,jwNewId);
   });
}
