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
		$ui->append( 'submit', 'save', _t( 'Save', 'persona' ) );
		$ui->set_option( 'success_message', _t( 'Options successfully saved.' ) );
		$ui->out();
	}
	
	/**
	 * Modify the login form
	 * 
	 * @todo Decide which of these loginform methods I need to use
	 */
	public function action_theme_loginform_before()
    {
		// <script src="https://browserid.org/include.js" type="text/javascript"></script>
	}
	
	/**
	 * Modify the login form
	 */
	public function action_theme_loginform_controls()
	{

	}
	
	/**
	 * Modify the login form
	 * 
	 * @todo Decide which of these loginform methods I need to use
	 */
	public function action_theme_loginform_after()
    {
		
	}
}
?>