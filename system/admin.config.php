<?php
/* 
 |	DEXS.COUNTER
 |	@file		./system/admin.config.php
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
			<li class="dexs-item active"><a href="<?php echo admin_url("options-general.php?page=dexs-counter"); ?>"><?php _e("Settings"); ?></a></li>
			<li class="dexs-item"><a href="<?php echo admin_url("options-general.php?page=dexs-counter&dexs=docs"); ?>"><?php _e("Documentation"); ?></a></li>
		</ul>
		<div class="dexs-love"><span class="dashicons dashicons-heart"></span></div>
	</div>
	
	<div class="dexs-content">
		<div id="dexs-settings" class="dexs-page">
			<?php if(isset($_GET["status"]) && $_GET["status"] === "success"){ ?>
				<div class="dexs-status dexs-status-success">
					<?php if(isset($_GET["action"]) && $_GET["action"] === "reset"){ ?>
						<p><?php _e("Settings have been successfully reseted!", DEXS_CR); ?></p>
					<?php } else { ?>
						<p><?php _e("Settings have been successfully saved!", DEXS_CR); ?></p>
					<?php } ?>
				</div>
			<?php } ?>
			<?php if(isset($_GET["status"]) && $_GET["status"] === "error"){ ?>
				<div class="dexs-status dexs-status-error">
					<?php if(isset($_GET["action"]) && $_GET["action"] === "reset"){ ?>
						<p><?php _e("Settings couldn't be reseted!", DEXS_CR); ?></p>
					<?php } else { ?>
						<p><?php _e("Settings couldn't be saved!", DEXS_CR); ?></p>
					<?php } ?>
				</div>
			<?php } ?>
			
			<form method="post" action="" class="dexs-settings">
				<?php wp_nonce_field("dexs-counter-nonce", "dexs-counter-nonce-field"); ?>
				
				<div class="dexs-setting">
					<div class="dexs-setting-header">
						<div class="dexs-setting-reset">
							<button name="dexs-counter-action" value="reset" 
								class="button button-secondary"><?php _e("Reset Settings", DEXS_CR); ?></button>
						</div>
						<div class="dexs-setting-submit">
							<button name="dexs-counter-action" value="update" 
								class="button button-primary"><?php _e("Save Settings", DEXS_CR); ?></button>
						</div>
					</div>
					<div class="dexs-setting-tip">
						<?php _e("Don't forget to save the settings!", DEXS_CR); ?>
					</div>
				</div>
				
				<div class="dexs-setting">
					<div class="dexs-setting-header">
						<div class="dexs-setting-title">
							<label for="enable-statistics"><?php _e("Extended Statistics", DEXS_CR); ?></label>
						</div>
						<div class="dexs-setting-main">
							<input type="checkbox" id="enable-statistics" name="enable-statistics" value="1" 
								class="dexs-switch" <?php checked($config["enable-statistics"]); ?> />
							<label for="enable-statistics"></label>
						</div>
					</div>
					<div class="dexs-setting-description">
						<?php _e("Enable this option to store each day viewer and rating counter and display this data as a diagram inside the Dexs dashboard widget.", DEXS_CR); ?>
					</div>
				</div>
				
				<div class="dexs-setting">
					<div class="dexs-setting-header">
						<div class="dexs-setting-title">
							<label for="enable-widgets"><?php _e("Sidebar Widgets", DEXS_CR); ?></label>
						</div>
						<div class="dexs-setting-main">
							<input type="checkbox" id="enable-widgets" name="enable-widgets" value="1" 
								class="dexs-switch" <?php checked($config["enable-widgets"]); ?> />
							<label for="enable-widgets"></label>
						</div>
					</div>
					<div class="dexs-setting-description">
						<?php _e("Enable this option to use the Dexs Sidebar Widgets on your Frontend Theme.", DEXS_CR); ?>
					</div>
				</div>
				
				<div class="dexs-setting">
					<div class="dexs-setting-header">
						<div class="dexs-setting-title">
							<?php _e("Viewer Counter", DEXS_CR); ?>
						</div>
						<div class="dexs-setting-main"></div>
					</div>
					<div class="dexs-setting-description">
						<?php _e("The Viewer Counter saves every view on each enabled post type.", DEXS_CR); ?>
					</div>
					<div class="dexs-setting-content">
						<table>
							<tr>
								<th><?php _e("Post Types", DEXS_CR); ?></th>
								<td>
									<?php foreach($types AS $type){ ?>
										<?php $checked = (in_array($type->name, $config["view-types"])? 'checked="checked"': ""); ?>
										<label>
											<input type="checkbox" name="view-types[]" value="<?php echo $type->name; ?>" <?php echo $checked; ?>> 
											<?php echo $type->label; ?>
										</label>
									<?php } ?>
								</td>
							</tr>
							
							<tr>
								<th><?php _e("Exclude Roles", DEXS_CR); ?></th>
								<td>
									<?php foreach($roles AS $value => $name){ if($value === "subscriber"){ continue; } ?>
										<?php $checked = (in_array($value, $config["view-excludes"])? 'checked="checked"': ""); ?>
										<label>
											<input type="checkbox" name="view-excludes[]" value="<?php echo $value; ?>" <?php echo $checked; ?>> 
											<?php echo translate_user_role($name); ?>
										</label>
									<?php } ?>
								</td>
							</tr>
						</table>
					</div>
				</div>
				
				<div class="dexs-setting">
					<div class="dexs-setting-header">
						<div class="dexs-setting-title">
							<label for="enable-widgets"><?php _e("Rating System", DEXS_CR); ?></label>
						</div>
						<div class="dexs-setting-main"></div>
					</div>
					<div class="dexs-setting-description">
						<?php _e("The Rating System allows your reader to rate your posts.", DEXS_CR); ?>
					</div>
					<div class="dexs-setting-content">
						<table>
							<tr>
								<th><?php _e("Post Types", DEXS_CR); ?></th>
								<td>
									<?php foreach($types AS $type){ ?>
										<?php $checked = (in_array($type->name, $config["rating-types"])? 'checked="checked"': ""); ?>
										<label>
											<input type="checkbox" name="rating-types[]" value="<?php echo $type->name; ?>" <?php echo $checked; ?>> 
											<?php echo $type->label; ?>
										</label>
									<?php } ?>
								</td>
							</tr>
							
							<tr>
								<th><?php _e("Exclude Roles", DEXS_CR); ?></th>
								<td>
									<?php foreach($roles AS $value => $name){ if($value === "subscriber"){ continue; } ?>
										<?php $checked = (in_array($value, $config["rating-excludes"])? 'checked="checked"': ""); ?>
										<label>
											<input type="checkbox" name="rating-excludes[]" value="<?php echo $value; ?>" <?php echo $checked; ?>> 
											<?php echo translate_user_role($name); ?>
										</label>
									<?php } ?>
								</td>
							</tr>
							
							<tr>
								<th>
									<label for="rating-layout"><?php _e("Rating Layout", DEXS_CR); ?></label>
								</th>
								<td>
									<select id="rating-layout" name="rating-layout">
										<option value="single" <?php selected($config["rating-layout"], "single"); ?>><?php _e("Like", DEXS_CR); ?></option>
										<option value="double" <?php selected($config["rating-layout"], "double"); ?>><?php _e("Like and Dislike", DEXS_CR); ?></option>
										<option value="3-level" <?php selected($config["rating-layout"], "3-level"); ?>><?php _e("3-Level Rating", DEXS_CR); ?></option>
										<option value="5-level" <?php selected($config["rating-layout"], "5-level"); ?>><?php _e("5-Level Rating", DEXS_CR); ?></option>
									</select>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>