/*
   AdvAjax helper to solve problem with multiple submit button. This rule will disable all submit button except one clicked
*/

var advajax_helper = {
   'input[type="submit"]': function (element) {

       if (!element.className || !element.className.match(new RegExp('\\badvajax_helper_skip\\b'))) {
            element.onclick = function(){
               this.disableAllButThis(this);
            }
         }

      element.disableAllButThis = function(obj) {
         var the_exception_obj = obj;
         while(obj.parentNode) {
            /*console.log('Lookign for pNode', obj.parentNode.tagName);*/
            if (obj.parentNode.tagName == 'FORM') {
               var frm = obj.parentNode;
               var elmnts = frm.getElementsByTagName('input');
               for(var i=0;i<elmnts.length;i++) {
                  /*console.log(elmnts[i]);*/
                  if (elmnts[i].type=="submit") {
                     if ((elmnts[i].name==the_exception_obj.name) && (elmnts[i].value==the_exception_obj.value))
                        continue;
                     else {
                        /*elmnts[i].style.border = "2px solid red";*/
                        elmnts[i].disabled=true;
                     }
                  }
               }

               /* done */
               break;
            } else {
               /* go up */
               obj = obj.parentNode;
            }
         }
      };
   }
}

Behaviour.register(advajax_helper);
