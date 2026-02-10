var tooltip_content = {
   ".gtToolTip" : function(element){
      $(element).mouseover(function(){
         $(this).prepend("<span id='tooltip'>"+$(this).attr("title")+"</span>");
         $(this).removeAttr("title");
      });

      $(element).mouseout(function(){
         $(this).attr("title",$("#tooltip").html());
         $("#tooltip").remove();
      });
   }
}

Behaviour.register(tooltip_content);
