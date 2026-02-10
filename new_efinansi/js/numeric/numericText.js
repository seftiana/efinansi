/* Created AT Mulyana
This module contains script which handles input for Text box so that the only numeric input can be accepted
and also it will give denomination if needed.
TARGET ELEMENT : <INPUT TYPE=text     ...........>
*/ 

//Checks if string sItem exists inside string array arStr
function insideArray(arStr,sItem)
{
   var s = arStr.join("|~&*&~|");
   return (s.indexOf(sItem)!=-1)
}
//Gives denomination to sNumber and returns the result.
//Thus, sNumber must evaluate to number value
function giveDenomination(sNumber)
{
   if (sNumber=="") sNumber = "0";
   var s = demParseFloat(sNumber)+"";
   var sDec = "";
   if (s.indexOf(".")!=-1) {
      sDec = s.substring(s.indexOf("."),s.length);
      s = s.substring(0,s.indexOf("."));
   }
   var arNum = new Array();
   while (s.length > 3) {
      arNum[arNum.length] = s.substring(s.length-3,s.length);
      s = s.substring(0,s.length-3);
   }
   if (s.length > 0) arNum[arNum.length] = s;
   arNum.reverse();
   return (arNum.join()+sDec);
}
//Converts string sNumber to number
//sNumber is considered as number value with denomination
function demParseFloat(sNumber)
{
   var s = (sNumber+"").replace(new RegExp(",","g"), "");
   return parseFloat(s);
}
//========================================================================
//Only browsers that support event that is allowed to execute this script
if (document.addEventListener || (typeof window.event == "object")) {

//This array contains Id or name of the element to which the only numeric input must be given
window.ntArIdElm = new Array();
//This array contains Id or name of the element to which the denomination will be constructed
window.ntArDemId = new Array();
//Fills array ntArDemId and sets event handler of elements correspondig with Ids filled into ntArDemId
//This function doesn't define any parameters but because of the favor of JavaScript, the function can
//accept an arbitrary amount of parameters
function registerDemElmId()
{  
   for (i=0;i<arguments.length;i++) {
      var ar = new Array();
      ntArDemId[i] = arguments[i];
      if (document.all) {
         var ar1 = document.all(ntArDemId[i]);
         if (ar1.length) {
            for (j=0;j<ar1.length;j++) ar[j] = ar1[j];
         } else ar[0] = ar1;
      } else {
         var ar1 = document.getElementsByName(ntArDemId[i]);
         for (j=0;j<ar1.length;j++) ar[j] = ar1[j];
         if (document.getElementById(ntArDemId[i]))
            ar[ar.length] = document.getElementById(ntArDemId[i]);
      }
      for (j=0;j<ar.length;j++) {
         //ar[j].setAttribute("onkeyup","inputDenomination(this)"); //not work on IE
         ar[j].onkeyup = inputDenomination;
      }
   }
}
//Returns true if oElm is element of <INPUT TYPE=text  .......>
//and has right id or name
function validElement(oElm)
{
   if (oElm.tagName!="INPUT") return false;
   if (oElm.type!="text") return false;
   var s = " ";
   if (oElm.id) s = oElm.id;
   if (oElm.name) s = oElm.name;
   if (!insideArray(ntArIdElm,s)) return false;
   return true;
}
function inputDenomination()
{
   this.value = giveDenomination(this.value);
}
//Returns true if decimal char does not exist in oText and keyCode==keyValue (code for decimal char).
//It's to check whether decimal char is allowed for input
function decOK(oText,keyCode,keyValue)
{ 
  var decExist = (oText.value.indexOf(".")!=-1);
  var decPressed = (keyCode==keyValue);
  return (!decExist && decPressed);
}
function evKeyPressOnCostText_DOM(e)
{
   if (!e) e = window.event;
   if (!validElement(e.target)) return;
   var keyCode = e.keyCode;
   if (e.charCode && (e.charCode!=39)) keyCode = e.charCode;
   if ( ((keyCode < 48)||(keyCode > 57)) //non numeric
        && (keyCode != 8) //not backspace
        && (keyCode != 37) && (e.keyCode != 39) //not left-right arrow
		&& (e.keyCode != 46) //delete
        && !decOK(e.target,keyCode,46)
      ) {
       e.preventDefault();
       e.stopPropagation();
       return false;
   }
}
function evKeyPressOnCostText_IE4()
{
    var e = window.event;
	//alert('keypress');
    if (!validElement(e.srcElement)) return;
    if ( ((e.keyCode < 48)||(e.keyCode > 57)) // non numeric
         && !decOK(e.srcElement,e.keyCode,46) ) {
       e.returnValue=false;
       e.keyCode=0;
    }
}
function evKeyDownOnCostText_IE4()
{
    var e = window.event;
    if (!validElement(e.srcElement)) return;
    if ( ((e.keyCode < 48)||(e.keyCode > 57))  // non numeric
         && (e.keyCode != 8) // not backspace
         && (e.keyCode != 37) && (e.keyCode != 39) //not left-right arrow
		 && (e.keyCode != 46) //delete
         && !decOK(e.srcElement,e.keyCode,110)
         && (!decOK(e.srcElement,e.keyCode,190) && !e.shiftKey)
       ) {
       e.returnValue=false;
       e.keyCode=0;
    }
}
function evBlurOnCostText(e)
{
    if (!e) e = window.event;
    var target=null;
    if (e.target) target = e.target;
    else if (e.srcElement) target = e.srcElement;
    if (target) {
       if (!validElement(target)) return;
    } else return;
    if (target.value=="") target.value = "0";
    if (target.stopPropagation) target.stopPropagation();
}
function evFocusOnCostText(e)
{
    if (!e) e = window.event;
    var target=null;
    if (e.target) target = e.target;
    else if (e.srcElement) target = e.srcElement;
    if (target) {
       if (!validElement(target)) return;
    } else return;
    target.value = target.value;
    if (target.stopPropagation) target.stopPropagation();
}
	
if (document.addEventListener) {
   // We hope it will work on all "DOM level 2 event compliant" browsers.
   // The key codes are based on the Gecko browser has.
   document.addEventListener("keypress",evKeyPressOnCostText_DOM,true);
   document.addEventListener("blur",evBlurOnCostText,true);
   document.addEventListener("focus",evFocusOnCostText,true);
} else {
   // It is intended for IE 4 or higher version but it can be caught by
   // the other browser and the result we don\'t yet know
   document.onkeypress = evKeyPressOnCostText_IE4;
   // The key down event can be canceled as of IE 5. Formerly, only
   // key press event can be canceled. On the other hand, key press event
   // is only trigered by pressing a key that has ASCII code (except BACKSPACE???).
   // So, pressing arrow key will not generate key press event.
   document.onkeydown = evKeyDownOnCostText_IE4;
   document.onblur = evBlurOnCostText;
   document.onfocus = evFocusOnCostText;
}


} //if (document.addEventListener || window.event)