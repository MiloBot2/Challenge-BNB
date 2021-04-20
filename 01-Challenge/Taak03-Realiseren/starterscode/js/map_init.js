
// Je dient enkel regel 13 aan te passen, de rest van deze JS code kun je laten staan :))


var mymap = L.map('mapid').setView([52.1283, 5.109], 8);

L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
    maxZoom: 18,
    id: 'mapbox/streets-v11',
    tileSize: 512,
    zoomOffset: -1,
    accessToken: 'pk.eyJ1Ijoic3JtaWVwIiwiYSI6ImNrbmZyc2o2aTBrMm4yeG53bHhlZWNxMHoifQ.oQ09lfMb1f7mL3gLm_ji7Q'
}).addTo(mymap);

// Voor een access token maak je account aan https://account.mapbox.com/access-tokens/
// plak de code in het object hierboven

var myIcon = L.icon({
    iconUrl: 'images/roc.jpg',
    iconSize: [24, 24],
});

var rocMarker = L.marker([52.28785, 4.83866], {
    icon: myIcon
}).addTo(mymap);