function ShowMail(user, domain1, domain2) {
	var email = user+'&#64;'+domain1+'&#46;'+domain2;
	if (ShowMail.arguments[3]) name = ShowMail.arguments[3];
	else name = email;
	document.writeln('<a href="mailto:'+email+'">'+name+'</a>');
}

function openImage(image) {
	if (image) window.open("/popup.php?img="+image, "popupimage", "scrollbars=1, resizable=1, width=750, height=500").focus();
	return false;
}


function MM_findObj(n, d) { 
  	var p,i,x;  
	if(!d) d=document; 
	if((p=n.indexOf("?"))>0&&parent.frames.length) {
	    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);
	}
	if(!(x=d[n])&&d.all) x=d.all[n]; 
	for (i=0;!x&&i<d.forms.length;i++) 
		x=d.forms[i][n];
  	for(i=0;!x&&d.layers&&i<d.layers.length;i++) 
		x=MM_findObj(n,d.layers[i].document);
	if(!x && d.getElementById) 
		x=d.getElementById(n); 
	return x;
}


function preload() {
	b=preload.arguments; 
	preload_images = new Array();
	for(i=0; i<b.length; i++) {
		preload_image = new Image;
		preload_image.src=b[i];
		preload_images[i]=preload_image;
	}
}
