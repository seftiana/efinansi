var type_http = {
   '.type_http' : function(element){
       element.hasTypeFormat = true,
       element.isValidFormat = function(){
         var httpPat=new RegExp();
         httpPat = /^http:\/\/(.*)/
         return httpPat.test(this.value);
      },
      element.getErrorMessage = function() {
         if (!this.errorMessage)
            return 'URL "' + this.value + '" is not valid.';
         else
            return this.errorMessage;
      }
   }
}

Behaviour.register(type_http);