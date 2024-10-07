<?php
/* @var $processes OsProcessModel[] */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}



?>

<div class="os-processes-w os-form-blocks-w">
	<?php
		if($processes){
			foreach($processes as $process){
				$process->build_from_json();
				include('_form.php');
			}
		}
		wp_enqueue_editor();
	?>
</div>
<div class="os-add-box"
     data-os-after-call="latepoint_init_process_conditions_form"
     data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('processes', 'new_form')); ?>"
     data-os-output-target-do="append"
     data-os-output-target=".os-processes-w">
	<div class="add-box-graphic-w">
		<div class="add-box-plus"><i class="latepoint-icon latepoint-icon-plus4"></i></div>
	</div>
	<div class="add-box-label"><?php esc_html_e('Add Workflow', 'latepoint'); ?></div>
</div>