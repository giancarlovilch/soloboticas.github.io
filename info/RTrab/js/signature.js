// $Id: $
	function zf_SetCanvasElemWidth(linkName){
    	var canvasElem = document.getElementById("drawingCanvas-"+linkName);
		if(canvasElem !== null && canvasElem.nodeName=='CANVAS')
		{
			var imgElem  = document.getElementById("img-"+linkName);
			var signContainerSignature = document.getElementById("signContainer-"+linkName);
			canvasElem.width = signContainerSignature.parentNode.offsetWidth;
			if(imgElem){				        		
				imgElem.setAttribute("class","signImg");// No I18N
			}
		}
	}

	function zf_AddEventListenersToCanvas(canvasElem){
			canvasElem.addEventListener("mousedown",function (event) {					       
		        var context = canvasElem.getContext("2d");// No I18N
				var position = zf_GetXYCoords(event, canvasElem,false);
				context.moveTo(position.X, position.Y);
				context.beginPath();
				var startScribble = function (event) {
					zf_StartScribbling(event, canvasElem, context,false);
				}
				var endScribble = function (event) {
					zf_EndScribbling(event, canvasElem, context,startScribble,endScribble);
				}
				this.addEventListener("mousemove",startScribble,false);
				this.addEventListener("mouseup",endScribble,false);
				this.addEventListener("mouseout",endScribble,false);
				this.addEventListener('touchstart', function(event){
					position = zf_GetXYCoords(event, canvasElem,true);
					context.moveTo(position.X, position.Y);
					context.beginPath();
					event.preventDefault();
				}, false);
				this.addEventListener('touchmove', function(event){
					zf_StartScribbling(event, canvasElem, context,true);
					event.preventDefault();
				}, false);
		    });
                            
        var userAgent = window.navigator.userAgent;
        var msie = userAgent.indexOf("MSIE ");
        if (msie > 0) {
        	this.addEventListener("keypress",function (event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                }
            });
        }
	}
	
	function zf_ClearSignature(signElemID){
        var signatureElem = document.getElementById(signElemID);
        var signContext = signatureElem.getContext("2d");// No I18N
		signContext.clearRect(0, 0, signatureElem.width, signatureElem.height);
	}
	
	function zf_GetXYCoords(event, signatureElem,isTouchEvent) {
		var xCoordinate, yCoordinate;
		var signElemLeftPos = zf_GetOffsetLeft(signatureElem);
		var signElemTopPos  = zf_GetOffsetTop(signatureElem);

		var xPosition,yPosition;
		if(!isTouchEvent){
			xPosition = event.clientX;
			yPosition = event.clientY;
		}else{
			xPosition = event.changedTouches[0].clientX;
			yPosition = event.changedTouches[0].clientY;
		}
		
		if (!isTouchEvent && (event.pageX || event.pageY)) {
			xCoordinate = event.pageX;
			yCoordinate = event.pageY;
		} else if (isTouchEvent && (event.changedTouches[0].pageX || event.changedTouches[0].pageY)) {
			xCoordinate = event.changedTouches[0].pageX;
			yCoordinate = event.changedTouches[0].pageY;
		}else {
			xCoordinate = xPosition + document.body.scrollLeft + document.documentElement.scrollLeft;
			yCoordinate = yPosition + document.body.scrollTop + document.documentElement.scrollTop;
		}
		var xVal = xCoordinate - signElemLeftPos;
		var yVal = yCoordinate - signElemTopPos;
		return { X: xVal, Y: yVal};
	}

	function zf_GetOffsetLeft(signElem){
		var rect = signElem.getBoundingClientRect();
		var offSetLeft = document.body.scrollLeft;
		if( offSetLeft == 0 ){
			offSetLeft = offSetLeft + rect.left + document.documentElement.scrollLeft;
		}else{
			offSetLeft = offSetLeft + rect.left;
		}
		return offSetLeft;
	}

	function zf_GetOffsetTop(signElem){
		var rect = signElem.getBoundingClientRect();
		var offSetTop = document.body.scrollTop;
		if( offSetTop == 0 ){
			offSetTop = offSetTop +rect.top + document.documentElement.scrollTop;
		}else{
			offSetTop = offSetTop + rect.top;
		}
		return offSetTop;
	}	

	function zf_StartScribbling(event, canvasElem, context,isTouchEvent) {
		var position = zf_GetXYCoords(event, canvasElem,isTouchEvent);
		context.lineTo(position.X, position.Y);
		context.stroke();
		context.strokeStyle="#000000"; // No I18N
	}
	function zf_EndScribbling(event, canvasElem, context,startScribleEventListn,endScribleEventListn) {
		context.closePath();
		canvasElem.removeEventListener("mousemove",startScribleEventListn);
		canvasElem.removeEventListener("mouseup",endScribleEventListn);
		canvasElem.removeEventListener("mouseout",endScribleEventListn);
	}
	     
