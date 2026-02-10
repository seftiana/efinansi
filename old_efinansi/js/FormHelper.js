/**
FormHelper
A library to aid scripted binding, to enhance simple_validator
Goal: enhance server side validation mechanism

@author Akhmad Fathonih (akhmadf@gmail.com)
@version 0.1
@copyright 2006
@see simple_validator.js
*/

FormHelper = function (frmName) {
   frm = document.getElementsByName(frmName);

   if (frm) {
      if (frm.length > 1)
         alert("Cannot handle multiple form, yet.");
      else {
         this.frmObj = frm.item(0);
         if (!(/\bstd_form\b/.test(this.frmObj.className)))
            this.frmObj.className = this.frmObj.className + ' std_form';
         //alert(this.frmObj.className);
      }
   } else {
      alert("Unable to locate form " + frmName + ". Validation rules will not be applied");
   }

   //alert(this.frmObj);
}

FormHelper.prototype.hasValidationType = function(obj, valType) {
   var regexp1 = new RegExp("^" + valType + "\\s+");
   var regexp2 = new RegExp("\\s+" + valType + "\\s+");
   var regexp3 = new RegExp("\\s+" + valType + "$");
   var regexp4 = new RegExp("^" + valType + "$");

   //alert(regexp.test(obj.className));
   return regexp1.test(obj.className) || regexp2.test(obj.className) || regexp3.test(obj.className) || regexp4.test(obj.className);
}

FormHelper.prototype._addValidation = function (elmName, valType) {
   //alert(this.frmObj);

   var obj1 = this.frmObj.getElementsByTagName('input');
   var obj2 = this.frmObj.getElementsByTagName('select');
   var obj3 = this.frmObj.getElementsByTagName('textarea');

   if (obj1) {
      this.__addValidation(obj1, elmName, valType);
   }

   if (obj2) {
      this.__addValidation(obj2, elmName, valType);
   }

   if (obj3) {
      this.__addValidation(obj3, elmName, valType);
   }
}

FormHelper.prototype.__addValidation = function(obj, elmName, valType) {
   for(i=0;i<obj.length;i++) {
      if ((obj[i].name == elmName) && (!this.hasValidationType(obj[i], valType))) {
         obj[i].className = obj[i].className + ' ' + valType;
         //alert("added validation("+valType+") to " + obj[i].name + " class= " + obj[i].className);
      }
   }
}

FormHelper.prototype._addCustomError = function (elmName, err_msg) {
   obj = this.frmObj.getElementsByTagName('input');
   if (obj) {
      for(i=0;i<obj.length;i++) {
         //alert("_addCustomError checking object: " + obj[i].name + " type:" + obj[i].type);
         if (obj[i].name == elmName) {
            //alert("_addCustomError to " + obj[i].name + " error= " + err_msg);
            obj[i].errorMessage = err_msg;
         }
      }
   }
}

/* required_input */
FormHelper.prototype.addRequired = function(elmName) {
   this._addValidation(elmName, "required_input");
}

FormHelper.prototype.isRequiredObj = function (obj) {
   return this.hasValidationType(obj, "required_input" );
}

/* optional_input */
FormHelper.prototype.addOptional = function(elmName) {
   this._addValidation(elmName, "optional_input");
}

FormHelper.prototype.isOptionalObj = function (obj) {
   return this.hasValidationType(obj, "optional_input" );
}

/* type_number */
FormHelper.prototype.addTypeNumber = function(elmName) {
   this._addValidation(elmName, "type_number");
}

FormHelper.prototype.hasTypeNumber = function (obj) {
   return this.hasValidationType(obj, "type_number" );
}

/* type e-mail */
FormHelper.prototype.addTypeNumber = function(elmName) {
   this._addValidation(elmName, "type_email");
}

FormHelper.prototype.hasTypeNumber = function (obj) {
   return this.hasValidationType(obj, "type_email" );
}

/* custom error suppor */
FormHelper.prototype.setCustomErrorMessage = function(elmName, err_msg) {
   this._addCustomError(elmName, err_msg);
}

var FormHelperManager = {
   /* stolen from behaviour library */
   addLoadEvent : function(func){
      var oldonload = window.onload;

      if (typeof window.onload != 'function') {
         window.onload = func;
      } else {
         window.onload = function() {
            oldonload();
            func();
         }
      }
   }
}