function setElementColor(element, type, color){

	if(type == "background-color") type = "timelineColor";
	
	$(element).css(type, color);
	
}