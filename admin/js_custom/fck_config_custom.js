//##
//## Style Names
//##
config.StyleNames  = ';������� ����� (8px);������� ����� (10px);������� ����� (13px);������� ����� (16px);����� ����� (8px);����� ����� (10px);����� ����� (13px);����� ����� (16px);������� ����� (8px);������� ����� (10px);������� ����� (13px);������� ����� (16px);��������� 1;��������� 2;��������� 3;��������� 4;��������� 5;��������� 6;' ;
config.StyleValues = ';text_small;text_middle;text_big;text_very_big;text_small_blue;text_middle_blue;text_big_blue;text_very_big_blue;text_small_red;text_middle_red;text_big_red;text_very_big_red;text_title1;text_title2;text_title3;text_title4;text_title5;text_title6;' ;
config.EditorAreaCSS = 'main.css';


config.ToolbarSets   = new Object() ;
config.downToolbarSets = new Object() ;
config.ToolbarSets["Default"] = [
	['Cut','Copy','Paste','PasteText','PasteWord','-','Undo','Redo','-','SelectAll','RemoveWordF','-','Link','RemoveLink','Anchor','-','Image','Table','Rule','SpecialChar',],
	['FontStyle','Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyFull','-','InsertOrderedList','InsertUnorderedList','-','Outdent','Indent',],
	['FontStyleAdv','-','FontFormat','-','Font','-','FontSize','-','TextColor','BGColor']] ;


config.ToolbarSets["Common"] = [
	['Cut','Copy','Paste','PasteText','PasteWord','-','Undo','Redo','-','SelectAll','RemoveWordF','-','Link','RemoveLink','Anchor','-','Image','Table','Rule','SpecialChar',],
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyFull','-','InsertOrderedList','InsertUnorderedList','-','Outdent','Indent',],
	['FontStyle','-','Font','-','FontSize']] ;
config.downToolbarSets["Common"] = [
	['EditSource','ShowDetails','ShowTableBorders','-','Zoom','Preview','Find','Replace'] ,
] ;

FCKConfig.ToolbarSets["Common"] = [
	['Source','-','Templates'],
	['Cut','Copy','Paste','PasteText','PasteWord','-','Print'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	['Bold','Italic','Underline','StrikeThrough'],	
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull','-','Outdent','Indent'],
	['Link','Unlink','Anchor','OrderedList','UnorderedList','Image','Table','TextColor','BGColor'],
	['FontFormat','FontSize']
] ;
