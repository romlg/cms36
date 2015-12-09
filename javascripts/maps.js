//Переменные
var map;
var list = "";
var marker = [];
var markers;
var tableClass = [];
//var bounds = new GLatLngBounds();
var markerImage = ["/images/marker_green.png", "/images/marker_grey.png", "/images/marker_grey.png"];

//Инициализация карты
function initMap(coordinats) {
	if (GBrowserIsCompatible()) {
		map = new GMap2(document.getElementById("map"));
		map.addControl(new GMapTypeControl());
		map.addControl(new GLargeMapControl());
		map.setCenter(new GLatLng(55.75, 37.62), 10);
		parseXml(coordinats);
	}
}

//Парсинг XML
function parseXml(fileName) {
	var xml = GXml.parse(fileName);
	markers = xml.documentElement.getElementsByTagName("marker");
	var list;
	var lat, lng, address, name;
	var geocoder = new GClientGeocoder();
	for (var i = 0; i < markers.length; i++) {
		lat = parseFloat(markers[i].getAttribute("lat"));
		lng = parseFloat(markers[i].getAttribute("lng"));
		if (lat > 0 && lng > 0) {
			var latlng = new GLatLng(lat,lng);

			label  = "<div class='forMapBlock'>";
			label += "<img class='pic' src='/images/pi_nophoto_big.gif' alt='' />";
			label += "<img class='ratePic' src='/images/star_left_"+markers[i].getAttribute("stars")+".gif' width='95' height='17' alt='' />";
			label += "<p>"+markers[i].getAttribute("name")+"</p>";
			label += "<p style='clear:right;'><strong>"+markers[i].getAttribute("address")+"</strong></p>";
			label += "<p>выбор/22</p>";
			label += "<p>"+markers[i].getAttribute("total_area")+"\/"+markers[i].getAttribute("living_area")+"\/"+markers[i].getAttribute("kitchen_area")+" кв.м</p>";
			label += "<p>"+markers[i].getAttribute("price_rub")+" руб. <a href='obj_search/object/"+markers[i].getAttribute("obj_id")+"/'>подробнее</a></p>";
			label += "</div>";

			ZMarker(latlng,markers[i].getAttribute("name"),label,1,0,i,null);
		} else {
			address = markers[i].getAttribute("address");
			if (address) {
				var functName = 'gmap_callback_'+i;
				window[functName] = new Function('responce', "addObjAddress('"+i+"', responce);");
				geocoder.getLocations(address, window[functName]);
			}
		}
	}
}

//Эта функция передается в виде литерала, как параметр (поэтому необходимо вложение ZMarkerInside).
function addObjAddress (id, response){
	if (response && response.Status.code == 200) {
		function ZMarkerInside(point,name,label,n,imInd,i,visited) {
			function sendBack(marker,b) {
				return GOverlay.getZIndex(marker.getPoint().lat())-n*10000;
			}
			marker[i] = new GMarker(point,{title:name, zIndexProcess:sendBack});
			map.addOverlay(marker[i]);
			marker[i].setImage(markerImage[imInd]);
			marker[i].visited = visited;

			GEvent.addListener(marker[i], "click", function() {
				marker[i].openInfoWindowHtml(label);
				marker[i].visited = true;
				GEvent.trigger(marker[i],"mouseout");
			});
			GEvent.addListener(marker[i],'mouseover',function(){
				marker[i].setImage(markerImage[1]);
				document.getElementById("list_tr_"+i).className = "hover";
			});
			GEvent.addListener(marker[i],'mouseout',function(){
				if(marker[i].visited){
					marker[i].setImage(markerImage[2]);
				}
				else{
					marker[i].setImage(markerImage[0]);
				}
				document.getElementById("list_tr_"+i).className = tableClass[i];
			});
			GEvent.addListener(marker[i], "infowindowclose", function() {
				map.removeOverlay(marker[i]);
				ZMarkerInside(point,name,label,count(), 2,i,marker[i].visited);
			})
		}

		place = response.Placemark[0];
		label  = "<div class='forMapBlock'>";
		label += "<img class='pic' src='/images/pi_nophoto_big.gif' alt='' />";
		label += "<img class='ratePic' src='/images/star_left_"+markers[id].getAttribute("stars")+".gif' width='95' height='17' alt='' />";
		label += "<p>"+markers[id].getAttribute("name")+"</p>";
		label += "<p style='clear:right;'><strong>"+markers[id].getAttribute("address")+"</strong></p>";
		label += "<p>выбор/22</p>";
		label += "<p>"+markers[id].getAttribute("total_area")+"\/"+markers[id].getAttribute("living_area")+"\/"+markers[id].getAttribute("kitchen_area")+" кв.м</p>";
		label += "<p>"+markers[id].getAttribute("price_rub")+" руб. <a href='obj_search/object/"+markers[id].getAttribute("obj_id")+"/'>подробнее</a></p>";
		label += "</div>";

		var my_marker = {};
		my_marker["new_lat"] = "'"+place.Point.coordinates[1]+"'";
		my_marker["new_lng"] = "'"+place.Point.coordinates[0]+"'";
		my_marker['address_id'] = markers[id].getAttribute("address_id");
		$.ajax({
			type: "POST",
			url: "save_obj_coordinat",
			dataType: "json",
			data: 'marker='+encodeURIComponent($.toJSON(my_marker))
		});

		var latlng = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
		ZMarkerInside(latlng,markers[id].getAttribute("name"),label,1,0,id,null);
	}
}

//Подсветка маркеров
var n=1;
function count(){
	n++;
	return n;
}

function ZMarker(point,name,label,n,imInd,i,visited) {
	function sendBack(marker,b) {
		return GOverlay.getZIndex(marker.getPoint().lat())-n*10000;
	}
	marker[i] = new GMarker(point,{title:name, zIndexProcess:sendBack});
	map.addOverlay(marker[i]);
	marker[i].setImage(markerImage[imInd]);
	marker[i].visited = visited;

	GEvent.addListener(marker[i], "click", function() {
		marker[i].openInfoWindowHtml(label);
		marker[i].visited = true;
		GEvent.trigger(marker[i],"mouseout");
	});
	GEvent.addListener(marker[i],'mouseover',function(){
		marker[i].setImage(markerImage[1]);
		document.getElementById("list_tr_"+i).className = "hover";
	});
	GEvent.addListener(marker[i],'mouseout',function(){
		if(marker[i].visited){
			marker[i].setImage(markerImage[2]);
		}
		else{
			marker[i].setImage(markerImage[0]);
		}
		document.getElementById("list_tr_"+i).className = tableClass[i];
	});
	GEvent.addListener(marker[i], "infowindowclose", function() {
		map.removeOverlay(marker[i]);
		ZMarker(point,name,label,count(), 2,i,marker[i].visited);
	})
}

window.onunload = GUnload;