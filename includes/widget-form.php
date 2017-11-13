<?php
namespace CDevelopers\Query\Terms;

if ( ! defined( 'ABSPATH' ) )
	exit; // disable direct access

$active = array();
foreach ($instance as $key => $val) {
	$active[ $this->get_field_name( $key ) ] = $val;
}

?>
<div class="widget-panel-widget-form">

	<legend class="screen-reader-text"><span><?php _e('General Settings') ?></span></legend>
	<?php
	$data = Utils::get_settings( 'global.php', array('widget' => $this, 'instance' => $instance) );
	$form = new WP_Admin_Forms( $data, false, array( 'admin_page'  => false ));
	$form->set_active( $active );
	$form->render();
	?>

	<!-- Thumbnail -->
	<div class="widget-panel-section">
		<?php echo Utils::build_section_header( $title = 'Term Thumbnail' ); ?>

		<fieldset data-fieldset-id="thumbnails" class="widget-panel-settings widget-panel-fieldset settings-thumbnails">

			<legend class="screen-reader-text"><span><?php _e('Term Thumbnail') ?></span></legend>

			<?php if( $thumb_compatible = true ) : ?>

				<?php
				$data = Utils::get_settings( 'thumbnail.php', array('widget' => $this, 'instance' => $instance) );
				$form = new WP_Admin_Forms( $data, false, array( 'admin_page'  => false ));
				$form->set_active( $active );
				$form->render();
				?>

			<?php else : ?>

				<?php
				$_install_url = add_query_arg( 's', 'Advanced+Term+Images', admin_url( 'plugin-install.php?tab=search' ) );
				$_intro = sprintf( 'The Advanced Categories Widget is compatible with the <b>%1$s</b> plugin to display featured images for category terms.  It appears the <b>%1$s</b> plugin is not installed on your site.  Please install this plugin to enable compatibility.',
					sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( $_install_url ),
						'Advanced Term Images'
					)
				); ?>

				<div class="description widget-panel-description">
					<?php echo wpautop( $_intro ); ?>
				</div>

			<?php endif; ?>

		</fieldset>
	</div><!-- /.widget-panel-section -->

	<!-- template -->
	<div class="widget-panel-section">
		<?php echo Utils::build_section_header( $title = 'Template' ); ?>

		<fieldset data-fieldset-id="layout" class="widget-panel-settings widget-panel-fieldset settings-layout">
			<legend class="screen-reader-text"><span><?php _e('Template') ?></span></legend>
			<?php
			$data = Utils::get_settings( 'template.php', array('widget' => $this, 'instance' => $instance) );
			$form = new WP_Admin_Forms( $data, false, array( 'admin_page'  => false ));
			$form->set_active( $active );
			$form->render();
			?>
		</fieldset>
	</div><!-- /.widget-panel-section -->

	<!-- Query Settings -->
	<div class="widget-panel-section">
		<?php echo Utils::build_section_header( $title = 'Query Settings' ); ?>

		<fieldset data-fieldset-id="thumbnails" class="widget-panel-settings widget-panel-fieldset settings-view-settings">
			<legend class="screen-reader-text"><span><?php _e('Query Settings') ?></span></legend>
			<?php
			$data = Utils::get_settings( 'query.php', array('widget' => $this, 'instance' => $instance) );
			$form = new WP_Admin_Forms( $data, false, array( 'admin_page'  => false ));
			$form->set_active( $active );
			$form->render();
			?>
		</fieldset>
	</div>

	<?php /* ?>
	<!-- Include -->
	<div class="widget-panel-section">
		<?php echo Utils::build_section_header( $title = 'Terms Included' ); ?>

		<fieldset data-fieldset-id="filters" class="widget-panel-settings widget-panel-fieldset settings-filters">

			<legend class="screen-reader-text"><span><?php _e('Terms Included') ?></span></legend>

			<div class="description widget-panel-description">
				<?php echo wpautop( __( 'Use the following fields to limit your list to certain categories.' ) ); ?>
			</div>

			<?php
			// $taxonomies = Utils::get_allowed_taxonomies();

			if( count( $taxes ) ) :
				foreach ( $taxes as $name => $label ) {
					Utils::build_term_select( $name, $label, $instance, $this );
				}
			endif;
			?>

		</fieldset>
	</div><!-- /.widget-panel-section -->

	<!-- Exclude -->
	<div class="widget-panel-section">
		<?php echo Utils::build_section_header( $title = 'Terms Excluded' ); ?>

		<fieldset data-fieldset-id="filters" class="widget-panel-settings widget-panel-fieldset settings-filters">

			<legend class="screen-reader-text"><span><?php _e('Terms Excluded') ?></span></legend>

			<div class="description widget-panel-description">
				<?php echo wpautop( __( 'Use the following fields to limit your list to certain categories.' ) ); ?>
			</div>

			<?php

			if( count( $taxes ) ) :
				foreach ( $taxes as $name => $label ) {
					Utils::build_term_select( $name, $label, $instance, $this );
				}
			endif;
			?>

		</fieldset>
	</div><!-- /.widget-panel-section -->
	*/
	?>

</div>
