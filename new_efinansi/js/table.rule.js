var table_content = {
   ".table-common" : function(element){
      objTr = $(element).find('tr');

      $(objTr).mouseover(function(){
         classAttr = $(this).attr("class");
         if (!$(this).hasClass('initialized') || !(this).hasClass('collapsed')){
         $(this).removeClass(classAttr);
         }
         $(this).addClass('hoverTd');
         $(this).addClass(classAttr+'_none');
      });

      $(objTr).mouseout(function(){
         $(this).removeClass('hoverTd');
         classAttr = $(this).attr("class");
         classAttr = classAttr.replace("_none","");
         $(this).addClass(classAttr);
      });
   }
}

Behaviour.register(table_content);
