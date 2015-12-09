/**
 * Cite plugin by xfox@rusoft.ru
 * $Id: fckplugin.js,v 1.2 2009-03-12 14:04:34 konovalova Exp $
 *
 * update by xscar@rusoft.ru
 * works perfectly in firefox2.0
 * guess what about IE :>
 *
 * update by konovalova@rusoft.ru
 * an now it works is IE too!   
 */

FCKCiteCommand = function()
{
	this.Name = 'Cite' ;
}

/* updated module */
FCKCiteCommand.prototype.Execute = function()  
{  
    var myText = '';
	if(FCKBrowserInfo.IsIE)  
	{  
		//triggered from IE.
		if (FCK.EditorWindow.getSelection)
		{
		    myText = FCK.EditorWindow.getSelection();
		}
		else if (FCK.EditorWindow.document.getSelection)
		{
		    myText = FCK.EditorWindow.document.getSelection();
		}
		else if (FCK.EditorWindow.document.selection)
		{
		    myText = FCK.EditorWindow.document.selection.createRange().text;
		}
		if (myText == '') myText = 'cite';
	}
	else  
	{  
		//triggered from Firefox.		
		mySelection = FCK.EditorWindow.getSelection() ;
		myText = mySelection != '' ? mySelection : 'cite';
	}  

	if (FCKConfig.BlockquoteCode != undefined) {
	    var code = FCKConfig.BlockquoteCode;
	    FCK.InsertHtml(code.replace("{text}", myText));
	} else {
	    FCK.InsertHtml("<blockquote>"+myText+"</blockquote>");
	}
}
/* end of update */

FCKCiteCommand.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF ;
}


FCKCommands.RegisterCommand( 'Cite', new FCKCiteCommand() ) ;
var oCiteItem = new FCKToolbarButton( 'Cite', FCKLang.CiteBtn ) ;

oCiteItem.IconPath = FCKPlugins.Items['cite'].Path + 'cite.gif' ;

FCKToolbarItems.RegisterItem( 'Cite', oCiteItem ) ;