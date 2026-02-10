/*
@author     galih@gmail.com
@version    0.1
@copyright  2010
@lib : table list js require jquery.js
*/

function GtfwTable(){
   var obj = new Object();
   /*wajib diisi*/
   obj.idTable = "gtfw";
   obj.idParent = "";
   obj.parentObj=null;
   obj.jumlahRecord = 0;
   obj.classTable = "";
   obj.columnLenght;
   obj.columnStyle=new Array();


   obj.createHeader = function(arrColumn,objParent){
      obj.columnLenght = arrColumn.length;
      var tableRow = "<thead><tr>";
      for(i=0;i<arrColumn.length;i++){
         attr = "";
         if(obj.columnStyle[i]!=undefined)
            attr = "style='"+obj.columnStyle[i]+"'";

         tableRow += "<th "+attr+">"+arrColumn[i]+"</th>"
      }

      //tableRow +="<td>Aksi</td></tr>";
      tableRow += "</thead>";

      $('#'+obj.idTable).append(tableRow);
      return tableRow;
   }

   obj.createBody = function(){
      return $("#"+obj.idTable).append("<tbody></tbody>");
   }

   obj.setEmptyRecord = function(){
      parentObj=$('#'+obj.idTable).find("tbody");

      $(parentObj).html('<tr><td colspan="'+obj.columnLenght+1+'" align="center"><em>-- Data tidak ditemukan --</em></td></tr>');
   }

   obj.checkData = function(objParent){
      if($(objParent).html()=="")
         obj.setEmptyRecord(objParent);
   }

   obj.removeRow = function(thisObj){
      obj.jumlahRecord -=1;
      objParent = $(thisObj).parent().parent().parent();
      $(thisObj).parent().parent().remove();
      obj.checkData(objParent);
   }

   obj.insertDataRow = function(arrColumn){

      if(obj.jumlahRecord==0){
         obj.cleanAllRecord();
      }
      obj.jumlahRecord +=1;
      tableRow ="<tr>";
      for(i=0;i<arrColumn.length;i++){
         valueColumn = arrColumn[i];
         columnPos = obj.idTable+"_"+obj.jumlahRecord+"_"+i;
         tableRow += "<td style='"+obj.columnStyle[i]+"' id='"+columnPos+"'>"+valueColumn+"</td>"
      }
      tableRow +="</tr>";

      bodyObj=$('#'+obj.idTable).find("tbody");

      $(bodyObj).append(tableRow);
      //$('#'+idParent).append(tableRow);
   }

   obj.cleanAllRecord = function(){
      $('#'+obj.idTable).find("tbody").html("");
   }

   obj.createTable = function(){
      tableString="<table id='"+obj.idTable+"'>";
      tableString+="</table>";
      obj.parentObj = $(obj.parentObj).append(tableString);
   }

   obj.createTableList = function(attr){
      if(attr==undefined)
         attr="";
      tableString="<table id='"+obj.idTable+"' class=\"table-common\" "+attr+">";
      tableString+="</table>";
      obj.parentObj = $(obj.parentObj).append(tableString);
   }

   obj.setData = function(objHeader,dataJson){
      for(var j=0;j<dataJson.length;j++){
         if(obj.jumlahRecord==0){
            obj.cleanAllRecord();
         }
         obj.jumlahRecord +=1;
         tableRow ="<tr>";
         for(i=0;i<objHeader.length;i++){
            columnPos = obj.idTable+"_"+obj.jumlahRecord+"_"+i;
            valueColumn = dataJson[j][objHeader[i]];
            tableRow += "<td style='"+obj.columnStyle[i]+"' id='"+columnPos+"'>"+valueColumn+"</td>"
         }

         tableRow +="</tr>";

         bodyObj=$('#'+obj.idTable).find("tbody");

         $(bodyObj).append(tableRow);
      }
   }

   return obj;
}
