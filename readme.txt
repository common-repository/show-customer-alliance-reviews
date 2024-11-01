=== CA-review ===
Contributors: jamwebvenice
Tags: reviews, hospitality, customer alliance, customer management
Requires at least: 4.4.1
Tested up to: 4.4.2
Stable tag: 1.1
License: GPLv2 or later

Use a shortcode to shows reviews from the Customer Alliance review system.

== Description ==

This plugin make available the [CA-reviews] shortcode which shows reviews from the Customer Alliance [ http://www.customer-alliance.com/ ] review system.
Reviews are ordered by date.

The plugin implements some filters that aren't directly supported by the Customer Alliance API (discard anonymous reviews, minimum rating, language).
Such folters are implemented internally and require a larger data exchange with CA servers, that could result in a bit more waiting time.

The shortcode can be used different times in the same page with different attributes.
The boxes are styled very simply so that can be easily changed to fit the site's theme.

This plugin is brought to you by those fine folks at jamweb [ http://www.jamweb.biz ]

available switches:

limit=<num>	[default: 100]
limits the number of reviews displayed.

order=ASC|DESC	[default: DESC]
set the order, ascending or descending.

offset=<num>	[default: 0]
starts showing results from the <num>th

monthsago=<num>	[default: 0]
results start from <num> months ago to now

start=<yyyy-mm-dd>
results set start from <yyyy-mm-dd>

end=<yyyy-mm-dd>
results set ends on <yyyy-mm-dd>

anon=no
discard anonymous reviews

lang=<langcode>
show only reviews written in <langcode> (use international code: it, en, fr, ...)

minrating=num
show only reviews with a rating higher than <num>

EXAMPLE
[CA-reviews anon=no limit=5]
shows latest 5 reviews with a defined author

== Installation ==

Upload the plugin ;) activate it and insert your Customer Alliance Id and Access key.
You can now use the shortcode to retrieve your structure reviews.

== Changelog ==

= 1.1 =
Added option page and the checkbox to show or hide the numeric score of the reviews.
