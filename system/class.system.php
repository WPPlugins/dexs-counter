<?php
/* 
 |	DEXS.COUNTER
 |	@file		./system/class.system.php
 |	@author		SamBrishes@pytesNET
 |	@version	0.1.3 [0.1.0] - Alpha
 |
 |	@license	X11 / MIT License
 |	@copyright	Copyright © 2015 - 2016 pytesNET
 */
	if(!defined("DEXS_CR")){ die("Go directly to jail; do not pass go, do not collect 200 cookies."); }
	
	class dexsCounter_System{
		/*
		 |	GLOBAL VARs
		 */
		private $config = array();
		private $default = array(
			"daily_stats"			=> array(
				"views" 	=> array(0, 0), 
				"ratings" 	=> array(0, 0, 0, 0, 0)
			),
			"daily_date"			=> "",
			"daily_meta"			=> array(),
			"counter_views"			=> array(0, 0),
			"counter_views_meta"	=> array(),
			"counter_ratings"		=> array(0, 0, 0, 0, 0),
			"counter_ratings_meta"	=> array()
		);
		
		/*
		 |	CONSTRUCTOR
		 |	@since	0.1.0
		 |	@update	0.1.1
		 */
		public function __construct($config){
			$this->config = $config;
			
			add_action("wp_head", array($this, "_script"));
			add_action("wp_footer", array($this, "_script"));
		}

		/*
		 |	HELPER :: GET IP
		 |	@since	0.1.0
		 |	@update	0.1.0
		 */
		private function _ip(){
			$ip = "127.0.0.1";
			$check = array(
				"HTTP_CLIENT_IP", "HTTP_FORWARDED_FOR", "HTTP_FORWARDED", "REMOTE_ADDR"
			);
			foreach($check AS $c){
				if(isset($_SERVER[$c]) && filter_var($_SERVER[$c], FILTER_VALIDATE_IP)){
					$ip = $_SERVER[$c];
					break;
				}
			}
			return crypt($ip, "ip");
		}

		/*
		 |	HELPER :: CHECK IF CALL IS FROM A HUMAN
		 |	@since	0.1.1
		 */
		private function is_human(){
			// Exclude (some) Bots
			if(isset($_SERVER["HTTP_FROM"]) && !empty($_SERVER["HTTP_FROM"])){
				foreach(array("bot", "google", "yahoo", "bing", "spider", "crawl") AS $item){
					if(stripos($_SERVER["HTTP_FROM"], $item) !== false){
						return false;
					}
				}
			}
			if(isset($_SERVER["HTTP_USER_AGENT"]) && !empty($_SERVER["HTTP_USER_AGENT"])){
				foreach(array("bot", "crawl", "spider", "slurp", "developers.google.com") AS $item){
					if(stripos($_SERVER["HTTP_USER_AGENT"], $item) !== false){
						return false;
					}
				}
			}
			
			// JavaScript Cookie
			if(isset($_SERVER["HTTP_COOKIE"])){
				if(isset($_COOKIE["dexs_verify"]) && $_COOKIE["dexs_verify"] == "verified"){
					return true;
				}
				return false;
			}
			
			// Exclude Multiple IPs
			$user = $this->_ip();
			$option = get_option("dexs-counter-verify", array());
			if(is_array($option) && !empty($option)){
				if(array_key_exists($user, $option)){
					if((time() - $option[$user]) < 30){
						$option[$user] = time();
						update_option("dexs-counter-verify", $option, "no");
						return false;
					}
				}
			}
			$option[$user] = time();
			update_option("dexs-counter-verify", $option, "no");
			return true;
		}
		
		/*
		 |	HELPER :: GET ROW
		 |	@since	0.1.0
		 */
		private function _getRow($post_id){
			global $wpdb;
			$table = $wpdb->prefix . "dexs_counter";
			
			// Check
			if(!is_numeric($post_id)){
				return false;
			}
			$post_id = (int) $post_id;
			
			// Check | Return
			$data = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM $table WHERE post_id = %d",
				array($post_id)
			), ARRAY_A);
			
			if($data === NULL){
				return $this->default;
			}
			foreach($data AS $key => $value){
				if(($temp = @unserialize($value)) !== false){
					$data[$key] = $temp;
				}
			}
			return $data;
		}
		
		/*
		 |	HELPER :: UPDATE ROW
		 |	@since	0.1.0
		 */
		private function _updateRow($post_id, $data){
			global $wpdb;
			$table = $wpdb->prefix . "dexs_counter";
			
			// Check | Serialize
			if(!is_numeric($post_id)){
				return false;
			}
			$post_id = (int) $post_id;
			
			$update = array();
			foreach($data AS $key => $value){
				if(is_array($value)){
					$update[$key] = serialize($value);
				} else {
					$update[$key] = $value;
				}
			}
			
			// Insert | Update Data
			if(isset($update["counter_id"])){
				$counter_id = $update["counter_id"];
				unset($update["counter_id"]);
				
				if($wpdb->update($table, $update, array("counter_id" => $counter_id)) === false){
					return false;
				}
			} else {
				$check = $wpdb->query($wpdb->prepare(
					"INSERT INTO $table (post_id, ".implode(", ", array_keys($update)).")
						VALUES (%d, '".implode("', '", array_values($update))."')",
					array($post_id)
				));
				if($check === false){
					return false;
				}
			}
			
			// Insert | Update Meta
			$meta = array(
				"dexs_views_total"			=> $data["counter_views"][0],
				"dexs_views_unique"			=> $data["counter_views"][1],
				"dexs_views_today"			=> $data["daily_stats"]["views"][0],
				"dexs_views_today_unique"	=> $data["daily_stats"]["views"][1],
				"dexs_ratings"				=> $this->_calcRatings($data["counter_ratings"]),
				"dexs_ratings_num"			=> $this->_countRatings($data["counter_ratings"])
			);
			foreach($meta AS $key => $value){
				if(in_array($key, array("dexs_ratings", "dexs_ratings_num"))){
					if($value == 0){
						if(get_post_meta($post_id, $key, true) !== ""){
							delete_post_meta($post_id, $key);
						}
						continue;
					}
				}
				
				if(get_post_meta($post_id, $key, true) === ""){
					add_post_meta($post_id, $key, $value, true);
				} else {
					update_post_meta($post_id, $key, $value);
				}
			}
			return true;
		}
		
		/*
		 |	ACTION :: VERIFY COUNTER
		 |	@since	0.1.1
		 */
		public function _script(){
			if(is_admin() || is_preview() || !is_singular()){
				return;
			}
			
			// Create Cookie
			$value  = "dexs_verify=verified";
			$value .= ";domain=".$_SERVER['HTTP_HOST'];
			if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on"){
				$value .= ";secure";
			}
			
			// Add Cookie
			if(current_filter() === "wp_head"){
				?>
					<script type="text/javascript">
						if(navigator.cookieEnabled){
							if(document.cookie.indexOf("dexs_verify=") === -1){
								document.cookie = "<?php echo $value; ?>";
								window.location.reload(true);
							}
						}
					</script>
				<?php
			}
			
			// Remove Cookie
			if(current_filter() === "wp_footer"){
				$this->_counter();
				?>
					<script type="text/javascript">
						document.cookie = "<?php echo $value; ?>;expires=Thu, 01-Jan-1970 00:00:01 GMT";
					</script>
				<?php
			}
		}
		
		/*
		 |	ACTION :: FRONTEND COUNTER HOOK
		 |	@since	0.1.0
		 |	@update	0.1.1
		 */
		private function _counter(){
			if(is_admin() || is_preview()){
				return;
			}
			if(!is_singular($this->config["view-types"])){
				$break_view = true;
			}
			if(!is_singular($this->config["rating-types"])){
				$break_rate = true;
			}
			
			// Check User Role
			$user = wp_get_current_user();
			if($user->ID !== 0){
				foreach($user->roles AS $role){
					if(in_array($role, $this->config["view-excludes"])){
						$break_view = true;
					}
					if(in_array($role, $this->config["rating-excludes"])){
						$break_rate = true;
					}
				}
				$user = "_".(string) $user->ID;
			} else {
				$user = $this->_ip();
			}
			
			// Verify View
			if(isset($_GET["dexs-nonce"]) || isset($_GET["dexs"]) || !$this->is_human()){
				$break_view = true;
			}
			
			// Verify Rating
			if(!isset($_GET["dexs-nonce"]) || !isset($_GET["dexs"])){
				$break_rate = true;
			} else {
				if(!$this->_verifyRating(esc_attr($_GET["dexs-nonce"]), esc_attr($_GET["dexs"]))){
					$break_rate = true;
				}
			}
			
			// Count
			if(!isset($break_view)){
				$this->_countView(get_the_ID(), $user);
			}
			if(!isset($break_rate)){
				$this->_countRate(get_the_ID(), $user, $_GET["dexs"]);
			}
		}
		
		/*
		 |	STATS COUNTER
		 |	@since	0.1.0
		 */
		public function _countStats(){
			global $wpdb;
			$table1 = $wpdb->prefix . "dexs_counter";
			$table2 = $wpdb->prefix . "dexs_counter_stats";
			
			// Get latest stats date
			$query = $wpdb->get_results("SELECT stats_date FROM $table2 ORDER BY stats_date DESC LIMIT 1");
			if($query){
				foreach($query AS $line){
					$old_date = $line->stats_date;
				}
			}
			
			// Get current stats data
			$data = array();
			$query = $wpdb->get_results("SELECT * FROM $table1 ORDER BY daily_date DESC");
			if(!$query){
				return false;
			}
			
			foreach($query AS $line){
				if($line->daily_date >= date("Y-m-d")){
					continue;	// Continue, because this stats are from today.
				}
				if(isset($old_date) && $line->daily_date <= $old_date){
					break;		// Break, because this stats are already stored.
				}
				
				// Add data array
				if(!isset($data[$line->daily_date])){
					$data[$line->daily_date] = array(
						"views"		=> array(),
						"ratings"	=> array(),
						"meta"		=> array(0, 0, 0)
					);
				}
				$cur = &$data[$line->daily_date];
				
				// Add daily data
				if(($temp = @unserialize($line->daily_stats)) !== false){
					$cur["views"][$line->post_id] = $temp["views"];
					$cur["ratings"][$line->post_id] = $temp["ratings"];
					
					$cur["meta"][0] = $cur["meta"][0] + $temp["views"][0];
					$cur["meta"][1] = $cur["meta"][1] + $temp["views"][1];
					$cur["meta"][2] = $cur["meta"][2] + $this->_countRatings($temp["ratings"]);
				}
			}
			
			// Store data
			if(empty($data)){
				return false;
			}
			foreach($data AS $datetime => $stats){
				$check = $wpdb->insert(
					$table2,
					array(
						"stats_date"	=> $datetime,
						"stats_views"	=> serialize($stats["views"]),
						"stats_ratings" => serialize($stats["ratings"]),
						"stats_meta"	=> serialize($stats["meta"])
					),
					array("%s", "%s", "%s", "%s")
				);
				if($check === false){
					return false;
				}
			}
			return true;
		}
		
		/*
		 |	VIEW COUNTER++
		 |	@since	0.1.0
		 */
		private function _countView($post_id, $user){
			$data = $this->_getRow($post_id);
			if($data === false){
				return false;
			}
			
			// Count Total | Unique
			$data["counter_views"][0]++;
			if(!in_array($user, $data["counter_views_meta"])){
				$data["counter_views"][1]++;
				array_push($data["counter_views_meta"], $user);
			}
			
			// Count Daily
			if($data["daily_date"] === date("Y-m-d")){
				$data["daily_stats"]["views"][0]++;
				
				if(!in_array($user, $data["daily_meta"])){
					$data["daily_stats"]["views"][1]++;
					array_push($data["daily_meta"], $user);
				}
			} else {
				if($this->config["enable-statistics"]){
					$this->_countStats();
				}
				$data["daily_date"] = date("Y-m-d");
				$data["daily_meta"] = array($user);
				$data["daily_stats"]["views"] = array(1, 1);
				$data["daily_stats"]["ratings"] = $this->default["daily_stats"]["ratings"];
			}
			return $this->_updateRow($post_id, $data);
		}
		
		/*
		 |	GET VIEW COUNTER
		 |	@since	0.1.0
		 */
		public function getView($post_id, $type = "total"){
			$data = $this->_getRow($post_id);
			if($data === false){
				return false;
			}
			
			if($type === "daily"){
				return (int) $data["daily_stats"][0];
			} else if($type === "daily_unique"){
				return (int) $data["daily_stats"][1];
			} else if($type === "unique"){
				return (int) $data["counter_views"][1];
			}
			return (int) $data["counter_views"][0];
		}
		
		/*
		 |	HAS USER ALREADY VIEWED
		 |	@since	0.1.0
		 */
		public function hasViewed($post_id){
			$data = $this->_getRow($post_id);
			if($data === false){
				return false;
			}
			
			// Check User Role
			$user = wp_get_current_user();
			if($user->ID !== 0){
				foreach($user->roles AS $role){
					if(in_array($role, $this->config["view-excludes"])){
						return true;
					}
				}
				$user = "_".(string) $user->ID;
			} else {
				$user = $this->_ip();
			}
			
			// Check User
			if(!in_array($user, $data["counter_views_meta"])){
				return false;
			}
			return true;
		}
		
		/*
		 |	HELPER :: VERIFY RATING
		 |	@since	0.1.0
		 */
		private function _verifyRating($nonce, $rating){
			// Verify Setting
			$values = array(
				"single"	=> array("like"),
				"double"	=> array("like", "dislike"),
				"3-level"	=> array("1", "2", "3"),
				"5-level"	=> array("1", "2", "3", "4", "5")
			);
			if(!array_key_exists($this->config["rating-layout"], $values)){
				return false;
			}
			
			// Verify Layout
			$values = $values[$this->config["rating-layout"]];
			if(!in_array($rating, $values)){
				return false;
			}
			
			// Verify Nonce
			if(!wp_verify_nonce($nonce, $rating)){
				return false;
			}
			return true;
		}
		
		/*
		 |	HELPER :: CALCULATE RATING
		 |	@since	0.1.0
		 */
		private function _calcRatings($data){
			if(!is_array($data) || count($data) < 5){
				return 0;
			}
			$data = array_values($data);
			
			$count  = $data[0]*1;
			$count += $data[1]*2;
			$count += $data[2]*3;
			$count += $data[3]*4;
			$count += $data[4]*5;
			return $count;
		}
		
		/*
		 |	HELPER :: COUNT RATING
		 |	@since	0.1.0
		 */
		private function _countRatings($data){
			if(!is_array($data) || count($data) < 5){
				return 0;
			}
			$data = array_values($data);
			return $data[0]+$data[1]+$data[2]+$data[3]+$data[4];
		}
		
		/*
		 |	RATE COUNTER++
		 |	@since	0.1.0
		 */
		private function _countRate($post_id, $user, $rating){
			$data = $this->_getRow($post_id);
			if($data === false){
				return false;
			}
			
			// Check User
			if(array_key_exists($user, $data["counter_ratings_meta"])){
				if($data["counter_ratings_meta"][$user] === $rating){
					return false;
				}
				$remove = $data["counter_ratings_meta"][$user];
			}
			$data["counter_ratings_meta"][$user] = $rating;
			
			// Check Daily Stats
			if($data["daily_date"] !== date("Y-m-d")){
				if($this->config["enable-statistics"]){
					$this->_countStats();
				}
				$data["daily_date"] = date("Y-m-d");
				$data["daily_meta"] = array();
				$data["daily_stats"]["views"] = array(1, 1);
				$data["daily_stats"]["ratings"] = $this->default["daily_stats"]["ratings"];
			}
			
			// Get Rating
			$rating = array($rating);
			if(isset($remove)){
				array_push($rating, $remove);
			}
			
			for($i = 0; $i < count($rating); $i++){
				if($rating[$i] === "like"){ $rating[$i] = 5; }
				if($rating[$i] === "dislike"){ $rating[$i] = 1; }
				$rating[$i] = (int) $rating[$i];
				
				if($i === 0){
					$data["counter_ratings"][$rating[$i]-1] += 1;
					$data["daily_stats"]["ratings"][$rating[$i]-1] += 1;
				} else {
					$data["counter_ratings"][$rating[$i]-1] -= 1;
					$data["daily_stats"]["ratings"][$rating[$i]-1] -= 1;
				}
			}
			return $this->_updateRow($post_id, $data);
		}
		
		/*
		 |	GET RATE COUNTER
		 |	@since	0.1.0
		 */
		public function getRating($post_id){
			$data = $this->_getRow($post_id);
			if($data === false){
				return false;
			}
			
			$rating = $data["counter_ratings"];
			if($this->config["rating-layout"] === "single"){
				$rating = array("like" => $rating[4]);
			} else if($this->config["rating-layout"] === "double"){
				$rating = array("like" => $rating[4], "dislike" => $rating[0]);
			} else {
				$_rating = array();
				for($i = 0; $i < count($rating); $i++){
					if($this->config["rating-layout"] === "3-level"){
						if($i == 0 || $i == 2 || $i === 4){
							$_rating[(string) ($i+1)] = $rating[$i];
						}
					} else {
						$_rating[(string) ($i+1)] = $rating[$i];
					}
				}
				$rating = $_rating;
			}
			return $rating;
		}
		
		/*
		 |	HAS USER ALREADY RATED
		 |	@since	0.1.0
		 */
		public function hasRated($post_id, $value = false){
			$data = $this->_getRow($post_id);
			if($data === false){
				return false;
			}
			
			// Check User Role
			$user = wp_get_current_user();
			if($user->ID !== 0){
				foreach($user->roles AS $role){
					if(in_array($role, $this->config["rating-excludes"])){
						return NULL;
					}
				}
				$user = "_".(string) $user->ID;
			} else {
				$user = $this->_ip();
			}
			
			// Check User
			if(!array_key_exists($user, $data["counter_ratings_meta"])){
				if($value){ return ""; }
				return false;
			}
			if($value){ return $data["counter_ratings_meta"][$user]; }
			return true;
		}
	}
