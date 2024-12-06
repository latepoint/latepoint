<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

/* @var $topic string */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-lightbox-heading">
	<h2><?php esc_html_e(OsSupportTopicsHelper::get_title_for_topic($topic)); ?></h2>
</div>
<div class="latepoint-lightbox-content">
	<?php include('partials/'.sanitize_file_name($topic.'.php')); ?>
</div>