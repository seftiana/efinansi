/**
Simple js animation

@author Dyan Galih Nugroho Wicaksi(galih_xp@yahoo.com)
@version 1.0
@copyright 2008

**/
	
function janime(){
	
	var DOMCapable;
	var heightnow = 0;
	var interval = null;
	var opacity;
	var xNow;
	var yNow;

	this.interval = 10;
	this.speed = 10;
	this.minHeight = 0;
	this.maxHeight = 50;
   this.element;

	document.getElementById ? DOMCapable = true : DOMCapable = false;
	
	

	slideDownOpen =  function(speed,maxheight,element){
      obj = document.getElementById(element);
		if((element != null) && (maxheight != undefined)){
			if(DOMCapable){
				for(i=0;i<speed;i++){
					if(heightnow<maxheight){ 
						heightnow += 1;
						obj.style.height = heightnow+"px";
					}else{
						clearInterval(interval);
						return;
					}
				}
			}
		}else{
			clearInterval(interval);
			alert('no object to slide down');
			return;
		}
	}

	slideDownClose = function(speed,minheight,element){
      obj = document.getElementById(element);
		if(DOMCapable){
			for(i=0;i<speed;i++){
				if(heightnow>minheight){
					heightnow -= 1;
					obj.style.height = heightnow+"px";
				}else{
					clearInterval(interval);
					return;
				}
			}
		}
	}

	moveMeTo = function(x,y,element){
	   obj = document.getElementById(element);
		nbr = Math.floor((xNow - x)/3);
		
		if(Math.abs(nbr) != 0){
			xNow = xNow - nbr;
		}else{
			xNow = x;
		}
		
		
		obj.style.left = xNow+"px";
		obj.style.top = y+"px";
	
		if(x == xNow){	
			clearInterval(interval);
			return;
		};
	}

	setOpacity = function(opacity,element){
		//write by Johan Känngård and modified by me
		var o=document.getElementById(element).style;
		
		opacityValue = (opacity/100);
			
		o.opacity=opacityValue; //Opera
		o.MozOpacity=opacityValue; //Mozilla+Firefox
		o.KhtmlOpacity=opacityValue; //Konqueror
		o.filter="alpha(opacity="+opacity+")"; //IE

		
		return true;
	}

	jFadeOut = function(speed,element){
		for(i=0;i<speed;i++){
			opacity--;
			setOpacity(opacity,element);
			
			if(opacity<=0){
				clearInterval(interval);
				return;
			}
		}
	}

	jFadeIn = function(speed,element){
		for(i=0;i<speed;i++){
			opacity++;
			setOpacity(opacity,element);
	
			if(opacity>=100){
				clearInterval(interval);
				return;
			}
		}
	}

	

	this.slideOpen = function(){
		clearInterval(interval);
		slideStart = 'slideDownOpen('+this.speed+','+this.maxHeight+',"'+this.element+'")';
		interval = setInterval(slideStart,this.interval);
	};

	this.slideClose = function(){
		clearInterval(interval);
		slideStart = 'slideDownClose('+this.speed+','+this.minHeight+',"'+this.element+'")';
		interval = setInterval(slideStart,this.interval);
	};


	this.alpha = function (opacity){
		return setOpacity(opacity,this.element);
	};

	this.moveTo = function(x,y){
      obj = document.getElementById(this.element);
		xNow = (obj.style.left).substring(0,(obj.style.left).length-2);
		yNow = (obj.style.top).substring(0,(obj.style.top).length-2);

		xNow = new Number(xNow);
		yNow = new Number(yNow);
	
		//moveMeTo(x,y);
		clearInterval(interval);
		interval = setInterval('moveMeTo('+x+','+y+',"'+this.element+'")',this.interval);
	};
	
	this.fadeOut = function(){
		
		opacity = 100;
		clearInterval(interval);
		interval = setInterval('jFadeOut('+this.speed+',"'+this.element+'")',this.interval);
		
		//$("div").filter("."+this.element).fadeOut(this.speed);
	};

	this.fadeIn = function(){
		opacity = 0;
		clearInterval(interval);
		interval = setInterval('jFadeIn('+this.speed+',"'+this.element+'")',this.interval);
		
		//$("div").filter("."+this.element).fadeIn(this.speed);
	};
   
   this.setContent = function(content){
      var el = document.getElementById(this.element);
      el.innerHTML = content;
   }
}
