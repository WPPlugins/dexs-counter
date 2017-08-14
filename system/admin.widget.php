<?php
/* 
 |	DEXS.COUNTER
 |	@file		./system/admin.widget.php
 |	@author		SamBrishes@pytesNET
 |	@version	0.1.3 [0.1.0] - Alpha
 |
 |	@license	X11 / MIT License
 |	@copyright	Copyright © 2015 - 2016 pytesNET
 */
	if(!defined("DEXS_CR")){ die("Go directly to jail; do not pass go, do not collect 200 cookies."); }
?>
<div class="dexs-counter-part">
	<h3><?php _e("Statistics", DEXS_CR); ?></h3>
	<?php if(!isset($data)){ ?>
		<div class="dexs-counter-stats-chart-holder"><?php _e("I'm sorry, but there is no data to display yet!", DEXS_CR); ?></div>
	<?php } else { ?>
		<canvas id="dexs-counter-stats-chart" height="150px" data-stats='<?php echo json_encode($data); ?>'></canvas>
	<?php } ?>
</div>
<?php if(!empty($this->config["view-types"])){ ?>
	<div class="dexs-counter-part">
		<h3><?php _e("Most-Viewed Posts", DEXS_CR); ?></h3>
		<?php
			$query = new WP_Query(array(
				"post_type"				=> $this->config["view-types"],
				"post_status"			=> "publish",
				"posts_per_page"		=> 5,
				"has_password"			=> false,
				"ignore_sticky_posts"	=> true,
				"order"					=> "DESC",
				"orderby"				=> "meta_value_num",
				"meta_key"				=> "dexs_views_total"
			));
			
			if($query->have_posts()){
				?><ul><?php
				while($query->have_posts()){ $query->the_post();
					?>
						<li>
							<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="item-link">
								<span class="item-title"><?php the_title(); ?></span>
								<span class="item-date"><?php the_time(get_option("date_format")); ?></span>
								<span class="item-views">(
									<?php _e("Total", DEXS_CR); ?>: <?php echo dexs_cr_get_views(get_the_ID(), "total"); ?> | 
									<?php _e("Unique", DEXS_CR); ?>: <?php echo dexs_cr_get_views(get_the_ID(), "unique"); ?>
								)</span>
							</a>
						</li>
					<?php
				}
				?></ul><?php
			} else {
				?>
					<ul>
						<li class="item-empty"><?php _e("I'm sorry, but there is no data to display yet!", DEXS_CR); ?></li>
					</ul>
				<?php
			}
			wp_reset_postdata();
		?>
	</div>
<?php } ?>
<?php if(!empty($this->config["rating-types"])){ ?>
	<div class="dexs-counter-part">
		<h3><?php _e("Best-Rated Posts", DEXS_CR); ?></h3>
		<?php
			$query = new WP_Query(array(
				"post_type"				=> $this->config["rating-types"],
				"post_status"			=> "publish",
				"posts_per_page"		=> 5,
				"has_password"			=> false,
				"ignore_sticky_posts"	=> true,
				"order"					=> "DESC",
				"orderby"				=> "meta_value_num",
				"meta_key"				=> "dexs_ratings"
			));
			
			if($query->have_posts()){
				?><ul><?php
				while($query->have_posts()){ $query->the_post();
					?>
						<li>
							<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="item-link">
								<span class="item-title"><?php the_title(); ?></span>
								<span class="item-date"><?php the_time(get_option("date_format")); ?></span>
								<span class="item-ratings">(
									<?php _e("Ratings", DEXS_CR); ?>: <?php echo get_post_meta(get_the_ID(), "dexs_ratings_num", true); ?>
								)</span>
							</a>
						</li>
					<?php
				}
				?></ul><?php
			} else {
				?>
					<ul>
						<li class="item-empty"><?php _e("I'm sorry, but there is no data to display yet!", DEXS_CR); ?></li>
					</ul>
				<?php
			}
			wp_reset_postdata();
		?>
	</div>
<?php } ?>