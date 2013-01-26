jQuery(document).ready(function() {
	var pid = jQuery('meta[name=post_id]').attr("value");
	var purl = jQuery('meta[name=plugin_url]').attr("value");
	jQuery.post(purl, { post_id: pid } );
});
