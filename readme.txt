=== Dexs.Counter ===
Contributors: sambrishes
Tags: count, counter, view, views, rating, ratings, dashboard, widget, statistics
Requires at least: 4.0
Tested up to: 4.7
Stable tag: 0.1.3
License: X11 / MIT License
License URI: https://opensource.org/licenses/MIT

A simple but powerful view counter and rating system, with own (dashboard) widgets, for your awesome WordPress website.

== Description ==
The Dexs.Counter plugin detects each (unique) view and stores the collected data into the own 
database table as well as own post_meta, which can be used to configure WP_Query loops depending on 
the counter. This informations are already used in the 2 frontend and in the small dashboard widget. 
The plugin contains also an extended statistic, which shows the daily views (of all posts) as 
diagram within the dashboard widget. Last but not Least: The plugin offers also a small rating system.

= Alpha =
This plugin is still Alpha and will be expanded in the future. However, it should not contain 
critical bugs, but if you found one or if you even have general suggestions for improvement then 
write us on our [WordPress Plugin Support Page](http://wordpress.org/support/plugin/dexs-counter).

Thank You!

= Features =
-	Post Types: En/Disable the view and rating counter on each single (costum) post type.
-	Exclude Roles: In/Exclude user roles from the view- and rating system.
-	Rating Layout: Choose between a single (Like), double (Like, Dislike), 3-level and 5-level rating layout.
-	Frontend Widgets: 2 Frontend widgets which allows you to display post lists depending on the view and rate counter.
-	Extended Statistics: Logs each day-counter and displays this informations within the dashboard widget.

== Installation ==
1.	Upload the `dexs-counter` folder to your `/wp-content/plugins/` directory
2.	Activate the plugin through the 'Plugins' menu in WordPress
3.	Configure the plugin through the own 'Dexs Counter' Admin Page.
4.	Use It and rate our plugin on the official [WordPress Plugin Directory](http://plugins.wordpress.org/dexs-counter) ;3!

== Frequently Asked Questions ==
You can use the dexs counter environment easily within your own WordPress theme. This is useful, if 
you want to adapt the dexs counter design seamless to your theme. Please Note: You should always 
check, if the respective function exists with the php `function_exists(function_name)` method! 

= dexs_cr_get_views ($post_id, $type) =
Get one of the four view counter numbers for the respective post. 

__Parameters__

-	**$post_id** *(Required)*<br />
	The respective post id. Use the `get_the_ID()` WordPress function within a loop. 
-	**$type** *(Optional)*<br />
	Set the view counter type by using one of the following strings
-	-	`"total"` returns all registered clicks. (Default)
-	-	`"daily"` returns all registered clicks for the current day.
-	-	`"unique"` returns all unique clicks (one click / user).
-	-	`"daily_unique"` returns all unique clicks (one click / user) for the current day. 

__Return Values__

Returns the view counter number as **integer** or **false** if the $post_id parameter in invalid. 

= dexs_cr_has_viewed ($post_id) =
Checks if the current user has already clicked on the respective post. 

__Parameters__

-	**$post_id** *(Required)*<br />
	The respective post id. Use the `get_the_ID()` WordPress function within a loop. 

__Return Values__

Returns **true** if the user has already clicked on the post, and **false** if not or if the 
$post_id parameter is invalid! 

= dexs_cr_get_rating ($post_id) =
Get the rating data for the respective post. 

__Parameters__

-	**$post_id** *(Required)*<br />
	The respective post id. Use the `get_the_ID()` WordPress function within a loop. 

__Return Values__

Returns an **array** with the rating data or **false** on failure. Note: The rating data (or rather 
the array keys) depends on your rating layout setting (see below), but the values are always 
**integers** an represents the number of ratings / clicks on the respective rating value.

-	On **"Single"**: `array("like" => [int])`
-	On **"Double"**: `array("like" => [int], "dislike" => [int])`
-	On **"3-Level"**: `array("1" => [int], "2" => [int], "3" => [int])`
-	On **"5-Level"**: `array("1" => [int], "2" => [int], "3" => [int], "4" => [int], "5" => [int])`

= dexs_cr_has_rated ($post_id, $value) =
Checks if the current user has already rated yet and returns perhaps his rating value. 

__Parameters__

-	**$post_id** *(Required)*<br />
	The respective post id. Use the `get_the_ID()` WordPress function within a loop. 
-	**$value** *(Optional)*<br />
	Change the returning value:
-	-	`false` checks only if the user has already rated. (Default)
-	-	`true` checks if the user has already rated and returns the rate value. 

__Return Values__

`($value == false):`<br />
Returns **true** if the user has already rated yet or **false** if not or if the $post_id is invalid. 
Note: Returns **NULL** if the current user is within an excluded role!

`($value == true):`<br />
Returns the respective rating value as **string** or an empty **string** if the current user hasn't 
rated yet. Note: Returns **false** if the $post_id is invalid and **NULL** if the current user is 
within an excluded role

= dexs_cr_rating_link ($post_id, $value) =

__Parameters__
Creates one or more rating links with an WordPress nonce. Example: 
http://www.example.com/post_permalink/?dexs-nonce=_nonce_&dexs=rating_value 

-	**$post_id** *(Required)*<br />
	The respective post id. Use the `get_the_ID()` WordPress function within a loop. 
-	**$value** *(Optional)*<br />
	Configure the returning value:
-	-	`NULL` all rating values, depending on the rating layout. (Default)
-	-	`*value*` checks if the user has already rated and returns the rate value. 

__Return Values__

`($value == NULL):`<br />
Returns all rating links as **array** or **false* on failure. Note: The array keys are the 
respective rating values, depending on the rating layout setting!

`($value == value):`<br />
Returns the respective rating link as **string** or **false** on failure. Note: Returns also false 
if the given value doesn't match to the rating layout setting! 

= WP_Query =
You can use the following settings to change the post order, of your costum WP_Query requests, 
depending to view counter OR rating counter. Note: You may return an empty post list, if you use the 
daily or daily_unique counter and if nobody has viewed a single post item on the day yet. Note also: 
The Dexs.Counter plugin doesn't automatically add the counter meta data to all of your posts. So 
each post that havn't been clicked / rated yet are therefore not considered! 

`&lt;?php
	// The Most-Viewed Posts (Total)
	$query = array(
		"order"		=> "DESC",
		"orderby"	=> "meta_value_num",
		"meta_key"	=> "dexs_views_total"
	);
	$posts = new WP_Query($query);
	
	// The Most-Viewed Posts (Unique)
	$query = array(
		"order"		=> "DESC",
		"orderby"	=> "meta_value_num",
		"meta_key"	=> "dexs_views_unique"
	);
	$posts = new WP_Query($query);
	
	// The Most-Viewed Posts (Total / Today)
	$query = array(
		"order"		=> "DESC",
		"orderby"	=> "meta_value_num",
		"meta_key"	=> "dexs_views_today"
	);
	$posts = new WP_Query($query);
	
	// The Most-Viewed Posts (Unique / Today)
	$query = array(
		"order"		=> "DESC",
		"orderby"	=> "meta_value_num",
		"meta_key"	=> "dexs_views_today_unique"
	);
	$posts = new WP_Query($query);
	
	// The Best-Rated Posts
	$query = array(
		"order"		=> "DESC",
		"orderby"	=> "meta_value_num",
		"meta_key"	=> "dexs_ratings"
	);
	$posts = new WP_Query($query);
	
	// The Worst-Rated Posts
	$query = array(
		"order"		=> "ASC",
		"orderby"	=> "meta_value_num",
		"meta_key"	=> "dexs_ratings"
	);
	$posts = new WP_Query($query);
	
	// The Most-Rated Posts
	$query = array(
		"order"		=> "DESC",
		"orderby"	=> "meta_value_num",
		"meta_key"	=> "dexs_ratings_num"
	);
	$posts = new WP_Query($query);
	
	// The Fewest-Rated Posts
	$query = array(
		"order"		=> "ASC",
		"orderby"	=> "meta_value_num",
		"meta_key"	=> "dexs_ratings_num"
	);
	$posts = new WP_Query($query);
?&gt;
`

== Screenshots ==
1.	The settings page.
2.	The dashboard widget.

== Changelog ==

= Version 0.1.3 (Alpha) =
-	[FIX] Error on an empty counter diagram within the dashboard widget.

= Version 0.1.2 (Alpha) =
-	[UPD] The design and data within the counter diagram.
-	[FIX] The counter diagram datasets had the wrong direction.

= Version 0.1.1 (Alpha) =
-	[ADD] Trivial "Humanity Test" for the view counter.
-	[ADD] The `dexs-counter-verify` option as part of the new "Humanity Test".
-	[FIX] The `_ip()` function has hashed the wrong value.

= Version 0.1.0 (Alpha) =
-	First Release

== Upgrade Notice ==
None