/*
@author     galih@gmail.com
@author     moe_zhank@yahoo.com
@version    0.1
@copyright  2010
@lib : table list js require jquery.js for now
*/

function DOM(typeObj,value){

   this.objParent = null;
   this.obj;

   this.createSimpleDOM = function(typeObj,value){
      var el = document.createElement(typeObj);

      if(!value){
      }else{
         el.appendChild(document.createTextNode(value));
      }

      return el;
   }

   if(typeObj)
      this.obj=this.createSimpleDOM(typeObj,value);

   this.createDOM = function(nama){
      var el = document.createElement(nama.typeObj);
      delete nama.typeObj;

      for(var i in nama){
         $(el).attr(i,nama[i]);
      }

      this.obj = el;
   }

   this.setAttribute = function(attribute, value){
      $(this.obj).attr(attribute, value);
   }

   this.setParent = function(objParent){
      this.objParent = objParent;
   }

   this.append = function(){
      if(this.objParent!=null)
         this.objParent.appendChild(this.obj);
   }

   this.appendTo = function(objParent){
      objParent.appendChild(this.obj);
   }

   this.appendText = function(text){
      this.obj.appendChild(document.createTextNode(text));
   }

   this.appendChild = function(obj){
      this.obj.appendChild(obj);
   }

   this.getObject = function(){
      return this.obj;
   }

}
