/**
Simple js zoom

@author Dyan Galih Nugroho Wicaksi(galih_xp@yahoo.com)
@version 1.0
@copyright 2008

**/
	
var jzoom = {
	'.xzoom' : function(element){
	   element.onmouseover = function(){
	      var newHeight = $(this).height();
	      var newWidth = $(this).width();
	      $(this).animate({height: newHeight+20, width: newWidth+20,}, 300);
      },
      element.onmouseout = function(){
	      var newHeight = $(this).height();
	      var newWidth = $(this).width();
	      $(this).animate({height: newHeight-20, width: newWidth-20,}, 300);
      }
   }
}
Behaviour.register(jzoom);
