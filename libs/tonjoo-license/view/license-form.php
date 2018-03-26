<style>
	#adminform .hndle {
		margin: 0;
		padding: .5em 15px;
	}
	#adminform .form td {
		padding: 10px 0;
		vertical-align: top;
	}
	#adminform .form .title {
		padding-right: 60px;
	}
	#adminform .form .license-status {
		margin: 0;
		display: inline-block;
		color: #fff;
		padding: 2px 7px 2px 2px;
		border-radius: 4px;
	}
	#adminform .form .license-status.stat-error {
		background-color: #ca4a1f;
	}
	#adminform .form .license-status.stat-success {
		background-color: #46b450;
	}
	#adminform .form .license-status span {
		display: inline-block;
		vertical-align: top;
		line-height: 20px;
	}
	#adminform .form .license-status .dashicons {
		line-height: 22px;
	}
	#adminform .form .license-field {
	}
	#adminform .form .license-field .input-key {
		width: 300px;
	}
	#adminform .form .license-field .status {
		padding-top: 5px;
		min-height: 23px;
	}
	#adminform .form .license-field .status.stat-error {
		color: #ca4a1f;
	}
	#adminform .form .license-field .status.stat-success {
		color: #46b450;
	}
</style>
<div class="meta-box">
	<div id="adminform" class="postbox">
		<h3 class="hndle">License for <?php echo esc_html( $plugin_info['Name'] ); ?></h3>
		<div class="inside" style="z-index:1;">

			<?php do_action( 'tj_license_before_form_' . $this->plugin ); ?>

			<table class="form">
				<tr>
					<td class="title"><?php esc_html_e( 'License Status', 'tpc' ); ?></td>
					<td class="field">
						<?php if ( $is_active ) : ?>
							<p class="license-status stat-success">
								<span class="dashicons dashicons-yes"></span>
								<?php if ( 'trial' === $license_info['type'] ) : ?>
									<span><?php esc_html_e( 'Trial Activated', 'tpc' ); ?></span>
								<?php else : ?>
									<span><?php esc_html_e( 'Activated', 'tpc' ); ?></span>
								<?php endif; ?>
							</p>
						<?php else : ?>
							<?php if ( $is_expired ) : ?>
								<p class="license-status stat-error">
									<span class="dashicons dashicons-no-alt"></span>
									<?php if ( 'trial' === $license_info['type'] ) : ?>
										<span><?php esc_html_e( 'Trial Expired', 'tpc' ); ?></span>
									<?php else : ?>
										<span><?php esc_html_e( 'Expired', 'tpc' ); ?></span>
									<?php endif; ?>
								</p>
							<?php else : ?>
								<p class="license-status stat-error">
									<span class="dashicons dashicons-no-alt"></span>
									<span><?php esc_html_e( 'Not Activated', 'tpc' ); ?></span>
								</p>
							<?php endif; ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td class="title"><?php esc_html_e( 'Your License Code', 'tpc' ); ?></td>
					<td class="field">
						<div class="license-field">
							<div class="field">
								<input type="text" class="input-key" value="<?php echo esc_attr( $license_info['key'] ); ?>" <?php echo $is_active ? 'readonly' : ''; ?>>
								<?php if ( $is_active ) : ?>
									<button data-context="deactivate" class="submit-key button-primary"><?php esc_html_e( 'Deactivate', 'tpc' ); ?></button>
								<?php else : ?>
									<button data-context="activate" class="submit-key button-primary"><?php echo $is_expired ? __( 'Renew License', 'tpc' ) : __( 'Check License', 'tpc' ); ?></button>
								<?php endif; ?>
							</div>
							<div class="status <?php echo $is_active ? 'stat-success' : 'stat-error'; ?>">
								<?php
								if ( $is_active ) {
									printf( __( 'Valid until: %s', 'tpc' ), $this->get_local_time( $license_info['expiry'] ) );
									if ( ( $license_info['expiry'] - time() ) < WEEK_IN_SECONDS ) {
										printf( __( '(expired soon, <a href="%s" target="_blank">click to renew</a>)', 'tpc' ), 'https://tonjoostudio.com/manage/user/myItem/' );
									}
								} elseif ( $is_expired ) {
									printf( __( 'Expired at: %s', 'tpc' ), $this->get_local_time( $license_info['expiry'] ) );
								} else {
									esc_html_e( 'Input your license key', 'tpc' );
								}
								?>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="title"><?php esc_html_e( 'Last Check' ,'tpc' ); ?></td>
					<td class="field last-check">
						<?php echo esc_html( $this->get_local_time( $license_info['last_check'] ) ); ?>
					</td>
				</tr>
			</table>
			<p style="margin-bottom:0;"><?php printf( __( 'Find your license code at <a href="%1$s" target="_blank">%2$s</a>.', 'tpc' ), 'https://tonjoostudio.com/manage/plugin/', 'https://tonjoostudio.com/manage/plugin/' ); ?></p>
			<p style="margin:0;"><?php printf( __( 'If you have problems with the license, reach us at <a href="%s" target="_blank">Our Forum</a>.', 'tpc' ), 'https://forum.tonjoostudio.com/' ); ?></p>

			<?php do_action( 'tj_license_after_form_' . $this->plugin ); ?>
		</div>
	</div>
</div>
<script>
	jQuery(function($) {
		function setStatus( status, message ) {
			if ( "error" === status ) {
				$('.license-field .status').removeClass('stat-success').addClass('stat-error').html(message);
			} else {
				$('.license-field .status').removeClass('stat-error').addClass('stat-success').html(message);
			}
		}
		$('button.submit-key').on('click', function() {
			var key = $('input.input-key').val();
			var context = $(this).data('context');
			if ( 'activate' === context ) {
				var data = {
					action: 'tj_activate_plugin_<?php echo esc_html( $this->plugin ); ?>',
					key: key,
					tj_license: '<?php echo esc_html( wp_create_nonce( 'tonjoo-activate-license' ) ); ?>'
				}
			} else {
				var data = {
					action: 'tj_deactivate_plugin_<?php echo esc_html( $this->plugin ); ?>',
					key: key,
					tj_license: '<?php echo esc_html( wp_create_nonce( 'tonjoo-deactivate-license' ) ); ?>'
				}
			}
			$.ajax({
				url: ajaxurl,
				dataType: 'json',
				type: 'post',
				data: data,
				context: this,
				beforeSend: function() {
					$(this).prop('disabled',true);
					$('.license-field .status').removeClass('stat-success').addClass('stat-error').html('Waiting for response...');
				},
				success: function(response) {
					if ( response.status ) {
						location.reload();
					} else {
						setStatus( 'error', response.message );
						$('.last-check').html(response.last_check);
						$(this).prop('disabled',false);
					}
				},
				error: function(data) {
					$(this).prop('disabled',false);
					setStatus( 'error', 'Unknown error' );
				}
			});
		});

	});
</script>
