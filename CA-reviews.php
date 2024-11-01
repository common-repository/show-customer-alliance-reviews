<?php
/*
Plugin Name: CA reviews viewer
Plugin URI: 
Description: Enables the shortcode [CA-reviews] to show some reviews from Customer Alliance database
Version: 1.0
Author: info@jamweb.biz
Author URI: http://www.jamweb.biz
License: GPL
*/

add_shortcode("CA-reviews", "jw_CA_reviews");
wp_register_style("jw_CA-reviews",plugins_url('style.css',__FILE__ ));
wp_enqueue_style("jw_CA-reviews");

function jw_CA_register_settings() {
	register_setting("jw_CA_settings", "jw_option_customer_id");
	register_setting("jw_CA_settings", "jw_option_customer_access_key");
	register_setting("jw_CA_settings", "jw_option_print_number");
}

function jw_CA_options_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	ob_start();
	?>
	<div class="wrap">
		<h2>Customer Alliance reviews viewer</h2>
		<h4>by <a href="http://www.jamweb.biz" target="_new">jamweb.biz</a> - a web agency based in Venice</h4>
		<div>
			<p>This plugin make available the <code>[CA-reviews]</code> shortcode which shows reviews from the Customer Alliance review system.<br />
			Reviews are ordered by date.</p>
		</div>
		<form method="post" action="options.php">
		<?php
			settings_fields("jw_CA_settings");
			do_settings_sections("jw_CA_settings");
		?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Customer id</th>
				<td><input type="text" name="jw_option_customer_id" value="<?php echo esc_attr(get_option("jw_option_customer_id")); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row">Customer access key</th>
				<td><input type="text" name="jw_option_customer_access_key" value="<?php echo esc_attr(get_option("jw_option_customer_access_key")); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row">Show review numeric score</th>
				<td><input type="checkbox" name="jw_option_print_number" <?php if (get_option("jw_option_print_number") == "on") echo "CHECKED"; ?> /></td>
			</tr>
		</table>
		<?php submit_button(); ?>
		</form>
		<h3>available attributes:</h3>
		<div>
			<p>
				<code>limit=&lt;num&gt;	[default: 100]</code><br />
				limits the number of reviews displayed.
			</p>

			<p>
				<code>order=ASC|DESC	[default: DESC]</code><br />
				set the order, ascending or descending.
			</p>

			<p>
				<code>offset=&lt;num&gt;	[default: 0]</code><br />
				starts showing results from the &lt;num&gt;th
			</p>

			<p>
				<code>monthsago=&lt;num&gt;	[default: 0]</code><br />
				results start from &lt;num&gt; months ago to now
			</p>

			<p>
				<code>start=&lt;yyyy-mm-dd&gt;</code><br />
				results set start from &lt;yyyy-mm-dd&gt;
			</p>

			<p>
				<code>end=&lt;yyyy-mm-dd&gt;</code><br />
				results set ends on &lt;yyyy-mm-dd&gt;
			</p>

			<p>
				<code>anon=no</code><br />
				discard anonymous reviews
			</p>

			<p>
				<code>lang=&lt;langcode&gt;</code><br />
				show only reviews written in &lt;langcode&gt; (use international code: it, en, fr, ...)
			</p>

			<p>
				<code>minrating=&lt;num&gt;</code><br />
				show only reviews with a rating higher than &lt;num&gt;
			</p>

			<p>
				<h4>EXAMPLE</h4>
				<code>[CA-reviews anon=no limit=5]</code><br />
				shows latest 5 reviews with a defined author
			</p>
		</div>
	</div>
	<?php
	ob_flush();
	
}

function jw_CA_menu() {
	add_options_page("Customer Alliance reviews by jamweb.biz", "CA-reviews", "manage_options", "jw-ca-reviews-options", "jw_CA_options_page");
}
add_action("admin_menu","jw_CA_menu");
add_action("admin_init","jw_CA_register_settings");

