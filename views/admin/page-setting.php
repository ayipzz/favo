<div class="wrap">
 	<h2>Settings</h2><br />
	<div id="favo-setting">
		<form method='post'>
			<div class="form-section">
				<h4 class="form-section-title">General Setting</h4>
				<div class="input-row">
					<label for="favo_enabled">Enabled</label>
					<div class="input-field">
						<input type="checkbox" name="favo_enabled" id="favo_enabled" value="yes" <?php echo favo_setting('enabled') == 'yes' ? 'checked' : ''; ?> />
					</div>
					<div class="helper">
						Enabled Favo Plugin Function
					</div>
				</div>
				<div class="input-row">
					<label for="favo_enabled">Favorite List</label>
					<div class="input-field">
						<a href="<?php echo site_url( 'favorite-list' ); ?>" target="blank">Page Favorite List</a>
					</div>
					<div class="helper">
						Default page to display favorite list, or use shortcode <code>[favo_list]</code>
					</div>
				</div>
				<div class="input-row">
					<label for="favo_count">Display Favorite Number</label>
					<div class="input-field">
						<input type="checkbox" name="favo_count" id="favo_count" value="yes" <?php echo favo_setting('favo_count') == 'yes' ? 'checked' : ''; ?> />
					</div>
					<div class="helper">
						Displays the number of favorites in each product <i>(beta)</i>
					</div>
				</div>
				<div class="input-row">
					<label for="favo_required_login">Required Login</label>
					<div class="input-field">
						<input type="checkbox" name="favo_required_login" id="favo_required_login" value="yes" <?php echo favo_setting('required_login') == 'yes' ? 'checked' : ''; ?> />
					</div>
					<div class="helper">
						User
					</div>
				</div>
				<!-- <div class="input-row">
					<label for="favo_display_on">Display Button Position</label>
					<div class="input-field">
						<select name="display_position_button" id="display_position_button">
							<option value="after_add_to_cart" <?php echo !empty( favo_setting('display_position_button') ) && 'after_add_to_cart' == favo_setting('display_position_button') ? 'selected' : ''; ?> >After Add to Cart Button</option>
							<option value="product_image" <?php echo !empty( favo_setting('display_position_button') ) && 'product_image' == favo_setting('display_position_button') ? 'selected' : ''; ?> >Product Image</option>
						</select>
					</div>
					<div class="helper">
						Choose position where your favorite button will appear.<br />
						<code><b>Product Image :</b> only work on product loop</code>
					</div>
				</div> -->
				<div class="input-row">
					<label for="favo_display_on">Display Button On</label>
					<div class="input-field">
						<select name="display_on[]" id="favo_display_on" multiple>
							<option value="single_product" <?php echo !empty( favo_setting('display_on') ) && in_array( 'single_product', favo_setting('display_on') ) ? 'selected' : ''; ?> >Single Product</option>
							<option value="loop_product" <?php echo !empty( favo_setting('display_on') ) && in_array( 'loop_product', favo_setting('display_on') ) ? 'selected' : ''; ?>>Loop Product</option>
						</select>
					</div>
					<div class="helper">
						Choose where your favorite button will appear or you can use shortcode <code>[favo_button]</code>
					</div>
				</div>
			</div>

			<div class="form-section">
				<h4 class="form-section-title">Button Setting</h4>
				<div class="input-row">
					<label for="favo_enabled">Type</label>
					<div class="input-field">
						<select name="favo_type" id="favo_type">
							<option value="text" <?php echo favo_setting('type_active') == 'text' ? 'selected' : ''; ?>>Text</option>
							<option value="image" <?php echo favo_setting('type_active') == 'image' ? 'selected="selected"' : ''; ?>>Image</option>
						</select>
					</div>
					<div class="helper">
						Select type favorite button, Image or Text
					</div>
				</div> 
				<div id="favo_type_selected_text" style="display: none;">
					<div class="input-row">
						<label for="favo_enabled">Button Text Off</label>
						<div class="input-field">
							<input type="text" name="favo_text_val_off" value="<?php echo favo_setting('val_off'); ?>" />
						</div>
						<div class="helper">
							Button Text settings when the product has not entered the favorites list
						</div>
					</div>
					<div class="input-row">
						<label for="favo_enabled">Button Text On</label>
						<div class="input-field">
							<input type="text" name="favo_text_val_on" value="<?php echo favo_setting('val_on'); ?>" />
						</div>
						<div class="helper">
							Button Text settings when the product is in the favorites list
						</div>
					</div>
				</div>
				<div id="favo_type_selected_image" style="display: none;">
					<div class="input-row">
						<label for="favo_enabled">Button Image Off</label>
						<div class="input-field">
							<input type="button" class="button favo_image_upload" data-ids="favo_image_val_off" data-values="<?php echo $favo_image_val_off; ?>" value="<?php _e( 'Upload image' ); ?>" />
							<input type='hidden' name='favo_image_val_off' id='favo_image_val_off' value='<?php echo $favo_image_val_off; ?>'>
							<div class='image-preview-wrapper'>
								<img id='preview-favo_image_val_off' src='<?php echo wp_get_attachment_url( $favo_image_val_off ); ?>' height='36'>
							</div>
						</div>
						<div class="helper">
							Button Image settings when the product has not entered the favorites list
						</div>
					</div>
					<div class="input-row">
						<label for="favo_enabled">Button Image On</label>
						<div class="input-field">
							<input type="button" class="button favo_image_upload" data-ids="favo_image_val_on" data-values="<?php echo $favo_image_val_on; ?>" value="<?php _e( 'Upload image' ); ?>" />
							<input type='hidden' name='favo_image_val_on' id='favo_image_val_on' value='<?php echo $favo_image_val_on; ?>'>
							<div class='image-preview-wrapper'>
								<img id='preview-favo_image_val_on' src='<?php echo wp_get_attachment_url( $favo_image_val_on ); ?>' height='36'>
							</div>
						</div>
						<div class="helper">
							Button Image settings when the product is in the favorites list
						</div>
					</div>
				</div>
			</div>

			<div class="form-section">
				<h4 class="form-section-title">Messages Setting</h4>
				<div class="input-row">
					<label for="favo_enabled">Add Success Message</label>
					<div class="input-field">
						<input type="checkbox" name="enable_add_success_message" value="yes" <?php echo favo_setting('enable_add_success_message') == 'yes' ? 'checked' : ''; ?>> Enable<br /><br />
						<input type="text" name="add_success_message" value="<?php echo favo_setting('add_success'); ?>">
					</div>
					<div class="helper">
						The message will appear when the product has been successfully added to the favorites list
					</div>
				</div>
				<div class="input-row">
					<label for="favo_display_on">Remove Success Message</label>
					<div class="input-field">
						<input type="checkbox" name="enable_remove_success_message" value="yes" <?php echo favo_setting('enable_remove_success_message') == 'yes' ? 'checked' : ''; ?>> Enable<br /><br />
						<input type="text" name="remove_success_message" value="<?php echo favo_setting('remove_success'); ?>">
					</div>
					<div class="helper">
						The message will appear when the product was successfully removed from the favorites list
					</div>
				</div>
				<div class="input-row">
					<label for="favo_display_on">Required Login Message</label>
					<div class="input-field">
						<input type="text" name="required_login_message" value="<?php echo favo_setting('required_login_message'); ?>">
					</div>
					<div class="helper">
						The message will appear when the product was successfully removed from the favorites list
					</div>
				</div>
			</div>

			<input type="submit" value="Save Settings" class="button-primary">

			<?php wp_nonce_field( 'favo_action_setting', 'favo_field_setting' ); ?>
		</form>
	</div>
</div>