$(document).ready(function() {
    var mymap = L.map('mapid').setView([10.31672, 123.89071], 15);

    $('.location').on('click', function() {
        coors = JSON.parse("[" + $(this).attr('coor') + "]");
        loc = $(this);

        var marker = L.marker(coors).addTo(mymap);                 

        if (loc.attr('loc') == 'booking') {
            mymap.setView(coors, 15);
        }
        else {
            mymap.setView(coors, 10);
        }

        $('#locationModal').show();

        setTimeout(function() {
            mymap.invalidateSize();

            if (loc.attr('loc') == 'dep') {
                marker.bindPopup("<b>You Depart Here</b>").openPopup();
            }
            else if (loc.attr('loc') == 'dest') {
                marker.bindPopup("<b>You Arrive Here</b>").openPopup();
            }
            else {
                marker.bindPopup("<b>"+ loc.attr('address') +"</b><br>Contact: "+loc.attr('phone')).openPopup();
            }
        }, 5);
    });

    $('.close').on('click', function() {
        $('#locationModal').hide();
    });
    
    L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1Ijoic2hpcHNrZWQiLCJhIjoiY2o3aDFsNGFzMWRjbjJ4bzZnYWFsaGdkZiJ9.y04pjlR2aYVU-a5nJn9SEQ', {
            attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
                maxZoom: 18,
                    id: 'mapbox.streets',
                        accessToken: 'pk.eyJ1Ijoic2hpcHNrZWQiLCJhIjoiY2o3aDFsNGFzMWRjbjJ4bzZnYWFsaGdkZiJ9.y04pjlR2aYVU-a5nJn9SEQ'
    }).addTo(mymap);
});
