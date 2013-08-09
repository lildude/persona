<?php
/**
 * @package Persona
 * @version 0.1
 * @author Colin Seymour - http://colinseymour.co.uk
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

class Persona extends Plugin
{
	/**
     * Add the Configure option for the plugin
     *
     * @access public
     * @param array $actions
     * @param string $plugin_id
     * @return array
     */
    public function filter_plugin_config( $actions )
    {
		$actions['configure']= _t( 'Configure', 'persona' );
        return $actions;
    }
	
	/**
     * Plugin UI
     *
     * @access public
     * @return void
     */
    public function action_plugin_ui_configure()
    {
		$ui = new FormUI( strtolower( __CLASS__ ) );
		$ui->append( 'checkbox', 'enable_login', __CLASS__ . '__enable_login', _t( 'Enable Habari User Authentication' ) );
			$ui->enable_login->helptext = _t('Enable this if you wish to allow Habari users to authenticate using Persona' );
		$ui->append( 'submit', 'save', _t( 'Save', 'persona' ) );
		$ui->set_option( 'success_message', _t( 'Options successfully saved.' ) );
		$ui->out();
	}

	/**
	 * Suppress Compatibility Mode on IE
	 */
	public function action_admin_header()
	{
		echo '<meta http-equiv="X-UA-Compatible" content="IE=Edge">';
        echo self::persona_props();
	}

	/**
	 * Add Persona info to the admin pages
	 *
	 * We need this so we can successfully log out from Persona at the same time
	 * as we log out of Habari
	 */
	public function action_admin_footer()
	{
		if ( Options::get( __CLASS__ . '__enable_login' ) ) {
			//echo self::persona_props();
			Stack::add( 'admin_footer_javascript', 'https://login.persona.org/include.js', 'persona_include' );
			Stack::add( 'admin_footer_javascript', $this->get_url() . '/persona.js', 'persona', 'persona_props' );
		}
	}

    /**
     * Forcefully remove the email address from the session cookie
     * 
     */
    public function action_user_logout()
    {
        //unset($_SESSION['login']['email']);
    }
	
	/**
	 * Modify the login form
	 *
	 * @todo  Change this with Habari 0.10 as it uses FormUI for the login form
	 */
	public function action_theme_loginform_controls()
	{
		if ( Options::get( __CLASS__ . '__enable_login' ) ) {
			echo '<p><a href="#"><img id="signin" src="' . URL::get_from_filesystem( __FILE__ ) . '/img/persona_sign_in_black.png" width="185px" height="25px"/></a><br><a href="https://login.persona.org/" target="_blank" style="font-size: smaller; vertical-align: baseline;">What is Persona?</a></p>';
			//echo '<p><a href="#" id="link_logout">logout of persona</a></p>';
		}
	}
	
	private static function persona_props()
	{
		$user = User::identify();
		$email = ( $user->email != '' ) ? '"' . $user->email .'"' : 'null';
		$login_redirect = ( isset( $_SESSION['login'] ) ? $_SESSION['login']['original'] : Site::get_url( 'admin' ) );
		return '<script type="text/javascript">var persona = { "sitename":"'. Options::get( 'title' ) .'","currentUser":' . $email . ',"login_redirect":"'. $login_redirect .'","logout_redirect":"'. Site::get_url( 'logout' ) .'"};</script>';
	}

	/**
	 * Modify the login form
	 * 
	 * @todo Decide which of these loginform methods I need to use
	 */
	public function action_theme_loginform_after()
    {	
    	/*if ( Options::get( __CLASS__ . '__enable_login' ) ) {
			echo '<script src="https://login.persona.org/include.js"></script>';
			echo '<script src="' . URL::get_from_filesystem( __FILE__ ) . '/persona.js"></script>';
		}*/
	}

	/**
	 * This is what we need to call to authenticate the user locally after Persona has done it's bit.
	 */
	public function filter_user_authenticate( $user, $username, $password )
    {
    	if ( isset( $username ) && $username == 'PersonaID' ) {
    		//EventLog::log( 'Persona Login attempt' );
    		$result = self::verify_assertion( $password );
    		if ( empty( $result ) || empty( $result->status ) ) {
    			// No result or status
    			// TODO: Tie this into human message for normal failed logins
    			EventLog::log( 'Verification response invalid' );
    			return false;
    		}
    		else if ( $result->status != 'okay' ) {
    			// Bad status
    			$msg = 'Verification failed';
    			if ( isset( $result->reason ) ) {
    				$msg .= ': ' . $result->reason;
    			}
    			EventLog::log( $msg );
    			return false;
    		} else {
    			// Success so lets make sure this is a valid local user
    			//EventLog::log( 'Checking user...' );
    			if ( $user = User::get_by_email( $result->email ) ) {
    				//EventLog::log( 'Local user found' );
    				return $user;
    			} else {
    				// Email address isn't registered with a user on this system so fail
    				EventLog::log( 'Attempt to login as '.$result->email );
    				return false;
    			}
    		}
    	} else {
    		//EventLog::log( 'Not a Persona Login' );
    		return $user;
    	}
    }

    /**
     * Verify the assertion to the verifier
     *
     * @todo  Make the verifier configurable.
     */
    private static function verify_assertion( $assertion )
    {
    	// POST to Mozilla our assertion code
    	$rr =  new RemoteRequest( 'https://verifier.login.persona.org/verify', 'POST' );
    	// TODO: This is messy.
    	$post = Config::get( 'custom_http_port', isset( $_SERVER['SERVER_PORT'] ) ? $_SERVER['SERVER_PORT'] : $port );
    	$rr->set_postdata( array(
    		'assertion' => $assertion,
    		'audience' => Site::get_url( 'host' ).':'.$post,	// Not sure if this should be the host or full path
    		) );
    	try {
    		$rr_result = $rr->execute();
            $rr_resp = $rr->get_response_body();
            $response = json_decode( $rr_resp );
            EventLog::log( 'Verification received: ' . $rr_resp );
            return $response;
    	}
    	catch( Exception $e ) {
    		 EventLog::log( 'Failed to verify identity: ' . $e->getMessage() );
    	}
    }
}
?>