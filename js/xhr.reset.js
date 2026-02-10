var xhr_reset_form = {
   '.xhr_simple_form': function(element) {
      element.onreset   = function(){
         var dest    = /\sdest_(.*?)\s/.exec(' '+this.className+' ');
         var url     = this.getAttribute('action');
         var target  = this.getAttribute('data-target');
         if(typeof(target) != 'undefined' && target != null){
            urlActions     = target;
         }else{
            urlActions     = url;
         }
         if(dest){
            GtfwAjax.replaceContentWithUrl(dest[1], urlActions + '&ascomponent=1');
         }

         return false;
      }
   },
   '.xhr_form': function(element) {
      element.onreset   = function(){
         var dest    = /\sdest_(.*?)\s/.exec(' '+this.className+' ');
         var url     = '?mod=home&sub=Home&act=view&typ=html';
         var target  = this.getAttribute('data-target');
         if(typeof(target) != 'undefined' && target != null){
            urlActions     = target;
         }else{
            urlActions     = url;
         }
         if(dest){
            GtfwAjax.replaceContentWithUrl(dest[1], urlActions + '&ascomponent=1');
         }

         return false;
      }
   }
}

Behaviour.register(xhr_reset_form);