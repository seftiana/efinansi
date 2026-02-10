var type_number = {
   '.type_number' : function(element){
      element.hasTypeFormat = true, // mandatory .. a marker for optional optional_input adn required_input
      // check on leave event
      element.isValidFormat = function(){
         if (this.value != '' ) // only chek if it's not empty
            return !isNaN(this.value);
         else
            return true;
      },
      element.getErrorMessage = function() {
         if (!this.errorMessage)
            return '"' + this.value + '" bukan angka.' + "\nFormat data harus berupa angka";
         else
            return this.errorMessage;
      }
   }
}

Behaviour.register(type_number);