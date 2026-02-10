// http://blog.kung-foo.tv/archives/001614.html
var ballon_effect = {
   '#baloon': function (element) {
      element.AssociatedElement = '',
      element.getAssociatedElement = function() { return this.AssociatedElement },
      element.setAssociatedElement = function (elmName) {
         this.AssociatedElement = elmName;
      }
   },
   '.balloon': function(element) {
      element.isBalloonable = true,
      //element.AssociatedElement = '',
      element.hasBalloon = function () {
         // auto prepare balloon
         //alert('has ballooon');
         var balloon = document.getElementById("baloon");

         if (!balloon) {
            //alert('auto creating balloon element');
            // create a new elemnt
            var balloon = document.createElement('div');
            balloon.setAttribute("id","baloon");

            balloon.setAssociatedElement = function (elmName) { this.AssociatedElement = elmName; };
            balloon.getAssociatedElement = function() { return this.AssociatedElement };
            balloon.setAssociatedElement('');
            document.body.appendChild(balloon);
         }
         //alert(balloon.getAssociatedElement() == this.id);
         return balloon.getAssociatedElement() == this.id;
      },
      element.hideBalloon = function() {
         BaloonManager.hideBaloon();
      },
      element.showBalloon = function(msg) {
            if ( !document.getElementById ) return;

            var objID = this.getAttribute('id');
            //alert('balloon');
            if (this.hasBalloon()) {
               // fukin show it!
               var balloon = document.getElementById("baloon");
               balloon.style.visibility = 'visible';
               return false; // baloon is already there
            }

            var objX = this.findPosX();
            var objY = this.findPosY();

            var balloon = document.getElementById("baloon");
            balloon.setAssociatedElement(objID);

            //alert(balloon);

            if ( balloon ) {
               //alert('let us play');
               this.removePrevBalloon(balloon);
               var header = document.createElement('div');
               header.setAttribute("class", "header");

               var bbody = document.createElement('div');
               bbody.setAttribute("class", "body");

               var closeButton = document.createElement('div');
               closeButton.setAttribute("class", "closeButton");
               self = this;
               closeButton.onclick = function() {
                  BaloonManager.hideBaloon();
               }

               var bmsg = document.createElement('p');
               bmsg.innerHTML = msg;

               bbody.appendChild(closeButton);
               bbody.appendChild(bmsg);

               var footer = document.createElement('div');
               footer.setAttribute("class", "footer");

               // construct
               balloon.appendChild(header);
               balloon.appendChild(bbody);
               balloon.appendChild(footer);

               //bmsg.innerHTML = 'balloon.offsetHeight: ' + balloon.offsetHeight + ' balloon.clientHeight: ' + balloon.clientHeight + ' balloon.scrollHeight: ' + balloon.scrollHeight  + ' balloon.style.height: ' + balloon.style.height;

               balloon.style.visibility = 'visible';
               balloon.style.position = 'absolute';
               balloon.style.top = (objY - balloon.offsetHeight + 2) + 'px';
               balloon.style.left = objX - (190 /*bubble root*/ - this.offsetWidth) + 2 /*shift right, a bit*/ + 'px';

               //alert('balloon.offsetHeight: ' + balloon.offsetHeight + ' balloon.clientHeight: ' + balloon.clientHeight + ' balloon.scrollHeight: ' + balloon.scrollHeight  + ' balloon.style.height: ' + balloon.style.height);

            }
            return false;
      },
      element.findPosX = function() {
            obj = this;
            var curleft = 0;
            if (obj.offsetParent)
            {
               while (obj.offsetParent)
               {
                     curleft += obj.offsetLeft;
                     obj = obj.offsetParent;
               }
               if ( obj != null )
                     curleft += obj.offsetLeft;
            }
            else if (obj.x)
               curleft += obj.x;
            return curleft;
      },
      element.findPosY = function() {
            var curtop = 0;
            obj = this;
            if (obj.offsetParent)
            {
               while (obj.offsetParent)
               {
                     curtop += obj.offsetTop;
                     obj = obj.offsetParent;
               }
               if ( obj != null )
                     curtop += obj.offsetTop;
            }
            else if (obj.y)
               curtop += obj.y;
            return curtop;
      },
      element.removePrevBalloon = function(obj) {
         if ( obj.childNodes ) {
            //alert(obj.childNodes);
            while ( obj.childNodes.length > 0 ) {
               obj.removeChild(obj.childNodes[0]);
            }
         }
      },
      element.hideBalloon() /*,*/
      /* check for validity, elemnt must have ID!
      if (!this.getAttribute('id')) {
         alert("Error on element: " + this.getAttribute('name') + "\nbaloonable element MUST have id");
         this.style.border = "5px solid red";
      }*/
   }
}

function BaloonManagerClass() {};

BaloonManagerClass.prototype = {
   hideBaloon: function() {
         var balloon = document.getElementById("baloon");
         if (balloon)
            balloon.style.visibility = 'hidden';
   }
}

var BaloonManager = new BaloonManagerClass();
Behaviour.register(ballon_effect);