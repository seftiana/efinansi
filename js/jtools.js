/**
Simple js Tools && js Element

@author Dyan Galih Nugroho Wicaksi(galih_xp@yahoo.com)
@version 0.1
@copyright 2008

@version 0.1.1
@author Gabri
@revision: perubahan style supaya bisa di baca oleh ie

**/

function jElement(){

   var obj = new Object();
   
   obj.theElement=null;
   obj.setElementOn=null;
   obj.style='';
   obj.attribute;
   obj.functionSet = undefined;
   obj.id=null;


   obj.delFunction = function(){
      var newElement = document.createElement(obj.theElement);
      newElement.onclick = "";
   }

   obj.createElement = function(){
      var newElement = document.createElement(obj.theElement);
      var insertSpot = document.getElementById(obj.setElementOn);
      
      for(i=0;i<obj.attribute.length;i++){
         arrAtr = obj.attribute[i].split('|'); 
         newElement.setAttribute(arrAtr[0],arrAtr[1]);
      }
      
      styles = obj.style.split(';');
      for (i = 0; style = styles[i]; i++)
      {
         style = style.split(':');
         styleName = style[0].replace(/^\s*\b(.*)\b\s*$/, '$1');
         styleValue = style[1].replace(/^\s*\b(.*)\b\s*$/, '$1');
         
         styleName = styleName.split('-');
         if (styleName.length > 1)
         {
            for (j = 1; j < styleName.length; j++) styleName[j] = styleName[j].charAt(0).toUpperCase() + styleName[j].substr(1);
            styleName = styleName.join('');
         }
         else styleName = styleName[0];
         try {newElement.style[styleName] = styleValue;}
         catch (e) {}
      }
      
      if(obj.functionSet!=undefined){
         newElement.onclick = new Function(obj.functionSet);
       }

      insertSpot.appendChild(newElement);
      obj.theElement = newElement;
   }

   addValueElement = function(id,addValue){
      var elemn = document.getElementById(id);
         elemn.value = elemn.value+addValue;
   }

   delElement = function(set,id){
      
      var deleteSpot = document.getElementById(set);

      if (deleteSpot.hasChildNodes()){
         var deleteElement = document.getElementById(id);
         
         deleteSpot.removeChild(deleteElement);
      }
   }

   obj.deleteElement = function(){
      var deleteSpot = document.getElementById(obj.setElementOn);

      if (deleteSpot.hasChildNodes()){
         var deleteElement = document.getElementById(obj.id);
         
         deleteSpot.removeChild(deleteElement);
      }
   }
   
   return obj;
}
