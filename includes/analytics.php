<?php

namespace PFMC\Analytics;

add_action( 'wp_head', __NAMESPACE__ . '\add_ga_tracker' );

/**
 * Add a Google Analytics tracker to front-end page views.
 */
function add_ga_tracker() {

	// Don't track authenticated users.
	if ( is_user_logged_in() ) {
		return;
	}

	?>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-25661802-1"></script>
	<script>
  		window.dataLayer = window.dataLayer || [];
  		function gtag(){dataLayer.push(arguments);}
  		gtag('js', new Date());
  		gtag('config', 'UA-69021927-1');
	</script>
	<?php
}
