function setLang(event, lang, base) {
    if (event == undefined) event = window.event;
    var arr = new Array();
    arr = lang.split("__");
    lang = arr[0];
    var domain = arr[1];

    if (lang != "" && lang != "undefined") SetCookie('lang', lang, base);
    if (domain != "" && domain != "undefined") SetCookie('domain', domain, base);

    var query, str;
    var search = new Array();
    query = "http://" + window.location.host + window.location.pathname;

    data = parseUrlQuery();
    for(var pos in data) {
        if (pos=='offset') {
            search.push(pos + '=0');
        } else {
            search.push(pos + '=' + data[pos]);
        }
    }
    str = search.join('&');
    if (str) {
        query = query + '?' + str;
    }

    window.location.assign(query);
    return false;
}

function parseUrlQuery() {
    var data = {};
    if(location.search) {
        var pair = (location.search.substr(1)).split('&');
        for(var i = 0; i < pair.length; i ++) {
            var param = pair[i].split('=');
            data[param[0]] = param[1];
        }
    }
    return data;
}


function maximizeCnt(src) {
    window.open(src, 'maximized', 'fullscreen=1');
    return true;
}

// Retrieve the value of the cookie with the specified name.
function GetCookie(sName) {
    // cookies are separated by semicolons
    var aCookie = document.cookie.split("; ");
    for (var i = 0; i < aCookie.length; i++) {
        // a name/value pair (a crumb) is separated by an equal sign
        var aCrumb = aCookie[i].split("=");
        if (sName == aCrumb[0])
            return unescape(aCrumb[1]);
    }

    // a cookie with the requested name does not exist
    return null;
}

function SetCookie(sName, sValue, path) {
    var d = new Date();
    var date = new Date(d.getFullYear(), d.getMonth() + 3, d.getDate());
    document.cookie = sName + "=" + escape(sValue) + "; expires=" + date.toGMTString() + "; path=" + path;
}
