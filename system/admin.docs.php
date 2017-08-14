<?php
/* 
 |	DEXS.COUNTER
 |	@file		./system/admin.docs.php
 |	@author		SamBrishes@pytesNET
 |	@version	0.1.3 [0.1.0] - Alpha
 |
 |	@license	X11 / MIT License
 |	@copyright	Copyright © 2015 - 2016 pytesNET
 */
	if(!defined("DEXS_CR")){ die("Go directly to jail; do not pass go, do not collect 200 cookies."); }
?>
<div class="dexs-wrap">
	<div class="dexs-header">
		<div class="dexs-logo">
			<span class="dexs-title">Dexs.</span>
			<span class="dexs-subtitle">Counter</span>
		</div>
		<ul class="dexs-menu">
			<li class="dexs-item"><a href="<?php echo admin_url("options-general.php?page=dexs-counter"); ?>"><?php _e("Settings"); ?></a></li>
			<li class="dexs-item active"><a href="<?php echo admin_url("options-general.php?page=dexs-counter&dexs=docs"); ?>"><?php _e("Documentation"); ?></a></li>
		</ul>
		<div class="dexs-love"><span class="dashicons dashicons-heart"></span></div>
	</div>
	
	<div class="dexs-content">
		<div id="dexs-documentation" class="dexs-page-sidebar">
			<h1>Documentation</h1>
			<h2 id="doc-functions">Function Reference</h2>
			<p>
				You can use the dexs counter environment within your WordPress theme with the 
				following functions. This is useful, if you want to adapt the dexs counter design 
				seamless to your theme. Please Note: You should always check, if the respective 
				function exists with the php <code>function_exists(function_name)</code> method!
			</p>
			<h3 id="doc-get_views">dexs_cr_get_views ($post_id, $type)</h3>
			<p>
				Get one of the four view counter numbers for the respective post.
			</p>
			
			<h4>Parameters</h4>
			<dl>
				<dt>$post_id</dt>
				<dd>
					(Required) The respective post id. Use the <code>get_the_ID()</code> WordPress function within a loop.
				</dd>
				<dt>$type</dt>
				<dd>
					(Optional) Set the view counter type by using one of the following strings:<br />
					<b style="margin-left:20px;">"total"</b> returns all registered clicks. (Default)<br />
					<b style="margin-left:20px;">"daily"</b> returns all registered clicks for the current day.<br />
					<b style="margin-left:20px;">"unique"</b> returns all unique clicks (one click / user).<br />
					<b style="margin-left:20px;">"daily_unique"</b> returns all unique clicks (one click / user) for the current day.
				</dd>
			</dl>
			
			<h4>Return Values</h4>
			<p>
				Returns the view counter number as <b>integer</b> or <b>false</b> if the $post_id 
				parameter in invalid.
			</p>
			
			<h3 id="doc-has_viewed">dexs_cr_has_viewed ($post_id)</h3>
			<p>
				Checks if the current user has already clicked on the respective post.
			</p>
			
			<h4>Parameters</h4>
			<dl>
				<dt>$post_id</dt>
				<dd>
					(Required) The respective post id. Use the <code>get_the_ID()</code> WordPress function within a loop.
				</dd>
			</dl>
			
			<h4>Return Values</h4>
			<p>
				Returns <b>true</b> if the user has already clicked on the post, and <b>false</b> if 
				not or if the $post_id parameter is invalid!
			</p>
			
			<h3 id="doc-get_rating">dexs_cr_get_rating ($post_id)</h3>
			<p>
				Get the rating data for the respective post.
			</p>
			
			<h4>Parameters</h4>
			<dl>
				<dt>$post_id</dt>
				<dd>
					(Required) The respective post id. Use the <code>get_the_ID()</code> WordPress function within a loop.
				</dd>
			</dl>
			
			<h4>Return Values</h4>
			<p>
				Returns an <b>array</b> with the rating data or <b>false</b> on failure. Note: The rating 
				data (or rather the array keys) depends on your rating layout setting (see below), but the 
				values are always integers an represents the number of ratings / clicks on the respective 
				rating value.
			</p>
			<p>
				<b>On "Single":</b>
				<code>array("like" => [int])</code><br />
				
				<b>On "Double":</b>
				<code>array("like" => [int], "dislike" => [int])</code><br />
				
				<b>On "3-Level":</b>
				<code>array("1" => [int], "2" => [int], "3" => [int])</code><br />
				
				<b>On "5-Level":</b>
				<code>array("1" => [int], "2" => [int], "3" => [int], "4" => [int], "5" => [int])</code>
			</p>
			
			<h3 id="doc-has_rated">dexs_cr_has_rated ($post_id, $value)</h3>
			<p>
				Checks if the current user has already rated yet and returns perhaps his rating value.
			</p>
			
			<h4>Parameters</h4>
			<dl>
				<dt>$post_id</dt>
				<dd>
					(Required) The respective post id. Use the <code>get_the_ID()</code> WordPress function within a loop.
				</dd>
				<dt>$value</dt>
				<dd>
					(Optional) Change the returning value:<br />
					<b style="margin-left:20px;">false</b> checks only if the user has already rated. (Default)<br />
					<b style="margin-left:20px;">true</b> checks if the user has already rated and returns the rate value.
				</dd>
			</dl>
			
			<h4>Return Values</h4>
			<p>
				($value == false):<br />
				Returns <b>true</b> if the user has already rated yet or <b>false</b> if not or if the 
				$post_id is invalid. Note: Returns <b>NULL</b> if the current user is within an 
				excluded role!
			</p>
			<p>
				($value == true):<br />
				Returns the respective rating value as <b>string</b> or an empty string if the current user 
				hasn't rated yet. Note: Returns <b>false</b> if the $post_id is invalid and <b>NULL</b> if 
				the current user is within an excluded role!
			</p>
			
			<h3 id="doc-rating_link">dexs_cr_rating_link ($post_id, $value)</h3>
			<p>
				Creates one or more rating links with an WordPress nonce. Example: 
				http://www.example.com/post_permalink/?dexs-nonce=_nonce_&dexs=rating_value
			</p>
			
			<h4>Parameters</h4>
			<dl>
				<dt>$post_id</dt>
				<dd>
					(Required) The respective post id. Use the <code>get_the_ID()</code> WordPress function within a loop.
				</dd>
				<dt>$value</dt>
				<dd>
					(Optional) Configure the returning value:<br />
					<b style="margin-left:20px;">NULL</b> all rating values, depending on the rating layout. (Default)<br />
					<b style="margin-left:20px;"><i>value</i></b> the respective rating value.
				</dd>
			</dl>
			
			<h4>Return Values</h4>
			<p>
				($value == NULL):<br />
				Returns all rating links as <b>array</b> or <b>false</b> on failure. Note: The array keys are 
				the respective rating values, depending on the rating layout setting!
			</p>
			<p>
				($value == <i>value</i>):<br />
				Returns the respective rating link as <b>string</b> or <b>false</b> on failure. Note: Returns 
				also <b>false</b> if the given value doesn't match to the rating layout setting!
			</p>
			
			<h2 id="doc-wp_query">WP_Query Filter</h2>
			<p>
				You can use the following methods to change your custom WP_Query requests depending 
				on the viewer or rating counter. Please Note: This requires some skills in PHP and 
				WordPress, if you don't have them: Use our frontend widgets or learn it.
			</p>
			
			<h3 id="doc-viewercounter">Viewer Counter</h3>
			<p>
				You can use the following settings to change the post order, of your costum WP_Query 
				requests, depending to there total, daily, unique or daily_unique counter. Note: You may 
				return an empty post list, if you use the daily or daily_unique counter and if nobody 
				has viewed a single post item on the day yet. Note also: The Dexs.Counter plugin 
				doesn't automatically add the counter meta data to all of your posts. So each post that 
				havn't been clicked yet are therefore not considered!
			</p>
