<?php
/**
 * Custom sidebars page
 *
 * @package ThemeMove_Custom_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
?>
<div class="tmc-wrap<?php echo esc_attr( class_exists( 'ThemeMove_Core' ) ? '' : ' no-tmc' ); ?>">
	<?php
	if ( class_exists( 'ThemeMove_Core' ) ) {
		require_once TMC_DIR . 'views/header.php';
	}
	?>
	<div class="tmc-body">

		<?php
		/**
		 * Action: tmc_page_custom_sidebars_before_content
		 */
		do_action( 'tmc_page_custom_sidebars_before_content' );
		?>

		<!-- Custom Sidebars -->
		<div class="tmc-box tmc-box--gray tmc-box--custom-sidebars">

			<?php if ( class_exists( 'ThemeMove_Core' ) ) : ?>
				<div class="tmc-box__header">
					<span class="tmc-box__icon"><i class="far fa-columns"></i></span>
					<span><?php esc_html_e( 'Custom Sidebars', 'tm-custom-sidebars' ); ?></span>
				</div>
			<?php endif; ?>

			<div class="tmc-box__body">

				<p class="tmc-error-text"></p>

				<table class="wp-list-table widefat striped" id="tm-custom-sidebars-table">
					<thead>
						<tr>
								<th><?php esc_html_e( 'Sidebar Name', 'tm-custom-sidebars' ); ?></th>
								<th><?php esc_html_e( 'CSS Class', 'tm-custom-sidebars' ); ?></th>
								<th><?php esc_html_e( 'Action', 'tm-custom-sidebars' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $sidebars ) ) : ?>
							<?php foreach ( $sidebars as $slug => $sidebar ) : ?>
								<tr>
									<td><?php echo esc_html( $sidebar ); ?></td>
									<td><?php echo esc_html( $slug ); ?></td>
									<td>
										<a href="#" class="tm-remove-sidebar" data-slug="<?php echo esc_attr( $slug ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( "remove_custom_sidebar_{$slug}" ) ); ?>">
											<i class="fal fa-times"></i> <?php esc_html_e( 'Remove', 'tm-custom-sidebars' ); ?>
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
						<tr class="tm-custom-sidebars-empty">
							<td colspan="3"><?php esc_html_e( 'No custom sidebar created', 'tm-custom-sidebars' ); ?></td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>

				<form action="" method="POST" id="tm-custom-sidebars-form">
					<input type="text" name="sidebar_name" id="sidebar-name" placeholder="<?php esc_attr_e( 'Sidebar Name', 'tm-custom-sidebars' ); ?>" >
					<input type="hidden" name="_wpnonce" id="sidebar-wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'add_custom_sidebar' ) ); ?>">
					<button class="button<?php echo esc_attr( class_exists( 'ThemeMove_Core' ) ? '' : ' button-primary' ); ?>"><?php esc_html_e( 'Add New Sidebar', 'tm-custom-sidebars' ); ?></button>
				</form>
			</div>
		</div>
		<!-- /Custom Sidebars -->

		<?php if ( ! class_exists( 'ThemeMove_Core' ) ) : ?>
		<div class="tm-custom-sidebars-import-export">
			<!-- Export Custom Sidebars -->
			<form action="<?php echo esc_url( admin_url( '/admin-post.php' ) ); ?>" method="POST" id="tm-custom-sidebars-export">
				<h3><?php esc_html_e( 'Export Custom Sidebars', 'tm-custom-sidebars' ); ?></h3>
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Export', 'tm-custom-sidebars' ); ?>
				</button>
				<input type="hidden" name="_wpnonce" value=<?php echo esc_attr( wp_create_nonce( 'export_custom_sidebars' ) ); ?>>
				<input type="hidden" name="action" value="export_custom_sidebars">
			</form>
			<!-- /Export Custom Sidebars -->

			<!-- Import Custom Sidebars -->
			<form action="<?php echo esc_url( admin_url( '/admin-post.php' ) ); ?>" method="POST" enctype="multipart/form-data" id="tm-custom-sidebars-import">
				<h3><?php esc_html_e( 'Import Custom Sidebars', 'tm-custom-sidebars' ); ?></h3>
				<input type="file" name="import_file" accept="application/json">
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Import', 'tm-custom-sidebars' ); ?>
				</button>
				<input type="hidden" name="_wpnonce" value=<?php echo esc_attr( wp_create_nonce( 'import_custom_sidebars_wo_tmc' ) ); ?>>
				<input type="hidden" name="action" value="import_custom_sidebars_wo_tmc">
			</form>
			<!-- /Import Custom Sidebars -->
		</div>
		<?php endif; ?>

		<?php
		/**
		 * Action: tmc_page_custom_sidebars_after_content
		 */
		do_action( 'tmc_page_custom_sidebars_after_content' );
		?>
	</div>
</div>
