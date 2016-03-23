var HANNAH_UID = '10208970880619765';
var BEN_UID = '2532088713305';

function statusChangeCallback(response) {
    // Logged into app and Facebook.
    if (response.status === 'connected') {
        FB.api('/me', function(response) {
            console.log(JSON.stringify(response));
        });
        var loginClass = ($.inArray(response.authResponse.userID, [HANNAH_UID, BEN_UID]) !== -1) 
            ? 'admin-logged-in' : 'logged-in';
        $('#content').addClass(loginClass); 
    } 
    // The person is logged into Facebook, but not app
    else if (response.status === 'not_authorized') {
      
    } 
    else {
      
    }
}

function checkLoginState() {
  FB.getLoginStatus(function(response) {
    statusChangeCallback(response);
  });
}

window.fbAsyncInit = function() {
    FB.init({
        appId: '547039428806361',
        cookie: true,  // enable cookies to allow the server to access the session
        version: 'v2.5' // use graph api version 2.5
    });

    checkLoginState();
};

// Load the SDK asynchronously
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

$(document).on('ready', function() {
    $('#fb-login').on('click', function() {
        FB.login(function(response){
            checkLoginState();
        });
    }); 
});