<pre>
<span class="pre-php">&lt;?php</span>
	<span class="pre-comment">// The Most-Viewed Posts (Total)</span>
	<span class="pre-var">$query</span> = array(
		<span class="pre-string">"order"</span>		=> <span class="pre-string">"DESC"</span>,
		<span class="pre-string">"orderby"</span>	=> <span class="pre-string">"meta_value_num"</span>,
		<span class="pre-string">"meta_key"</span>	=> <span class="pre-string">"dexs_views_total"</span>
	);
	<span class="pre-var">$posts</span> = new WP_Query(<span class="pre-var">$query</span>);
	
	<span class="pre-comment">// The Most-Viewed Posts (Unique)</span>
	<span class="pre-var">$query</span> = array(
		<span class="pre-string">"order"</span>		=> <span class="pre-string">"DESC"</span>,
		<span class="pre-string">"orderby"</span>	=> <span class="pre-string">"meta_value_num"</span>,
		<span class="pre-string">"meta_key"</span>	=> <span class="pre-string">"dexs_views_unique"</span>
	);
	<span class="pre-var">$posts</span> = new WP_Query(<span class="pre-var">$query</span>);
	
	<span class="pre-comment">// The Most-Viewed Posts (Total / Today)</span>
	<span class="pre-var">$query</span> = array(
		<span class="pre-string">"order"</span>		=> <span class="pre-string">"DESC"</span>,
		<span class="pre-string">"orderby"</span>	=> <span class="pre-string">"meta_value_num"</span>,
		<span class="pre-string">"meta_key"</span>	=> <span class="pre-string">"dexs_views_today"</span>
	);
	<span class="pre-var">$posts</span> = new WP_Query(<span class="pre-var">$query</span>);
	
	<span class="pre-comment">// The Most-Viewed Posts (Unique / Today)</span>
	<span class="pre-var">$query</span> = array(
		<span class="pre-string">"order"</span>		=> <span class="pre-string">"DESC"</span>,
		<span class="pre-string">"orderby"</span>	=> <span class="pre-string">"meta_value_num"</span>,
		<span class="pre-string">"meta_key"</span>	=> <span class="pre-string">"dexs_views_today_unique"</span>
	);
	<span class="pre-var">$posts</span> = new WP_Query(<span class="pre-var">$query</span>);
