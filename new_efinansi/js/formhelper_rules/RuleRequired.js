var type_required = {
   '.required_input': function(element) {
      element.orgBg = element.style.backgroundColor,
      element.isRequired = true,
      element.hasTypeFormat = false,
      element.onblur = function() {
         if (this.value == '') {
            this.orgBg = this.style.backgroundColor;
            this.style.backgroundColor = '#FEFF99';
         } else
            this.style.backgroundColor = this.orgBg;
      },
      element.isValid = function() {
         //alert('validating required element: ' + this.name + ' hasTypeFormat: ' + this.hasTypeFormat);

         if (this.value != '') {
            if (this.hasTypeFormat)
               return this.isValidFormat();
            else
               return true;
         } else
            return false;
      },
      element.getErrorMessage = function() {
         if (!this.errorMessage)
            return 'Field ini wajib diisi';
         else
            return this.errorMessage;
      },
      element.notifyUser = function() {
         //this.orgBorder = this.style.border;
         //this.style.border = '2px dotted red';
         this.orgBg = this.style.backgroundColor;
         this.style.backgroundColor = '#FEFF99';
      },
      element.resetNotifyUser = function() {
         //this.style.border = this.orgBorder;
       this.style.backgroundColor = this.orgBg;
      }
   }
}

Behaviour.register(type_required);