function jw_CA_reviews( $attrs ) {
	
	
	$jw_CA_url					= "https://api.customer-alliance.com/reviews/list";
	$jw_CA_version				= "4";
	$jw_CA_customer_id 			= get_option("jw_option_customer_id");
	$jw_CA_customer_access_key 	= get_option("jw_option_customer_access_key");
	$jw_option_print_number 	= (get_option("jw_option_print_number") == "on")?true:false;

	if (isset($attrs['limit'])) {
		$jw_CA_limit			= $attrs['limit'];
		$jw_CA_display_limit	= $attrs['limit'];		// we keep the limit even as a display information for reasons explained below
	} else {
		$jw_CA_limit			= "0";
		$jw_CA_display_limit	= 0;
	}
	if (isset($attrs['order'])) {
		$jw_CA_order	= $attrs['order'];
	} else {
		$jw_CA_order	= "N";
	}
	if (isset($attrs['offset'])) {
		$jw_CA_offset	= $attrs['offset'];
		$jw_CA_display_offset	= $attrs['offset'];
	} else {
		$jw_CA_offset	= "0";
		$jw_CA_display_offset	= 0;
	}
	if (isset($attrs['monthsago'])) {
		$jw_CA_monthsAgo	= $attrs['monthsAgo'];
	} else {
		$jw_CA_monthsAgo	= "0";
	}
	if (isset($attrs['start'])) {
		$jw_CA_startDate	= $attrs['start'];
	} else {
		$jw_CA_startDate	= "0000-00-00";
	}
	if (isset($attrs['end'])) {
		$jw_CA_endDate	= $attrs['end'];
	} else {
		$jw_CA_endDate	= "0000-00-00";
	}
	
	/*
	 * 	in case switches that cannot be encoded directly in the query to the API
	 * 	we remove the (possible) limit to the query so we get as much results as possible
	 *	and then filter and limit the results on our own.
	 */
	 
	if (isset($attrs['anon']) && ($attrs['anon'] == "no")) {
		$noAnon	= true;
		$jw_CA_limit			= "0";
		$jw_CA_offset			= "0";
	} else {
		$noAnon	= false;
	}
	if (isset($attrs['lang'])) {
		$lang	= $attrs['lang'];
		$jw_CA_limit			= "0";
		$jw_CA_offset			= "0";
	} else {
		$lang	= "all";
	}
	if (isset($attrs['minrating'])) {
		$minRating	= $attrs['minrating'];
		$jw_CA_limit			= "0";
		$jw_CA_offset			= "0";
	} else {
		$minRating	= 0;
	}
	
	$query_url	= $jw_CA_url;
	$query_url	.= "?version=" . $jw_CA_version;
	$query_url	.= "&id=" . $jw_CA_customer_id;
	$query_url	.= "&access_key=" . $jw_CA_customer_access_key;
	if ($jw_CA_limit != 0) $query_url	.= "&limit=" . $jw_CA_limit;
	if ($jw_CA_order != "N") $query_url	.= "&order=" . $jw_CA_order;
	if ($jw_CA_offset != 0) $query_url	.= "&offset=" . $jw_CA_offset;
	if ($jw_CA_monthsAgo != 0) $query_url	.= "&monthsAgo=" . $jw_CA_monthsAgo;
	if ($jw_CA_startDate != "0000-00-00") $query_url	.= "&start=" . $jw_CA_startDate;
	if ($jw_CA_endDate != "0000-00-00") $query_url	.= "&end=" . $jw_CA_endDate;
	
	$reviewsXML	= simplexml_load_file($query_url);
	
	ob_start();
	?>
	<div class="CA-reviews">
		<ul>
		<?php
			$displayed_reviews	= 0;
			$counted_reviews	= 0;
			foreach ($reviewsXML->reviews->review as $review) {
				/*
				 * the if clause here states that if:
				 * no display limit OR we are within the display range AND
				 * anonymous reviews are allowed OR the author is not empty AND
				 * all languages are allowed OR the language code is the one wanted AND
				 * all ratings are allowed OR the rating is equal or above the set minimum
				 * then the review is shown (and the display counter is increased)
				 */
				
				if ((($jw_CA_display_limit == 0) || ($displayed_reviews < $jw_CA_display_limit)) &&
					($noAnon == false || $review->author != "") &&
					($minRating == 0 || $review->overallRating >= $minRating) &&
					($lang == "all" || $review->language == $lang)) {
						/*
						 * this sub-if handle "local" offsets if needed due to API limitations
						 */
						if ($jw_CA_display_offset == 0 || ($counted_reviews > $jw_CA_display_offset)) {
							?>
							<li class="CA-review">
										<div class="CA_bubble">
										<div class="CA-rating" title="overall rating: <?php echo $review->overallRating; ?>">
																				
										<?php
											for ($i=0;$i<round($review->overallRating); $i++) {
												?><img src="<?php echo plugins_url('star.png',__FILE__ ); ?>" /><?php
											}
										
											if ($jw_option_print_number) { ?>(<?php echo $review->overallRating; ?>)<?php }
										?>
									    <br />
										<div class="CA-author author">
										<i class="icon-user"></i>
										<span><?php echo ($review->author != "")?$review->author:"Anonymous"; ?></span>
										
								     </div>
								<div class="col">
									<?php echo $review->overallComment; ?>
								</div>	
								<div class="col1">
								<br /><br /><span style="font-size:12px;color:grey;">Date: <?php echo $review->date; ?></span>
								</div>	
									
								     


								     </div>
								</div>
							</li>
							<?php
							$displayed_reviews++;
						}
						$counted_reviews++;
				}
			}
		?>
		</ul>
	</div>
	<?php
	return ob_get_clean();
}
?>