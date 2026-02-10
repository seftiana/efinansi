/*
@author     galih.xp@gmail.com
@version    0.0.1
@copyright  2010
*/

function notify (){

   var obj = new Object();

   obj.createWindow = function(title,text){
      setTimeout("jsWindow('content','"+title+"','"+text+"')", 10);
   }

   obj.send = function(title,text){
      GtfwNotify.createWindow(title,text);
   }

   return obj;
}

GtfwNotify = new notify();
