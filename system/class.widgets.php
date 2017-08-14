<?php
/* 
 |	DEXS.COUNTER
 |	@file		./system/class.widgets.php
 |	@author		SamBrishes@pytesNET
 |	@version	0.1.3 [0.1.0] - Alpha
 |
 |	@license	X11 / MIT License
 |	@copyright	Copyright © 2015 - 2016 pytesNET
 */
	if(!defined("DEXS_CR")){ die("Go directly to jail; do not pass go, do not collect 200 cookies."); }
	
	class dexsCounter_viewWidget extends WP_Widget{
		/*
		 |	CONSTRUCTOR
		 |	@since	0.1.0
		 */
		public function __construct(){
			parent::__construct(
				"dexs-view-counter",
				"Dexs ".__("View-Counter List", DEXS_CR),
				array(
					"classname"		=> "dexs-counter dexs-counter-views",
					"description"	=> __("This widget allows you to create a custom content list, depending on the view counter.", DEXS_CR)
				)
			);
		}
		
		/*
		 |	RENDER WIDGET
		 |	@since	0.1.0
		 */
		public function widget($args, $in){
			$title		= isset($in["title"])? esc_attr($in["title"]): "";
			$show_date	= isset($in["show_date"])? esc_attr($in["show_date"]): false;
			$show_view	= isset($in["show_view"])? esc_attr($in["show_view"]): false;
			$post_type	= isset($in["post_type"])? esc_attr($in["post_type"]): "post";
			$items_num	= isset($in["items_num"])? esc_attr($in["items_num"]): "5";
			$order_by1	= isset($in["order_by1"])? esc_attr($in["order_by1"]): "total";
			$order_by2	= isset($in["order_by2"])? esc_attr($in["order_by2"]): "lifetime";
			$limit_cat	= isset($in["limit_cat"])? esc_attr($in["limit_cat"]): "%all";
			$excludeID	= isset($in["excludeID"])? esc_attr($in["excludeID"]): "";
			
			// Get Order
			$orderby = "dexs_views_total";
			$__views = array(__("Total Views:", DEXS_CR), "total");
			if($order_by2 === "lifetime" && $order_by1 === "unique"){
				$orderby = "dexs_views_unique";
				$__views = array(__("Unique Views:", DEXS_CR), "unique");
			} else if($order_by2 === "today" && $order_by1 === "total"){
				$orderby = "dexs_views_today";
				$__views = array(__("Total Views Today:", DEXS_CR), "daily");
			} else if($order_by2 === "today" && $order_by1 === "unique"){
				$orderby = "dexs_views_today_unique";
				$__views = array(__("Unique Views Today:", DEXS_CR), "daily_unique");
			}
			
			// Get Exclude
			$exclude = array();
			if(!empty($excludeID)){
				$excludeID = explode(",", $excludeID);
				foreach($excludeID AS $id){
					$id = trim($id);
					if(is_numeric($id)){ array_push($exclude, $id); }
				}
			}
			
			// Get Query
			$query = array(
				"post_type"				=> $post_type,
				"post_status"			=> "publish",
				"posts_per_page"		=> $items_num,
				"has_password"			=> false,
				"ignore_sticky_posts"	=> true,
				"order"					=> "DESC",
				"orderby"				=> "meta_value_num",
				"meta_key"				=> $orderby
			);
			if($limit_cat !== "%all" && $post_type == "post"){
				$query["cat"] = $limit_cat;
			}
			if(!empty($exclude)){
				$query["post__not_in"] = $exclude;
			}
			$query = new WP_Query($query);
			
			// Render Widget
			if($query->have_posts()){
				echo $args["before_widget"];
				if(!empty($title)){
					echo $args["before_title"].apply_filters("widget_title", $title).$args["after_title"];
					
					?><ul class="dexs-counter-list dexs-counter-views"><?php
					while($query->have_posts()){ $query->the_post();
						?>
							<li class="dexs-counter-item item-<?php the_ID(); ?>">
								<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="item-link">
									<span class="item-title"><?php the_title(); ?></span>
									<?php if($show_date){ ?>
										<span class="item-date"><?php the_time(get_option("date_format")); ?></span>
									<?php } ?>
									<?php if($show_view){ ?>
										<span class="item-views"><?php echo $__views[0]." ".dexs_cr_get_views(get_the_ID(), $__views[1]); ?></span>
									<?php } ?>
								</a>
							</li>
						<?php
					}
					?></ul><?php
				}
				echo $args["after_widget"];
			}
			wp_reset_postdata();
		}
		
		/*
		 |	UPDATE WIDGET
		 |	@since	0.1.0
		 */
		public function update($new, $old){
			global $dexsCounter;
			$types = $dexsCounter->_config("view-types");
			
			$instance["title"]		= esc_attr($new["title"]);
			$instance["show_date"]	= isset($new["show_date"])? true: false;
			$instance["show_view"]	= isset($new["show_view"])? true: false;
			$instance["post_type"]	= esc_attr($new["post_type"]);
			$instance["items_num"]	= esc_attr($new["items_num"]);
			$instance["order_by1"]	= esc_attr($new["order_by1"]);
			$instance["order_by2"]	= esc_attr($new["order_by2"]);
			$instance["limit_cat"]	= esc_attr($new["limit_cat"]);
			$instance["excludeID"]	= esc_attr($new["excludeID"]);
			
			if(get_post_type_object($instance["post_type"]) === null){
				$instance["post_type"] = $types[0];
			}
			if(!is_numeric($instance["items_num"])){
				$instance["items_num"] = "5";
			}
			if(!in_array($instance["order_by1"], array("total", "unique"))){
				$instance["order_by1"] = "total";
			}
			if(!in_array($instance["order_by2"], array("lifetime", "today"))){
				$instance["order_by2"] = "lifetime";
			}
			return $instance;
		}
		
		/*
		 |	WIDGET FORM
		 |	@since	0.1.0
		 */
		public function form($in){
			global $dexsCounter;
			$types = $dexsCounter->_config("view-types");
			
			$title		= isset($in["title"])? esc_attr($in["title"]): "";
			$show_date	= isset($in["show_date"])? esc_attr($in["show_date"]): false;
			$show_view	= isset($in["show_view"])? esc_attr($in["show_view"]): false;
			$post_type	= isset($in["post_type"])? esc_attr($in["post_type"]): "post";
			$items_num	= isset($in["items_num"])? esc_attr($in["items_num"]): "5";
			$order_by1	= isset($in["order_by1"])? esc_attr($in["order_by1"]): "total";
			$order_by2	= isset($in["order_by2"])? esc_attr($in["order_by2"]): "lifetime";
			$limit_cat	= isset($in["limit_cat"])? esc_attr($in["limit_cat"]): "%all";
			$excludeID	= isset($in["excludeID"])? esc_attr($in["excludeID"]): "";
			
			$categories = get_categories(array(
				"order"			=> "ASC",
				"orderby"		=> "name",
				"hide_empty"	=> true,
			));
			?>
				<p>
					<label for="<?php $this->id("title"); ?>"><?php _e("Title", DEXS_CR); ?></label>
					<input type="text" class="widefat" id="<?php $this->id("title"); ?>" 
						name="<?php $this->nm("title"); ?>" value="<?php echo $title; ?>" />
				</p>
				
				<p>
					<input type="checkbox" id="<?php $this->id("show_date"); ?>" value="1" 
						name="<?php $this->nm("show_date"); ?>" <?php checked($show_date); ?> />
					<label for="<?php $this->id("show_date"); ?>"><?php _e("Display Post Date?", DEXS_CR); ?></label><br />
					
					<input type="checkbox" id="<?php $this->id("show_view"); ?>" value="1" 
						name="<?php $this->nm("show_view"); ?>" <?php checked($show_view); ?> />
					<label for="<?php $this->id("show_view"); ?>"><?php _e("Display View Counter?", DEXS_CR); ?></label>
				</p>
				
				<table style="width: 100%;">
					<tr>
						<td style="width: 40%;">
							<label for="<?php $this->id("post_type"); ?>"><?php _e("Post Type", DEXS_CR); ?></label>
						</td>
						<td>
							<select id="<?php $this->id("post_type"); ?>" name="<?php $this->nm("post_type"); ?>">
							<?php foreach($types AS $type){ $type = get_post_type_object($type); ?>
								<option value="<?php echo $type->name; ?>" 
									<?php selected($post_type, $type->name); ?>><?php echo $type->label; ?></option>
							<?php } ?>
							</select>
						</td>
					</tr>
					<tr id="_dexs-<?php $this->id("post_type"); ?>" style="display: none;">
						<td style="width: 40%;">
							<label for="<?php $this->id("limit_cat"); ?>"><?php _e("Category", DEXS_CR); ?></label>
						</td>
						<td>
							<select id="<?php $this->id("limit_cat"); ?>" name="<?php $this->nm("limit_cat"); ?>">
								<option value="%all"><?php _e("All Categories", DEXS_CR); ?></option>
								<?php foreach($categories AS $cat){ ?>
									<option value="<?php echo $cat->term_id; ?>" <?php selected($cat->term_id, $limit_cat); ?>><?php echo $cat->name; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td style="width: 40%;">
							<label for="<?php $this->id("items_num"); ?>"><?php _e("Number of Items", DEXS_CR); ?></label>
						</td>
						<td>
							<input type="number" class="small-text" id="<?php $this->id("items_num"); ?>" 
								name="<?php $this->nm("items_num"); ?>" value="<?php echo $items_num; ?>" />
						</td>
					</tr>
					<tr>
						<td style="width: 40%;">
							<label for="<?php $this->id("order_by1"); ?>"><?php _e("Order By", DEXS_CR); ?></label>
						</td>
						<td>
							<select id="<?php $this->id("order_by1"); ?>" name="<?php $this->nm("order_by1"); ?>">
								<option value="total" <?php selected($order_by1, "total"); ?>><?php _e("Total Views", DEXS_CR); ?></option>
								<option value="unique" <?php selected($order_by1, "unique"); ?>><?php _e("Unique Views", DEXS_CR); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td style="width: 40%;"></td>
						<td>
							<select id="<?php $this->id("order_by2"); ?>" name="<?php $this->nm("order_by2"); ?>">
								<option value="lifetime" <?php selected($order_by2, "lifetime"); ?>><?php _e("LifeTime", DEXS_CR); ?></option>
								<option value="today" <?php selected($order_by2, "today"); ?>><?php _e("for Today", DEXS_CR); ?></option>
							</select>
						</td>
					</tr>
				</table>
				<script type="text/javascript">
					jQuery(document).ready(function($){
						if(typeof(dexsCR_postType) !== "function"){
							var dexsCR_postType = function(element){
								if(element.val() === "post"){
									$(element).parent().parent().nextAll("#_dexs-" + element.attr("id")).css("display", "table-row");
								} else {
									$(element).parent().parent().nextAll("#_dexs-" + element.attr("id")).css("display", "none");
								}
							}
						}
						$("#<?php echo $this->id("post_type"); ?>").on("change", function(){
							dexsCR_postType($("#<?php echo $this->id("post_type"); ?>"));
						});
						dexsCR_postType($("#<?php echo $this->id("post_type"); ?>"));
					});
				</script>
				
				<p>
					<label for="<?php $this->id("excludeID"); ?>"><?php _e("Exclude", DEXS_CR); ?></label>
					<input type="text" class="widefat" id="<?php $this->id("excludeID"); ?>" 
						name="<?php $this->nm("excludeID"); ?>" value="<?php echo $excludeID; ?>" />
					<small><?php _e("Post-Type Content IDs, separated by commas.", DEXS_CR); ?></small>
				</p>
			<?php
		}
		
		/*
		 |	HELPER
		 |	@since	0.1.0
		 */
		private function id($id){
			echo $this->get_field_id($id);
		}
		private function nm($name){
			echo $this->get_field_name($name);
		}
	}
	
	class dexsCounter_rateWidget extends WP_Widget{
		/*
		 |	CONSTRUCTOR
		 |	@since	0.1.0
		 */
		public function __construct(){
			parent::__construct(
				"dexs-rate-counter",
				"Dexs ".__("Rate-Counter List", DEXS_CR),
				array(
					"classname"		=> "dexs-counter dexs-counter-ratings",
					"description"	=> __("This widget allows you to create a custom content list, depending on the rating counter.", DEXS_CR)
				)
			);
		}
		
		/*
		 |	RENDER WIDGET
		 |	@since	0.1.0
		 */
		public function widget($args, $in){
			$title		= isset($in["title"])? esc_attr($in["title"]): "";
			$show_date	= isset($in["show_date"])? esc_attr($in["show_date"]): false;
			$post_type	= isset($in["post_type"])? esc_attr($in["post_type"]): "post";
			$items_num	= isset($in["items_num"])? esc_attr($in["items_num"]): "5";
			$order_by1	= isset($in["order_by1"])? esc_attr($in["order_by1"]): "best";
			$limit_cat	= isset($in["limit_cat"])? esc_attr($in["limit_cat"]): "%all";
			$excludeID	= isset($in["excludeID"])? esc_attr($in["excludeID"]): "";
			
			// Get Order
			$order = "DESC"; $orderby = "dexs_ratings";
			if($order_by1 === "most"){
				$order = "DESC"; $orderby = "dexs_ratings_num";
			} else if($order_by1 === "worst"){
				$order = "ASC"; $orderby = "dexs_ratings";
			} else if($order_by1 === "fewest"){
				$order = "ASC"; $orderby = "dexs_ratings_num";
			}
			
			// Get Exclude
			$exclude = array();
			if(!empty($excludeID)){
				$excludeID = explode(",", $excludeID);
				foreach($excludeID AS $id){
					$id = trim($id);
					if(is_numeric($id)){ array_push($exclude, $id); }
				}
			}
			
			// Get Query
			$query = array(
				"post_type"				=> $post_type,
				"post_status"			=> "publish",
				"posts_per_page"		=> $items_num,
				"has_password"			=> false,
				"ignore_sticky_posts"	=> true,
				"order"					=> $order,
				"orderby"				=> "meta_value_num",
				"meta_key"				=> $orderby
			);
			if($limit_cat !== "%all" && $post_type == "post"){
				$query["cat"] = $limit_cat;
			}
			if(!empty($exclude)){
				$query["post__not_in"] = $exclude;
			}
			$query = new WP_Query($query);
			
			// Render Widget
			if($query->have_posts()){
				echo $args["before_widget"];
				if(!empty($title)){
					echo $args["before_title"].apply_filters("widget_title", $title).$args["after_title"];
					
					?><ul class="dexs-counter-list dexs-counter-ratings"><?php
					while($query->have_posts()){ $query->the_post();
						?>
							<li class="dexs-counter-item item-<?php the_ID(); ?>">
								<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="item-link">
									<span class="item-title"><?php the_title(); ?></span>
									<?php if($show_date){ ?>
										<span class="item-date"><?php the_time(get_option("date_format")); ?></span>
									<?php } ?>
								</a>
							</li>
						<?php
					}
					?></ul><?php
				}
				echo $args["after_widget"];
			}
			
			wp_reset_postdata();
		}
		
		/*
		 |	UPDATE WIDGET
		 |	@since	0.1.0
		 */
		public function update($new, $old){
			global $dexsCounter;
			$types = $dexsCounter->_config("view-types");
			
			$instance["title"]		= esc_attr($new["title"]);
			$instance["show_date"]	= isset($new["show_date"])? true: false;
			$instance["post_type"]	= esc_attr($new["post_type"]);
			$instance["items_num"]	= esc_attr($new["items_num"]);
			$instance["order_by1"]	= esc_attr($new["order_by1"]);
			$instance["limit_cat"]	= esc_attr($new["limit_cat"]);
			$instance["excludeID"]	= esc_attr($new["excludeID"]);
			
			if(get_post_type_object($instance["post_type"]) === null){
				$instance["post_type"] = $types[0];
			}
			if(!is_numeric($instance["items_num"])){
				$instance["items_num"] = "5";
			}
			if(!in_array($instance["order_by1"], array("best", "most", "worst", "fewest"))){
				$instance["order_by1"] = "best";
			}
			return $instance;
		}
		
		/*
		 |	WIDGET FORM
		 |	@since	0.1.0
		 */
		public function form($in){
			global $dexsCounter;
			$types = $dexsCounter->_config("view-types");
			
			$title		= isset($in["title"])? esc_attr($in["title"]): "";
			$show_date	= isset($in["show_date"])? esc_attr($in["show_date"]): false;
			$post_type	= isset($in["post_type"])? esc_attr($in["post_type"]): "post";
			$items_num	= isset($in["items_num"])? esc_attr($in["items_num"]): "5";
			$order_by1	= isset($in["order_by1"])? esc_attr($in["order_by1"]): "best";
			$limit_cat	= isset($in["limit_cat"])? esc_attr($in["limit_cat"]): "%all";
			$excludeID	= isset($in["excludeID"])? esc_attr($in["excludeID"]): "";
			
			$categories = get_categories(array(
				"order"			=> "ASC",
				"orderby"		=> "name",
				"hide_empty"	=> true,
			));
			
			?>
				<p>
					<label for="<?php $this->id("title"); ?>"><?php _e("Title", DEXS_CR); ?></label>
					<input type="text" class="widefat" id="<?php $this->id("title"); ?>" 
						name="<?php $this->nm("title"); ?>" value="<?php echo $title; ?>" />
				</p>
				
				<p>
					<input type="checkbox" id="<?php $this->id("show_date"); ?>" value="1" 
						name="<?php $this->nm("show_date"); ?>" <?php checked($show_date); ?> />
					<label for="<?php $this->id("show_date"); ?>"><?php _e("Display Post Date?", DEXS_CR); ?></label><br />
				</p>
				
				<table style="width: 100%;">
					<tr>
						<td style="width: 40%;">
							<label for="<?php $this->id("post_type"); ?>"><?php _e("Post Type", DEXS_CR); ?></label>
						</td>
						<td>
							<select id="<?php $this->id("post_type"); ?>" name="<?php $this->nm("post_type"); ?>">
							<?php foreach($types AS $type){ $type = get_post_type_object($type); ?>
								<option value="<?php echo $type->name; ?>" 
									<?php selected($post_type, $type->name); ?>><?php echo $type->label; ?></option>
							<?php } ?>
							</select>
						</td>
					</tr>
					<tr id="_dexs-<?php $this->id("post_type"); ?>" style="display: none;">
						<td style="width: 40%;">
							<label for="<?php $this->id("limit_cat"); ?>"><?php _e("Category", DEXS_CR); ?></label>
						</td>
						<td>
							<select id="<?php $this->id("limit_cat"); ?>" name="<?php $this->nm("limit_cat"); ?>">
								<option value="%all"><?php _e("All Categories", DEXS_CR); ?></option>
								<?php foreach($categories AS $cat){ ?>
									<option value="<?php echo $cat->term_id; ?>" <?php selected($cat->term_id, $limit_cat); ?>><?php echo $cat->name; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td style="width: 40%;">
							<label for="<?php $this->id("items_num"); ?>"><?php _e("Number of Items", DEXS_CR); ?></label>
						</td>
						<td>
							<input type="number" class="small-text" id="<?php $this->id("items_num"); ?>" 
								name="<?php $this->nm("items_num"); ?>" value="<?php echo $items_num; ?>" />
						</td>
					</tr>
					<tr>
						<td style="width: 40%;">
							<label for="<?php $this->id("order_by1"); ?>"><?php _e("Order By", DEXS_CR); ?></label>
						</td>
						<td>
							<select id="<?php $this->id("order_by1"); ?>" name="<?php $this->nm("order_by1"); ?>">
								<option value="best" <?php selected($order_by1, "best"); ?>><?php _e("best-rated", DEXS_CR); ?></option>
								<option value="most" <?php selected($order_by1, "most"); ?>><?php _e("most-rated", DEXS_CR); ?></option>
								<option value="worst" <?php selected($order_by1, "worst"); ?>><?php _e("worst-rated", DEXS_CR); ?></option>
								<option value="fewest" <?php selected($order_by1, "fewest"); ?>><?php _e("fewest-rated", DEXS_CR); ?></option>
							</select>
						</td>
					</tr>
				</table>
				<script type="text/javascript">
					jQuery(document).ready(function($){
						if(typeof(dexsCR_postType) !== "function"){
							var dexsCR_postType = function(element){
								if(element.val() === "post"){
									$(element).parent().parent().nextAll("#_dexs-" + element.attr("id")).css("display", "table-row");
								} else {
									$(element).parent().parent().nextAll("#_dexs-" + element.attr("id")).css("display", "none");
								}
							}
						}
						$("#<?php echo $this->id("post_type"); ?>").on("change", function(){
							dexsCR_postType($("#<?php echo $this->id("post_type"); ?>"));
						});
						dexsCR_postType($("#<?php echo $this->id("post_type"); ?>"));
					});
				</script>
				
				<p>
					<label for="<?php $this->id("excludeID"); ?>"><?php _e("Exclude", DEXS_CR); ?></label>
					<input type="text" class="widefat" id="<?php $this->id("excludeID"); ?>" 
						name="<?php $this->nm("excludeID"); ?>" value="<?php echo $excludeID; ?>" />
					<small><?php _e("Post-Type Content IDs, separated by commas.", DEXS_CR); ?></small>
				</p>
			<?php
		}
		
		/*
		 |	HELPER
		 |	@since	0.1.0
		 */
		private function id($id){
			echo $this->get_field_id($id);
		}
		private function nm($name){
			echo $this->get_field_name($name);
		}
	}
