function toolTip(s,w) {
	this.insName = s;
	this.className = 'toolTip';
	this.o = document.createElement('DIV');
	this.s = this.o.style;
	this.o.style.width = (w ? w : 200);
	this.s.display = 'none';
	this.o.className = s;
	document.body.insertBefore(this.o, document.body.lastChild);
	this.x = 10;
	this.y = 10;

	function View(s) {
		if (s) this.o.innerHTML = s;
		this.s.top = event.clientY + this.y + document.body.scrollTop;
		this.s.left = event.clientX + this.x + document.body.scrollLeft;
		this.s.display = 'block';
	}
	this.View = View;

	function Hide() {
		this.s.display = 'none';
	}
	this.Hide = Hide;
}
