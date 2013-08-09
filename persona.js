(function() {
	$('#signin').click(function(e){ navigator.id.request({siteName:persona.sitename}); });
	$('#link-logout').click(function(e){ navigator.id.logout(); });  // Reacts to the "logout" link in the admin menu only

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
                	navigator.id.logout();
                    alert( 'Login failure: ' + err );
                    
                }
            });
        },
        onlogout: function() {                       
            // I'm not sure if we really need this as we're using 
        	// Habari's session cookie so logging out from Habari normally should 
        	// be all that is needed.

            // A user has logged out! Here you need to tear down the
            // user's session by redirecting the user or making a call
            // to your backend. Also, make sure loggedInUser will get
            // set to null on the next page load.

            if (persona.currentUser) {
            	//alert('Trying to logout');
            	document.location = persona.logout_redirect;  
	        }
        }

	});
}) (jQuery);