<?php

class TAboutSite {

	function show(&$params) {
		$ret['text'] = "<P>Сайт разработан в 2008 г. по заказу компании \"Kvartal2000\".</P><A href=\"http://www.rusoft.ru\">
		<img src=\"/images/rusoft_logo.gif\" align=left border=0></a>
		<p><a href=\"http://www.rusoft.ru\">Разработка сайта</a>, <a href=\"http://www.rusoft.ru/services/cms/\">система управления контентом</a>, 
		<a href=\"http://www.rusoft.ru/services/hosting/\">хостинг</a> - компания RuSoft</a>
		<br clear=all>Материалы сайта - компания \"Kvartal2000\".</p>
		<p>
		Содержание данного сайта, включая, но не ограничиваясь, текстовыми и графическими материалами, защищены авторскими правами. Они не 
		подлежат частичному или полному размножению, опубликованию или сохранению в информационной системе без предварительного письменного согласия автора (компании \"Kvartal2000\").</p>";
		return $ret['text'];
	}
}

?> 