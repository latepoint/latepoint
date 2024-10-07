<?php
/* @var $activities OsActivityModel[] */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<?php
if ($activities) {
	foreach ($activities as $activity) { ?>
		<tr class="activity-type-<?php echo esc_attr($activity->code); ?>">
			<td class="activity-column-name"><div><?php echo esc_html($activity->name); ?></div></td>
			<td><?php echo $activity->user_link_with_avatar; ?></td>
			<td><?php echo esc_html($activity->nice_created_at); ?></td>
			<td><?php echo $activity->link_to_object; ?></td>
		</tr>
		<?php
	}
} ?>