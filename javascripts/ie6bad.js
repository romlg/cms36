function ie6SetCookie(){
var today = new Date();
var expire = new Date();
expire.setTime(today.getTime() + 86400000);
document.cookie = "ie6suxxblocked=crap;expires="+expire.toGMTString();}
var ie6working=1;
if (document.cookie.length>0){
c_start=document.cookie.indexOf("ie6suxxblocked=");
if (c_start!=-1){ 
c_start=c_start + 15; 
c_end=document.cookie.indexOf(";",c_start);
if (c_end==-1) c_end=document.cookie.length;
if (unescape(document.cookie.substring(c_start,c_end)) =='crap')
ie6working=0;}}
function ie6move(dx,top,disp){
for (var i=0; i<document.all.length; i++){
if (document.all[i].id == 'ie6sux') continue;
if (document.all[i].style.position == 'absolute'){
t = parseInt(document.all[i].style.top);
if (!isNaN(t))
document.all[i].style.top = (t + dx) + 'px';}}
if (parseInt(document.body.runtimeStyle.paddingTop) == 0)
document.body.style.paddingTop = top+"px"; 
else
document.body.style.marginTop = top+"px"; 
document.getElementById('ie6sux').style.display = disp;}
function ie6(){
ie6move(25,0,'block');}
function ie6suxxblock(){
ie6SetCookie();
ie6move(-25,0,'none');}
if (ie6working){ 
window.attachEvent("onload", ie6);
document.write('<div id="ie6sux">\
У вас устаревший и небезопасный браузер. Советуем вам его&nbsp;<a href="http://kvartal2000.ru/upgradebrowser.html" target=_blank>Обновить</a>\<a class="close" href="javascript:ie6suxxblock()" title="закрыть">Закрыть</a></div>');}