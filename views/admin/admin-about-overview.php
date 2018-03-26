<?php
/**
 * Admin Template: Overview
 *
 * @package favo
 * @since 1.0.0
 * @version 1.0.0
 */

do_action( 'favo_about_overview_before' );
?>
<div class="about-content">
	<div class="row">
		<div class="col-half">

			<img class="logo-ecae" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ); ?>assets/about/ecae_about.png" alt="">

			<p><?php esc_html_e( 'Easy Custom Auto Excerpt is a WordPress plugin to cut/excerpt your posts displayed on home, search or archive pages. This plugin also enables you to customize the read more button text and thumbnail image. Just activate the plugin, configure some options and you are good to go.', 'favo' ); ?></p>

			<div class="main-cta">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=favo-settings' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Settings', 'easy-custom-auto-excerpt' ); ?></a>
				<?php if ( ! $license_handler->is_license_active() ) : ?>
					<a href="#license" class="button button-primary"><?php esc_html_e( 'Activate License', 'easy-custom-auto-excerpt' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<div class="col-half">
			<div class="frame"><iframe css="display:block;margin:0px auto;max-height:300px" width="100%" height="300px" src="https://www.youtube.com/embed/ZZaXfrB4-68?ecver=1" frameborder="0" allowfullscreen=""></iframe></div>
		</div>
	</div>
</div>

<?php do_action( 'favo_about_overview_middle' ); ?>

<div class="row">
	<div class="col-half">
		<div class="more-content">
			<div class="more-text">
				<h3><?php esc_html_e( 'Documentation', 'favo' ); ?></h3>
				<p><?php esc_html_e( "Our online documentation will give you  important information about the plugin. This is an exceptional resource to start discovering the plugin's true potential.", 'favo' ); ?></p>
			</div>
			<div class="more-btn">
				<a href="http://pustaka.tonjoostudio.com/plugins/favo/?utm_source=wp_favo&utm_medium=onboarding_overview&utm_campaign=upsell" class="button-primary">
					<?php esc_html_e( 'View Documentation', 'favo' ); ?>
					<span class="dashicons dashicons-arrow-right-alt"></span>
				</a>
			</div>
		</div>
	</div>

	<div class="col-half">
		<div class="more-content">
			<div class="more-text">
				<h3><?php esc_html_e( 'Support Forum', 'favo' ); ?></h3>
				<p><?php esc_html_e( 'We offer outstanding support through our forum. To get support first you need to register (create an account) and open a thread in our forum.', 'favo' ); ?></p>
			</div>
			<div class="more-btn">
				<a href="https://forum.tonjoostudio.com/?utm_source=wp_favo&utm_medium=onboarding_overview&utm_campaign=upsell" class="button-primary">
					<?php esc_html_e( 'Visit Forum', 'favo' ); ?>
					<span class="dashicons dashicons-arrow-right-alt"></span>
				</a>
			</div>
		</div>
	</div>
</div>

<?php do_action( 'favo_about_overview_after' ); ?>
