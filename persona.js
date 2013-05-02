(function() {
	$('#signin').click(function(e){ navigator.id.request({siteName:persona.sitename}); });
	$('#link-logout').click(function(e){ navigator.id.logout(); });

	navigator.id.watch({
		loggedInUser: persona.currentUser || null,
		
		onlogin: function(assertion) {
            // A user has logged in! Here you need to send the
            // assertion to your backend for verification and to
            // create a session and then update your UI.
            $.ajax({
                type: 'POST',
                url: 'login', // This is a URL on your website.
                data: {habari_username: 'PersonaID',
                	habari_password: assertion,
                	submit_button: 'Login'},
                success: function(res, status, xhr) {
                    document.location = persona.login_redirect;
                },
                error: function(xhr, status, err) {
                    alert('Login failure: ' + err);
                    navigator.id.logout();
                }
            });
        },
        onlogout: function() {
        	// Chrome doesn't play nice here and seems to want to logout the moment
        	// you login.  That said, we don't really need this as we're using 
        	// Habari's session cookie so logging out from Habari normally should 
        	// be all that is needed.


            // A user has logged out! Here you need to tear down the
            // user's session by redirecting the user or making a call
            // to your backend. Also, make sure loggedInUser will get
            // set to null on the next page load.
            // 
            // Apparently there's a bug in Chrome so lets make sure someone is
            // actually logged in before calling logout:
            //if (persona.currentUser) {
            //	return;
            	//alert('Trying to logout');
            	//document.location = persona.logout_redirect;
	            /*$.ajax({
	                type: 'GET',
	                url: 'logout', // This is a URL on your website.
	                success: function(res, status, xhr) {
	                	
	                    //window.location.reload();
	                },
	                error: function(xhr, status, err) {
	                    alert('Logout failure: ' + err);
	                }
	            });*/
	        //}
        }

	});
}) (jQuery);