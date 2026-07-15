<?php
$wpsaf = wpsafelink_options();

if ( $wpsaf['verification_homepage'] == 'yes' ) {
	$permalink = get_bloginfo( 'url' );
} else {
	$args      = array(
		'post_type'      => 'post',
		'orderby'        => 'rand',
		'posts_per_page' => 1,
	);
	$permalink = "";
	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$permalink = get_permalink( $the_query->the_post() );
		}
	}
}

$humanverification = apply_filters( 'wp_safelink_humanverification', 1 );
?>
<html>

<head>
    <title>Landing..</title>
    <meta name="referrer" content="no-referrer">

    <META NAME="robots" CONTENT="noindex,nofollow">
</head>

<body>
<form id="landing" method="POST" action="<?php echo $permalink; ?>">
    <input type="hidden" name="humanverification" value="<?php echo $humanverification; ?>">
    <input type="hidden" name="newwpsafelink" value="<?php echo $_GET['linkr_encrypt'] ?>">
</form>
<script>
    window.onload = function () {
        document.getElementById('landing').submit();
    }
</script>
</body>

</html>
