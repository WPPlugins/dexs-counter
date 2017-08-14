<?php
/* 
 |	DEXS.COUNTER
 |	@file		./system/func.frontend.php
 |	@author		SamBrishes@pytesNET
 |	@version	0.1.3 [0.1.0] - Alpha
 |
 |	@license	X11 / MIT License
 |	@copyright	Copyright © 2015 - 2016 pytesNET
 */
	if(!defined("DEXS_CR")){ die("Go directly to jail; do not pass go, do not collect 200 cookies."); }
	
	/*
	 |	GET VIEW COUNTER
	 |	@since	0.1.0
	 |
	 |	@param	int		The post ID as int (or string).
	 |	@param	string	The counter type "daily", "daily_unique", "unique" or "total".
	 |
	 |	@return	multi	The counter number as INT, FALSE on failure.
	 */
	function dexs_cr_get_views($post_id, $type = "total"){
		global $dexsCounter;
		return $dexsCounter->system->getView($post_id, $type);
	}
	
	/*
	 |	HAS USER ALREADY VIEWED
	 |	@since	0.1.0
	 |
	 |	@param	int		The post ID as int (or string).
	 |
	 |	@return	bool	TRUE if the user has already viewed, FALSE if not (or on failure).
	 */
	function dexs_cr_has_viewed($post_id){
		global $dexsCounter;
		return $dexsCounter->system->hasViewed($post_id);
	}
	
	/*
	 |	GET RATING COUNTER
	 |	@since	0.1.0
	 |
	 |	@param	int		The post ID as int (or string).
	 |
	 |	@return	array	The rating data as ARRAY, FALSE on failure.
	 */
	function dexs_cr_get_rating($post_id){
		global $dexsCounter;
		return $dexsCounter->system->getRating($post_id);
	}
	
	/*
	 |	HAS USER ALREADY RATED
	 |	@since	0.1.0
	 |
	 |	@param	int		The post ID as int (or string).
	 |	@param	bool	TRUE to get the user rating, FALSE to check only if he has already rated
	 |
	 |	@return	multi	TRUE if the user has rated, FALSE if not (or on failure).
	 |					The rating value as STRING, an empty STRING if the user hasn't rated yet,
	 |					NULL if the user is within an excluded role, FALSE on failure.
	 */
	function dexs_cr_has_rated($post_id, $value = false){
		global $dexsCounter;
		return $dexsCounter->system->hasRated($post_id, $value);
	}
	
	/*
	 |	GET RATING LINK
	 |	@since	0.1.0
	 |
	 |	@param	int		The post ID as int (or string).
	 |	@param	multi	The specific rating value as STRING, or NULL for each rating value.
	 |
	 |	@return	multi	A valid nonce rating link as STRING, or an ARRAY with all rating links.
	 |					or FALSE on failure.
	 */
	function dexs_cr_rating_link($post_id, $value = NULL){
		global $dexsCounter;
		
		$values = array(
			"single"	=> array("like"),
			"double"	=> array("like", "dislike"),
			"3-level"	=> array("1", "2", "3"),
			"5-level"	=> array("1", "2", "3", "4", "5")
		);
		$config = $dexsCounter->_config("rating-layout");
		if(!array_key_exists($config, $values)){
			return false;
		}
		
		if($value === NULL){
			$nonces = $values[$config];
		} else if(is_string($value)){
			if(!in_array($value, $values[$config])){
				return false;
			}
			$nonces = array($value);
		} else {
			return false;
		}
		
		$return = array();
		foreach($nonces AS $nonce){
			$link = get_the_permalink($post_id);
			if(strpos($link, "?") === false){
				$link .= "?dexs-nonce=".wp_create_nonce($nonce);
			} else {
				$link .= "&dexs-nonce=".wp_create_nonce($nonce);
			}
			$return[$nonce] = $link."&dexs=".$nonce;
		}
		
		if($value === NULL || count($nonces) > 1){
			return $return;
		}
		return $return[$nonces[0]];
	}
