/**
 * Created by leo on 18.8.16..
 */



// var main = function () {
//     $('.click').click(function () {
//        alert('hello');
//     });
// };

$(document).ready(function () {
    $('.click').click(function () {
        var userName = $('#userNameInput').val();
        var password = $('#passwordInput').val();
        var firstname = $('#firstNameInput').val();
        var lastname = $('#lastNameInput').val();

        var userData = {
            "username" : userName,
            "password" : password,
            "firstname" : firstname,
            "lastname" : lastname
        };

        var storeUserLocation = "http://localhost.drive/user";

        $.ajax({
            method: "POST",
            url: storeUserLocation,
            data: {
                user: userData
            },
            crossDomain: true
        });
    });

    $('.submitLocation').click(function () {


        var longitude           = $('#longitudeInput').val();
        var latitude            = $('#latitudeInput').val();
        var altitude            = $('#altitudeInput').val();
        var speed               = $('#speedInput').val();

        var userId              = $('#userIdInput').val();
        var locationRoute = "http://localhost.drive/location/" + userId;

        var locationData        = {
            longitude : longitude,
            latitude : latitude,
            altitude : altitude,
            speed : speed
        };

        $.ajax({
            method: "POST",
            url: locationRoute,
            data: {
                location: locationData
            },
            crossDomain: true
        });
    });
});

//Cross-Origin Request Blocked: The Same Origin Policy disallows reading the remote resource at http:
//localhost.drive/user?undefined=&undefined=&undefined=&undefined=. (Reason: CORS header 'Access-Co
// ntrol-Allow-Origin' missing).