function select_all(formid,tagname,name,value) {
   formblock= document.getElementById(formid);
   //alert(formblock.id);
   forminputs = formblock.getElementsByTagName(tagname);
   //forminputsa = document.getElementById(tagname);
   alert(forminputs.name);
   for (i = 0; i < forminputs.length; i++) {
      // regex here to check name attribute
      var regex = new RegExp(name, "i");
      if (regex.test(forminputs[i].getAttribute('name'))) {
         if (value == '1') {
            forminputs[i].checked = true;
         } else {
            forminputs[i].checked = false;
         }
      }
   }
   alert('helo');
}  
