function ShowMail(user, domain1, domain2) {
	var email = user+'&#64;'+domain1+'&#46;'+domain2;
	if (ShowMail.arguments[3]) name = ShowMail.arguments[3];
	else name = email;
	document.writeln('<a href="mailto:'+email+'">'+name+'</a>');
} 