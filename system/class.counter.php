<?php
/* 
 |	DEXS.COUNTER
 |	@file		./system/class.counter.php
 |	@author		SamBrishes@pytesNET
 |	@version	0.1.3 [0.1.0] - Alpha
 |
 |	@license	X11 / MIT License
 |	@copyright	Copyright © 2015 - 2016 pytesNET
 */
	if(!defined("DEXS_CR")){ die("Go directly to jail; do not pass go, do not collect 200 cookies."); }
	
	class dexsCounter{
		/*
		 |	GLOBAL VARs
		 */
		public $system = NULL;
		private $config = array();
		private $default = array(
			"enable-statistics"		=> true,
			"enable-widgets"		=> true,
			"view-types"			=> array("post", "page"),
			"view-excludes"			=> array("administrator", "editor", "author"),
			"rating-types"			=> array("post", "page"),
			"rating-excludes"		=> array("administrator", "editor", "author"),
			"rating-layout"			=> "double"
		);
		
		/*
		 |	CONSTRUCTOR
		 |	@since	0.1.0
		 */
		public function __construct(){
			$this->config = array_merge($this->default, get_option("dexs-counter-config", array()));
			$this->system = new dexsCounter_System($this->config);
			
			if(is_admin()){
				add_filter("plugin_row_meta", array($this, "_filter"), 10, 2);
				add_filter("plugin_action_links_".DEXS_CR_FILE, array($this, "_filter"));
				
				add_action("admin_menu", array($this, "_action"));
				add_action("wp_dashboard_setup", array($this, "_action"));
				add_action("admin_enqueue_scripts", array($this, "_action"));
				
				add_action("init", array($this, "_update"));
			}
			
			if($this->config["enable-widgets"]){
				if(!empty($this->config["view-types"])){
					add_action("widgets_init", function(){ register_widget("dexsCounter_viewWidget"); });
				}
				if(!empty($this->config["rating-types"])){
					add_action("widgets_init", function(){ register_widget("dexsCounter_rateWidget"); });
				}
			}
		}
		
		/*
		 |	HELPER :: REDIRECT
		 |	@since	0.1.0
		 */
		private function _redirect($action, $status){
			$query = array(
				"page"		=> DEXS_CR,
				"action"	=> $action,
				"status"	=> $status
			);
			wp_safe_redirect(admin_url("options-general.php")."?".http_build_query($query));
			die();
		}
		
		/*
		 |	HELPER :: GET CONFIG
		 |	@since	0.1.0
		 */
		public function _config($key){
			if(array_key_exists($key, $this->config)){
				return $this->config[$key];
			}
			return false;
		}
		
		/*
		 |	FILTER :: HANDLE
		 |	@since	0.1.0
		 */
		public function _filter($links = array(), $file = ""){
			$filter = current_filter();
			
			// Filter Action Links
			if($filter === "plugin_action_links_".DEXS_CR_FILE){
				$admin = admin_url("options-general.php?page=".DEXS_CR);
				array_unshift($links, '<a href="'.$admin.'">'.__("Settings").'</a>');
				return $links;
			}
			
			// Filter Row Meta Links
			if($filter === "plugin_row_meta"){
				if($file === DEXS_CR_FILE){
					$icon = '<span class="dashicons dashicons-heart" style="color:#EA4235;"></span>';
					array_unshift($links, sprintf(__("Made with %s", DEXS_CR), $icon));
				}
				return $links;
			}
		}
		
		/*
		 |	ACTION :: HANDLE
		 |	@since	0.1.0
		 */
		public function _action(){
			$filter = current_filter();
			
			// Add Admin Page
			if($filter === "admin_menu"){
				$page = add_submenu_page(
					"options-general.php",
					"Dexs Counter",
					"Dexs Counter",
					"manage_options",
					DEXS_CR,
					array($this, "adminPage")
				);
				add_action("load-".$page, array($this, "_action"));
			}
			
			// Add Admin Page Stuff
			if($filter === "load-settings_page_".DEXS_CR){
				$screen = get_current_screen();
				if($screen->base === "settings_page_".DEXS_CR){
					wp_enqueue_style(
						"dexs-config", 
						DEXS_CR_URL."/includes/css/dexs.config.css", 
						array(), 
						"0.1.2"
					);
				}
			}
			
			// Add Dashboard Widget
			if($filter === "wp_dashboard_setup"){
				if($this->config["enable-statistics"]){
					wp_add_dashboard_widget(
						DEXS_CR, 
						"Dexs ".__("Counter Statistics", DEXS_CR),
						array($this, "adminWidget"),
						array($this, "adminWidgetConfig")
					);
				}
			}
			
			// Add Dashboard Widget Stuff
			if($filter === "admin_enqueue_scripts"){
				$screen = get_current_screen();
				if($screen->base === "dashboard" && $this->config["enable-statistics"]){
					wp_enqueue_script(
						"chart", 
						DEXS_CR_URL."/includes/js/chart.min.js", 
						array("jquery"), 
						"2.4.0"
					);
					wp_enqueue_script(
						"dexs-admin", 
						DEXS_CR_URL."/includes/js/dexs.dashboard.js", 
						array("chart"), 
						"0.1.2"
					);
					wp_enqueue_style(
						"dexs-admin", 
						DEXS_CR_URL."/includes/css/dexs.dashboard.css", 
						array(), 
						"0.1.2"
					);
				}
			}
		}
		
		/*
		 |	ACTION :: HANDLE FORM
		 |	@since	0.1.0
		 */
		public function _update(){
			// Check Fields
			if(!isset($_POST["dexs-counter-action"]) || !isset($_POST["dexs-counter-nonce-field"])){
				return;
			}
			if(!wp_verify_nonce($_POST["dexs-counter-nonce-field"], "dexs-counter-nonce")){
				return;
			}
			if(!in_array($_POST["dexs-counter-action"], array("reset", "update"))){
				return;
			}
			
			// Reset Settings
			if($_POST["dexs-counter-action"] === "reset"){
				if($this->config === $this->default || update_option("dexs-counter-config", $this->default, "no")){
					$this->_redirect("reset", "success");
				}
				$this->_redirect("reset", "error");
			}
			
			// Sanitize Settings
			$data = array();
			foreach($this->default AS $key => $value){
				if(!isset($_POST[$key])){
					if(is_array($value)){
						$data[$key] = array();
					} else if(is_bool($value)){
						$data[$key] = false;
					} else {
						$data[$key] = $value;
					}
					continue;
				}
				
				if(is_array($value)){
					$data[$key] = array();
					if(is_array($_POST[$key])){
						foreach($_POST[$key] AS $check){
							if(in_array($key, array("view-types", "rating-types"))){
								if(($temp = get_post_type_object($check)) !== null){
									array_push($data[$key], $temp->name);
								}
							} else if(in_array($key, array("view-excludes", "rating-excludes"))){
								if(($temp = get_role($check)) !== null){
									array_push($data[$key], $temp->name);
								}
							}
						}
					}
				} else if(is_bool($value)){
					$data[$key] = true;
				} else {
					$data[$key] = esc_attr($_POST[$key]);
				}
			}
			
			// Update Settings
			if($this->config === $data || update_option("dexs-counter-config", $data, "no")){
				$this->_redirect("save", "success");
			}
			$this->_redirect("save", "error");
		}
		
		/*
		 |	ADMIN :: RENDER ADMIN PAGE
		 |	@since	0.1.0
		 */
		public function adminPage(){
			global $wp_roles;
			
			if(isset($_GET["dexs"]) && $_GET["dexs"] === "docs"){
				include_once(DEXS_CR_PATH . "system/admin.docs.php");
			} else {
				if(!isset($wp_roles)){
					$wp_roles = new WP_Roles();
				}
				$roles  = $wp_roles->get_names();
				$types  = get_post_types(array("public" => true), "objects");
				$config = $this->config;
				include_once(DEXS_CR_PATH . "system/admin.config.php");
			}
		}
		
		/*
		 |	ADMIN :: RENDER DASHBOARD WIDGET
		 |	@since	0.1.0
		 |	@update	0.1.3
		 */
		public function adminWidget(){
			global $wpdb;
			$table = $wpdb->prefix . "dexs_counter_stats";
			
			// Get last 6 days
			$query = $wpdb->get_results("SELECT * FROM $table ORDER BY stats_date DESC");
			if($query){
				$data = array(
					"labels"	=> array(),
					"total"		=> array(),
					"unique"	=> array()
				);
				
				foreach($query AS $line){
					if(($temp = @unserialize($line->stats_meta)) === false){
						continue;
					}
					array_unshift($data["labels"], $line->stats_date);
					array_unshift($data["total"], 	$temp[0]);
					array_unshift($data["unique"], $temp[1]);
					if(count($data["labels"]) >= 6){
						break;
					}
				}
			}
			
			// Get current day
			$table = $wpdb->prefix . "dexs_counter";
			$query = $wpdb->get_results("SELECT * FROM $table WHERE daily_date = '".date("Y-m-d")."'");
			if($query){
				$today = array(0, 0);
				foreach($query AS $line){
					if(($temp = @unserialize($line->daily_stats)) === false){
						continue;
					}
					$today[0] += (int) $temp["views"][0];
					$today[1] += (int) $temp["views"][1];
				}
				if(!isset($data)){
					$data = array(
						"labels"	=> array(__("Today", DEXS_CR)),
						"total"		=> array($today[0]),
						"unique"	=> array($today[1])
					);
				} else {
					array_push($data["labels"], __("Today", DEXS_CR));
					array_push($data["total"], 	$today[0]);
					array_push($data["unique"], $today[1]);
				}
			} else if(isset($data)){
				array_push($data["labels"], __("Today", DEXS_CR));
				array_push($data["total"],  0);
				array_push($data["unique"], 0);
			}
			if(isset($data)){
				$data["label"] = array(__("Views", DEXS_CR), __("Unique", DEXS_CR));
			}
			
			include_once(DEXS_CR_PATH . "system/admin.widget.php");
		}
	}
