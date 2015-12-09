// получение типа броузера
var agent=navigator.userAgent.toLowerCase();
// получение версии броузера
var ver=navigator.appVersion;
// определение броузеров Opera
var op=agent.indexOf("opera")>-1 && window.opera;
// определение броузеров Internet Explorer
var ie = (agent.indexOf("msie")>-1 && !op);
// определение броузеров на движке Gecko (Mozill'a ,Netscape)
var mz=(agent.indexOf("gecko")>-1 || window.sidebar);

// Сообщения
var msg_wait = "Для просмотра панорамного изображения необходим iPIX-плагин.\n Плагин будет автоматически загружен в течение 1-5 минут.\nДождитесь загрузки и нажмите Yes в появившемся окне";
var msg_enable_java = "Для просмотра панорамного изображения необходимо в настройках Вашего браузера разрешить выполнение java-апплетов\n";
var msg_install_java = "Для просмотра панорамного изображения необходимо установить Java-машину";


  function pluginDetected() {
    if (ie) { 
      document.writeln('<SCRIPT LANGUAGE="VBScript"\>');
      document.writeln('on error resume next');
      document.writeln('ipix1 = IsObject(CreateObject("IPIX.Viewers.5"))');
      document.writeln('ipix2 = IsObject(CreateObject("IPIX.ActiveXCtrl.2"))');
      document.writeln('ipix3 = IsObject(CreateObject("IPIX.ActiveXCtrl.5"))');
      document.writeln('If ipix1 = True or ipix2 = True or ipix3 = True Then');
      document.writeln('ipixmode = 1');
      document.writeln('Else');
      document.writeln('ipixmode = 0');
      document.writeln('End if');
      document.writeln('</SCRIPT\>');
      return ipixmode;
    } else if (mz) { 
      //navigator.plugins.refresh(true);
      numPlugins = navigator.plugins.length;
      if (numPlugins > 0) {
        for (k = 0; k < numPlugins; k++) {
          plugin = navigator.plugins[k];
          if (plugin.description.indexOf("IPIX") != -1) {
            numTypes = plugin.length;
            for (j = 0; j < numTypes; j++) {
              mimetype = plugin[j];
              if (mimetype)       {
                if (mimetype.enabledPlugin && (mimetype.suffixes.indexOf("ipx") != -1)) {
                  return 1;
                }
              }
            }
          }
        }
      }
      return 0;
    }
    return 0;
  }

  function putIPX(fname,width,height){
//	  alert('put iPIX');
    if (ie) {
      document.write('<object id="IpixX1" width="' + width + '" height="' + height + '"');
      document.write('        classid="clsid:11260943-421B-11D0-8EAC-0000C07D88CF"');
      document.write('        codebase="http://www.ipix.com/download/installers/axIpx32.exe" name="ipix" vspace="0" hspace="0"');
      document.write('        border="0">');
      document.write('        <param name="_Version" value="65536">');
      document.write('        <param name="_ExtentX" value="11924">');
      document.write('        <param name="_ExtentY" value="9278">');
      document.write('        <param name="_StockProps" value="0">');
      document.write('        <param name="IPXFILENAME" value="' + fname +'">');
      document.write('</object>');
    } else if (mz) {
      document.writeln('      <embed src="' + fname + '"');
      document.writeln('        width="' + width + '" height="' + height + '" palette="FOREGROUND"');
      document.writeln('        type="application/x-ipix"');
      document.writeln('        pluginspage="http://www.ipix.com/cgi-bin/download.cgi"');
      document.writeln('        name="ipix" vspace="0" hspace="0" border="0"');
      document.writeln('        _version="65536" _extentx="11924" _extenty="9278"');
      document.writeln('        _stockprops="0" ipxfilename="' + fname + '">');
      document.writeln('      </embed>');
    }
  }

    function IfJavaInstalled() {
                 //alert('aa');
       var str, oClientCaps;
       if (mz || op) return true;
       oClientCaps = document.body;
       oClientCaps.style.behavior = "url(#default#clientCaps)";
       bMSvmAvailable = oClientCaps.isComponentInstalled("{08B0E5C0-4FCB-11CF-AAA5-00401C608500}","componentid");
       return (bMSvmAvailable) ? true : false;
    }

  function putJava(fname,width,height){
//	  alert('pu java');
    document.write('<applet archive="IpixViewer.jar" name="IpixViewer" code="IpixViewer.class" codebase="admin/third/ipix/"');
    document.write('        width="' + width + '" height="' + height + '" vspace="0" hspace="0">');
    document.write('        <param name="URL" value="' + fname + '">');
    document.write('        <param name="SpinSpeed" value="5">');
    document.write('        <param name="SpinStyle" value="flat">');
    document.write('        <param name="Toolbar" value="off">');
    document.write('</applet>');
  }

  function PutInstall(width,height) {
    var str;
    str = '   <div width="' + width + '" height="' + height + '" align=left valign=top style="padding:10px;">';
    str += ipx_install_msg;
    str += '   </div>';
    document.write(str);
    }

  function putError(message,width,height) {
    document.writeln('        <table width="'+width+'" cellpadding=0 cellspacing=0 border=0><tr>');
    document.writeln('        <td><img src="img/empty.gif" width=1 height='+height+'></td>');
    document.writeln('        <td><b>Ошибка:</b>' + message + '</b></td>');
    document.writeln('        </tr></table>');
  }

  function putipx(fname,width,height, msg) {

    if ( pluginDetected() ) {
        putIPX(fname,width,height);
        return;
    }
    if ( ie && (!IfJavaInstalled() || !navigator.javaEnabled()) ) {
        alert(msg_wait);
        putIPX(fname,width,height);
        return;
    }

    if ( IfJavaInstalled())
    {
         if( navigator.javaEnabled() ) {
            putJava(fname,width,height);
            if (ie) {
    	        document.write('<div style="visibility:hidden; display:none">');
                putIPX(fname,width,height);
    	        document.write('</div>');
            }
        }else{
            putJava(fname,width,height);
            alert(msg_enable_java);
        }
    }
    else {
            alert(msg_install_java);
            putJava(fname,width,height);
    }
  }