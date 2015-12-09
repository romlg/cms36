var idp = null;

$(function () {
    $(".idpico").click(
        function () {
            idp = $(this).attr("idp");

            switch (idp) {
                case "google"  :
                case "yahoo" :
                case "facebook":
                case "aol" :
                case "vimeo" :
                case "myspace" :
                case "tumblr" :
                case "lastfm" :
                case "twitter" :
                case "linkedin" :
                case "vkontakte" :
                    start_auth("?provider=" + idp);
                    break;
                case "wordpress" :
                case "blogger" :
                case "flickr" :
                case "livejournal" :
                    if (idp == "blogger") {
                        $("#openidm").html("¬ведите название блога");
                    }
                    else {
                        $("#openidm").html("¬ведите им€ пользовател€");
                    }
                    $("#openidimg").attr("src", "/images/hybridauth/" + idp + ".png");
                    $("#idps").hide();
                    $("#openidid").show();
                    break;
                case "openid" :
                    $("#openidm").html("¬ведите свой OpenID");
                    $("#openidimg").attr("src", "/images/hybridauth/" + idp + ".png");
                    $("#idps").hide();
                    $("#openidid").show();
                    break;
                case "eis":
                    $("#openidm").html("¬ведите им€ пользовател€");
                    $("#idps").hide();
                    $("#openidid").show();
                    break;
                default:
                    //alert("u no fun");
            }
        }
    );

    $("#openidbtn").click(
        function () {
            var oi, un;
            oi = un = $("#openidun").val();

            if (!un) {
                alert("¬ведите им€ пользовател€ или название блога.");
                return false;
            }

            switch (idp) {
                case "wordpress" :
                    oi = "http://" + un + ".wordpress.com";
                    break;
                case "livejournal" :
                    oi = "http://" + un + ".livejournal.com";
                    break;
                case "blogger" :
                    oi = "http://" + un + ".blogspot.com";
                    break;
                case "flickr" :
                    oi = "http://www.flickr.com/photos/" + un + "/";
                    break;
                case "eis" :
                    oi = "http://eis.oprf.ru/user/" + un + "/";
                    break;
            }

            start_auth("?provider=OpenID&openid_identifier=" + encodeURI(oi));
        }
    );

    $("#backtolist").click(
        function () {
            $("#idps").show();
            $("#openidid").hide();
            return false;
        }
    );

    $(".delSocial").click(function(){
        var id = $(this).attr('id');
        id = id.substr(9);
        $.post(
            '/cabinet/social',
            {id : id, act : 'del'},
            function (data) {
                location.href = '/cabinet/social';
            }
        );
        return false;
    });
});

function start_auth(params) {
    location.href = location.pathname + params + "&return_to=" + top.location.href + "&provider_auth=1&_ts=" + (new Date()).getTime();
}
