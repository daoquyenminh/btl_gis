<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>road</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css" />
    <script src="https://openlayers.org/en/v4.6.5/build/ol.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js" type="text/javascript"></script>
    <style>
         .map,
        .righ-panel {
            height: 86vh;
            width: 70vw;
            float: left;
        }

        .map {
            border: 1px solid #000;
            
        }
        a{
            text-decoration : none ;
            color: green;
        }
        a:hover {
            color: red;
        }
        
    </style>
</head>

<body onload="initialize_map();">
<h1>Hiển thị các con đường thuộc thành phố Washington</h1>
    <table>
        <tr>
            <td>
                <div id="map" class="map"></div>
                <!--<div id="map" style="width: 80vw; height: 100vh;"></div>-->
            </td>
            <td>
                <div id="info"></div>
               <a href="http://localhost/btl/index2.php">All file</a>
            </td>
        </tr>
    </table>
    <?php include 'CMR_pgsqlAPIRoad.php' ?>
    <script>
        //$("#document").ready(function () {
        var format = 'image/png';
        var map;
        var minX = -77.120849609375;
        var minY = 38.7906188964844;
        var maxX = -76.9080963134766;
        var maxY = 38.9969940185547;
        var cenX = (minX + maxX) / 2;
        var cenY = (minY + maxY) / 2;
        var mapLat = cenY;
        var mapLng = cenX;
        var mapDefaultZoom = 12;

        function initialize_map() {
            //*
            layerBG = new ol.layer.Tile({
                source: new ol.source.OSM({})
            });
            //*/
            var layerCMR_adm1 = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/btl/wms?',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.0',
                        STYLES: '',
                        LAYERS: 'roadway_functional_classification',
                    }
                })
            });
            var viewMap = new ol.View({
                center: ol.proj.fromLonLat([mapLng, mapLat]),
                zoom: mapDefaultZoom
                //projection: projection
            });
            map = new ol.Map({
                target: "map",
                layers: [layerBG, layerCMR_adm1],
                //layers: [layerCMR_adm1],
                view: viewMap
            });
            //map.getView().fit(bounds, map.getSize());

            var styles = {
                'MultiPolygon': new ol.style.Style({
                    fill: new ol.style.Fill({
                        color: 'green'
                    }),
                    
                })
            };
            var styleFunction = function(feature) {
                return styles[feature.getGeometry().getType()];
            };
            var vectorLayer = new ol.layer.Vector({
                //source: vectorSource,
                style: styleFunction
            });
            map.addLayer(vectorLayer);

            function createJsonObj(result) {
                var geojsonObject = '{' +
                    '"type": "FeatureCollection",' +
                    '"crs": {' +
                    '"type": "name",' +
                    '"properties": {' +
                    '"name": "EPSG:4326"' +
                    '}' +
                    '},' +
                    '"features": [{' +
                    '"type": "Feature",' +
                    '"geometry": ' + result +
                    '}]' +
                    '}';
                return geojsonObject;
            }

            function drawGeoJsonObj(paObjJson) {
                var vectorSource = new ol.source.Vector({
                    features: (new ol.format.GeoJSON()).readFeatures(paObjJson, {
                        dataProjection: 'EPSG:4326',
                        featureProjection: 'EPSG:3857'
                    })
                });
                var vectorLayer = new ol.layer.Vector({
                    source: vectorSource
                });
                map.addLayer(vectorLayer);
            }

            function displayObjInfo(result, coordinate) {
                //alert("result: " + result);
                //alert("coordinate des: " + coordinate);
                $("#info").html(result);
            }
            map.on('singleclick', function(evt) {
                //alert("coordinate org: " + evt.coordinate);
                //var myPoint = 'POINT(12,5)';
                var lonlat = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
                var lon = lonlat[0];
                var lat = lonlat[1];
                var myPoint = 'POINT(' + lon + ' ' + lat + ')';
                //alert("myPoint: " + myPoint);
                //*
                $.ajax({
                    type: "POST",
                    url: "CMR_pgsqlAPIRoad.php",
                    //dataType: 'json',
                    //data: {functionname: 'reponseGeoToAjax', paPoint: myPoint},
                    data: {
                        functionname: 'getInfoCMRToAjax',
                        paPoint: myPoint
                    },
                    success: function(result, status, erro) {
                        displayObjInfo(result, evt.coordinate);
                    },
                    error: function(req, status, error) {
                        alert(req + " " + status + " " + error);
                    }
                });
                //*/
            });
            function highLightGeoJsonObj(paObjJson) {
                var vectorSource = new ol.source.Vector({
                    features: (new ol.format.GeoJSON()).readFeatures(paObjJson, {
                        dataProjection: 'EPSG:4326',
                        featureProjection: 'EPSG:3857'
                    })
                });
                vectorLayer.setSource(vectorSource);
                /*
                var vectorLayer = new ol.layer.Vector({
                    source: vectorSource
                });
                map.addLayer(vectorLayer);
                */
            }

            function highLightObj(result) {
                //alert("result: " + result);
                var strObjJson = createJsonObj(result);
                //alert(strObjJson);
                var objJson = JSON.parse(strObjJson);
                //alert(JSON.stringify(objJson));
                //drawGeoJsonObj(objJson);
                highLightGeoJsonObj(objJson);
            }
            map.on('singleclick', function(evt) {
                //alert("coordinate: " + evt.coordinate);
                //var myPoint = 'POINT(12,5)';
                var lonlat = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
                var lon = lonlat[0];
                var lat = lonlat[1];
                var myPoint = 'POINT(' + lon + ' ' + lat + ')';
                //alert("myPoint: " + myPoint);
                //*
                $.ajax({
                    type: "POST",
                    url: "CMR_pgsqlAPIRoad.php",
                    //dataType: 'json',
                    data: {
                        functionname: 'getGeoCMRToAjax',
                        paPoint: myPoint
                    },
                    success: function(result, status, erro) {
                        highLightObj(result);
                    },
                    error: function(req, status, error) {
                        alert(req + " " + status + " " + error);
                    }
                });
                //*/
            });
        };
        //});
                        
    </script>
</body>

</html>