var custom_js    = {
   '.change_object' : function(element){
      element.onchange = function(e){
         var id         = this.options[this.selectedIndex].value;
         var action     = this.getAttribute('data-target');
         var object     = this.getAttribute('data-object');
         console.log(object);
         $.ajax({
            type:"POST",
            url:action,
            data:  { 'id': id, 'object': object},
            dataType : "json"
         }).done(function(data){
            eval(data.exec);
         }).fail(function(){
            removeOptionsAlternate(object);
         });
         e.preventDefault();
         return false;
      }
   },
   '.input_mask' : function(elem){
      var mask = elem.getAttribute('data-mask');
      if(!mask){
         mask  = '99-99-9999';
      }
      $(elem).inputmask(mask);
   },
   '.set_active' : function(element){
      element.onclick = function(e){
         var action     = this.getAttribute('href');
         var id         = this.getAttribute('data-id');
         var container  = this.parentNode;
         var imageLoader   = document.createElement('img');
         imageLoader.src   = 'images/icons/16/ajax-loader-2.gif';
         imageLoader.width = 16;
         var imageWarning     = document.createElement('img');
         imageWarning.src     = 'images/icons/16/lamp-red.gif';
         imageWarning.width   = 16;
         this.innerHTML       = '';
         this.appendChild(imageLoader);

         $.ajax({
            type:"POST",
            url:action,
            data:  { 'id': id},
            dataType : "json"
         }).done(function(data){
            eval(data.exec);
         }).fail(function(){
            this.innerHTML       = '';
            this.appendChild(imageWarning);
         });
         e.preventDefault();
         return false;
      }
   }
}

Behaviour.register(custom_js);