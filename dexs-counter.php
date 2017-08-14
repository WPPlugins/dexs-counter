<?php
/*
 	Plugin Name: 	Dexs.Counter
 	Version:		0.1.3
 	Plugin URI:		https://www.wordpress.org/plugins/dexs-counter
 	Description:	A simple but powerful view counter and rating system, with own (dashboard) widgets, for your awesome WordPress website.
 	Author:			SamBrishes@pytesNET
 	Author URI:		http://www.pytes.net
 	License:		X11 / MIT License
 	License URI:	https://opensource.org/licenses/MIT
 	Text Domain:	dexs-counter
 	Domain Path:	/includes/langs
 */
	if(!defined("ABSPATH")){ die("Go directly to jail; do not pass go, do not collect 200 cookies."); }
	$desc = __("A simple but powerful view counter and rating system, with own (dashboard) widgets, for your awesome WordPress website.", "dexs-counter");
	
	// Define Stuff
	define("DEXS_CR", 			"dexs-counter");
	define("DEXS_CR_URL",		plugins_url("", __FILE__));
	define("DEXS_CR_PATH",		plugin_dir_path(__FILE__));
	define("DEXS_CR_FILE",		plugin_basename(__FILE__));
	define("DEXS_CR_VERSION",	"0.1.2");
	
	// Init Language
	load_plugin_textdomain(DEXS_CR, false, dirname(DEXS_CR_FILE)."/includes/langs");
	
	// Load Stuff
	include_once(DEXS_CR_PATH . "/system/func.frontend.php");
	include_once(DEXS_CR_PATH . "/system/class.counter.php");
	include_once(DEXS_CR_PATH . "/system/class.system.php");
	include_once(DEXS_CR_PATH . "/system/class.widgets.php");
	$dexsCounter = new dexsCounter();
	
	/*
	 |	ACTIVATE PLUGIN
	 |	@since	0.1.0
	 |	@update	0.1.1
	 */
	function dexs_counter_activate(){
		global $wpdb;
		
		$version = get_option("dexs-counter-version", NULL);
		if($version === NULL){
			require_once(ABSPATH . "wp-admin/includes/upgrade.php");
			
			$table 	 = $wpdb->prefix . "dexs_counter";
			$charset = $wpdb->get_charset_collate();
			$query	 = "CREATE TABLE IF NOT EXISTS $table (
				counter_id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
				post_id MEDIUMINT(9) NOT NULL UNIQUE,
				daily_stats TEXT NOT NULL,
				daily_date VARCHAR(10) DEFAULT '0000-00-00' NOT NULL,
				daily_meta TEXT NOT NULL,
				counter_views TEXT NOT NULL,
				counter_views_meta TEXT NOT NULL,
				counter_ratings TEXT NOT NULL,
				counter_ratings_meta TEXT NOT NULL,
				PRIMARY KEY  (counter_id)
			) $charset;";
			dbDelta($query);
			
			$table 	 = $wpdb->prefix . "dexs_counter_stats";
			$query	 = "CREATE TABLE IF NOT EXISTS $table (
				stats_id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
				stats_date VARCHAR(10) DEFAULT '0000-00-00' NOT NULL UNIQUE,
				stats_views TEXT NOT NULL,
				stats_ratings TEXT NOT NULL,
				stats_meta TEXT NOT NULL,
				PRIMARY KEY  (stats_id)
			) $charset;";
			dbDelta($query);
			
			update_option("dexs-counter-version", DEXS_CR_VERSION, "no");
			update_option("dexs-counter-verify", array(), "no");
			update_option("dexs-counter-config", array(
				"enable-statistics"		=> true,
				"enable-widgets"		=> true,
				"view-types"			=> array("post", "page"),
				"view-excludes"			=> array("administrator", "editor", "author"),
				"rating-types"			=> array("post", "page"),
				"rating-excludes"		=> array("administrator", "editor", "author"),
				"rating-layout"			=> "double"
			), "no");
		} else {
			update_option("dexs-counter-version", DEXS_CR_VERSION, "no");
			update_option("dexs-counter-verify", array(), "no");
		}
		
		if(!wp_next_scheduled("dexs_counter_cron_job", array("dexs-counter-schedule"))){
			wp_schedule_event(time(), "twicedaily", "dexs_counter_cron_job", array("dexs-counter-schedule"));
		}
	}
	register_activation_hook(__FILE__, "dexs_counter_activate");
	
	/*
	 |	DEACTIVATION PLUGIN
	 |	@since	0.1.0
	 */
	function dexs_counter_deactivate(){
		wp_clear_scheduled_hook("dexs_counter_cron_job", array("dexs-counter-schedule"));
	}
	register_deactivation_hook(__FILE__, "dexs_counter_deactivate");
	
	/*
	 |	UNINSTALL PLUGIN
	 |	@since	0.1.0
	 |	@update	0.1.1
	 */
	function dexs_counter_uninstall(){
		global $wpdb;
		
		// Delete WP_Cron Job
		wp_clear_scheduled_hook("dexs_counter_cron_job", array("dexs-counter-schedule"));
		
		// Delete Options
		delete_option("dexs-counter-version");
		delete_option("dexs-counter-verify");
		delete_option("dexs-counter-config");
		
		// Delete Tables
		$table = $wpdb->prefix . "dexs_counter";
		$wpdb->query("DROP TABLE IF EXISTS $table");
		$table = $wpdb->prefix . "dexs_counter_stats";
		$wpdb->query("DROP TABLE IF EXISTS $table");
		
		// Delete Post Meta
		delete_post_meta_by_key("dexs_views_total");
		delete_post_meta_by_key("dexs_views_today");
		delete_post_meta_by_key("dexs_views_unique");
		delete_post_meta_by_key("dexs_views_today_unique");
		delete_post_meta_by_key("dexs_ratings");
		delete_post_meta_by_key("dexs_ratings_num");
	}
	register_uninstall_hook(__FILE__, "dexs_counter_uninstall");
	
	/*
	 |	DEXS COUNTER SCHEDULE (WP_CRON)
	 |	@since	0.1.0
	 */
	function dexs_counter_schedule(){
		global $dexsCounter;
		
		if($dexsCounter->_config("enable-statistics")){
			$dexsCounter->system->_countStats();
		}
		delete_post_meta_by_key("dexs_views_today");
		delete_post_meta_by_key("dexs_views_today_unique");
	}
	add_action("dexs_counter_cron_job", "dexs_counter_schedule");