<span class="pre-php">?&gt;</span>
</pre>

			<h3 id="doc-ratingcounter">Rating Counter</h3>
			<p>
				You can use the following settings to change the post order, of your costum WP_Query 
				requests, depending to the best-, worst-, most- or fewest ratings. Note: The best-rated 
				posts doesn't need to be also the most-rated posts. Note also: The Dexs.Counter plugin 
				doesn't automatically add the rating meta data to all of your posts. So each post that 
				havn't been rated yet are therefore not considered!
			</p>
<pre>
<span class="pre-php">&lt;?php</span>
	<span class="pre-comment">// The Best-Rated Posts</span>
	<span class="pre-var">$query</span> = array(
		<span class="pre-string">"order"</span>		=> <span class="pre-string">"DESC"</span>,
		<span class="pre-string">"orderby"</span>	=> <span class="pre-string">"meta_value_num"</span>,
		<span class="pre-string">"meta_key"</span>	=> <span class="pre-string">"dexs_ratings"</span>
	);
	<span class="pre-var">$posts</span> = new WP_Query(<span class="pre-var">$query</span>);
	
	<span class="pre-comment">// The Worst-Rated Posts</span>
	<span class="pre-var">$query</span> = array(
		<span class="pre-string">"order"</span>		=> <span class="pre-string">"ASC"</span>,
		<span class="pre-string">"orderby"</span>	=> <span class="pre-string">"meta_value_num"</span>,
		<span class="pre-string">"meta_key"</span>	=> <span class="pre-string">"dexs_ratings"</span>
	);
	<span class="pre-var">$posts</span> = new WP_Query(<span class="pre-var">$query</span>);
	
	<span class="pre-comment">// The Most-Rated Posts</span>
	<span class="pre-var">$query</span> = array(
		<span class="pre-string">"order"</span>		=> <span class="pre-string">"DESC"</span>,
		<span class="pre-string">"orderby"</span>	=> <span class="pre-string">"meta_value_num"</span>,
		<span class="pre-string">"meta_key"</span>	=> <span class="pre-string">"dexs_ratings_num"</span>
	);
	<span class="pre-var">$posts</span> = new WP_Query(<span class="pre-var">$query</span>);
	
	<span class="pre-comment">// The Fewest-Rated Posts</span>
	<span class="pre-var">$query</span> = array(
		<span class="pre-string">"order"</span>		=> <span class="pre-string">"ASC"</span>,
		<span class="pre-string">"orderby"</span>	=> <span class="pre-string">"meta_value_num"</span>,
		<span class="pre-string">"meta_key"</span>	=> <span class="pre-string">"dexs_ratings_num"</span>
	);
	<span class="pre-var">$posts</span> = new WP_Query(<span class="pre-var">$query</span>);
<span class="pre-php">?&gt;</span>
</pre>
		</div>
		<div id="dexs-documentation" class="dexs-sidebar">
			<h3>Table of Contents</h3>
			<ul class="dexs-toc">
				<li>
					<a href="#doc-functions">Function Reference</a>
					<ul>
						<li><a href="#doc-get_views">dexs_cr_get_views()</a></li>
						<li><a href="#doc-has_viewed">dexs_cr_has_viewed()</a></li>
						<li><a href="#doc-get_rating">dexs_cr_get_rating()</a></li>
						<li><a href="#doc-has_rated">dexs_cr_has_rated()</a></li>
						<li><a href="#doc-rating_link">dexs_cr_rating_link()</a></li>
					</ul>
				</li>
				<li>
					<a href="#doc-wp_query">WP_Query Filters</a>
					<ul>
						<li><a href="#doc-viewercounter">Viewer Counter</a></li>
						<li><a href="#doc-ratingcounter">Rating Counter</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</div>