<?php

	/**
	 * Plugin Name:  WP Online Users
	 * Plugin URI: 	 https://github.com/talhaQ96/wp-online-users
	 * Description:  This plugin will display list of logged-in users. Activate the plugin and use shortcode [display-online-users].
	 * Version:      1.0
	 * Author:       Talha Qureshi 
	 * Author URI:   https://github.com/talhaQ96/
	 **/

    class WPOnlineUsers {

    	public static $table_name = "wp_online_users";

        /**
         * Constructor
         */

        function __construct() {
 
            $this->init_hooks();

        }
        

        /**
         * Initializing Hooks
         */

        function init_hooks() {
        	register_activation_hook(__FILE__, array('WPOnlineUsers', 'wpou_create_plugin_table'));

        	register_uninstall_hook(__FILE__, array('WPOnlineUsers', 'wpou_delete_plugin_table'));

        	add_action('wp_enqueue_scripts', array('WPOnlineUsers', 'wpou_enqueue_styles'));

        	add_action('wp_login', array('WPOnlineUsers', 'wpou_login') , 10, 2);

        	add_action('clear_auth_cookie', array('WPOnlineUsers', 'wpou_logout'));

        	add_shortcode('display-online-users', array('WPOnlineUsers', 'wpou_shortcode'));
        }


		/**
		 *	This Function creates a new table `wp_online_users` in database upon activation 
		 */

		function wpou_create_plugin_table() {
	
			global $wpdb;

			$table = $wpdb->prefix . self::$table_name; 
			$query = $wpdb->prepare('SHOW TABLES LIKE $table');
	
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
			if($wpdb->get_var($query) != $table){
	
				$query = "CREATE TABLE $table (`ID` INT NOT NULL ,
					`name` TEXT NOT NULL,
					UNIQUE (`ID`)) ENGINE = MyISAM";
	
				dbDelta($query);
			}
		}


		/**
		 *	This Function deletes the table `wp_online_users` from database when plugin is deleted 
		 */

		function wpou_delete_plugin_table() {
	
			global $wpdb;

			$table = $wpdb->prefix . self::$table_name;

			$wpdb->query("DROP TABLE IF EXISTS $table");

		}


		/**
		 *	This Function loads CSS file for the plugin 
		 */
	
		function wpou_enqueue_styles() {
		    $plugin_url = plugin_dir_url( __FILE__ );
		
		    wp_enqueue_style( 'plugin_style', $plugin_url . '/css/style.css' );
		}


		/**
		 *	This Function Adds user to the table name `plugin_display_user` when user logs in.
		 *  All logged in users are listed inside table name `plugin_display_user`.
		 */
	
		function wpou_login($user_login, $user){

			global $wpdb;

			$table = $wpdb->prefix . self::$table_name; 
	
			$wpdb->insert($table, array('ID' => $user->ID, 'name' => $user->display_name));
		}


		/**
		 *	This Function Deletes user from the table name `plugin_display_user` when user logs out.
		 */
	
		function wpou_logout(){

			global $wpdb;
	
			$table = $wpdb->prefix . self::$table_name;
			$wpdb->delete($table, array('ID' => get_current_user_ID()));
		}


		/**
		 *	This Function fetch list of users from table name `plugin_display_user`
		 *  Table `plugin_display_user` has all loggedin users only
		 * 	Users can be displayed via shortcode.
		 *  Shortcode is [display-online-users]
		 */
		
		function wpou_shortcode($output){
			
			global $wpdb;

			$table = $wpdb->prefix . self::$table_name;
			$query = $wpdb->prepare("SELECT * FROM $table");
			$users = $wpdb->get_results($query);
	
			$output  = '<table id="wp_display_online_users">';
				$output .= '<tr>';
					$output .= '<th>User ID</th>';
					$output .= '<th>User Name</th>';
				$output .= '</tr>';
	
	        	foreach ($users as $user):
					$output .= '<tr>';
						$output .= '<td>' .$user->ID. '</td>';
						$output .= '<td>' .$user->name. '</td>';
					$output .= '</tr>';
	        	endforeach;
			$output .= '</table>';
	
			return $output;
	
		}
	}
    
    $wpou_init = new WPOnlineUsers();