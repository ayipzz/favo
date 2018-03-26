<?php
/**
 * Admin Template: Dashboard Page
 *
 * @package favo
 * @since 1.0.0
 * @version 1.0.0
 */

?>
<div class="wrap" id="favo-page-wrap">
	<h2><?php esc_html_e( 'About Favo', 'favo' ); ?></h2>
	<h2 class="nav-tab-wrapper">
		<?php foreach ( $tabs as $key => $tab ) : ?>
			<a class="nav-tab nav-tab-<?php echo esc_attr( $key ); ?> nav-tab-active" href="#<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $tab['title'] ); ?></a>
		<?php endforeach; ?>
	</h2>
	<div class="content">
		<?php
		foreach ( $tabs as $key => $tab ) {
			if ( ! empty( $tab['template'] ) ) {
				echo "<div id='tab-" . esc_attr( $key ) . "' class='tab-content'>";
				include $tab['template'];
				echo '</div>';
			}
		}
		?>
	</div>
</div>
