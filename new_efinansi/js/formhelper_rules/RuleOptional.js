var type_optional = {
   '.optional_input': function(element) {
      element.isRequired = false,
      element.isValid = function() {
         //alert('validating optional element: ' + this.name + ' hasTypeFormat: ' + this.hasTypeFormat);
         if (this.hasTypeFormat == true)
            return this.isValidFormat();
         else
            return true;
      },
      element.getErrorMessage = function() {
         return '';
      },
      element.notifyUser = function() {
         //this.orgBorder = this.style.border;
         //this.style.border = '2px dotted red';
       this.orgBg = this.style.backgroundColor;
         this.style.backgroundColor = '#CCCCCC';
      },
      element.resetNotifyUser = function() {
         //this.style.border = this.orgBorder;
       this.style.backgroundColor = this.orgBg;
      }
   }
}

Behaviour.register(type_optional);