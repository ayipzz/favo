jQuery(function($){

	/**
	* Handle button favorite
	*/
	function action_favo(product_id) {		
		// check cookie
		if( Cookies.get('favo_product') ) {
			var list_favo_product = JSON.parse(Cookies.get('favo_product'));
			var position = list_favo_product.indexOf(product_id);
			if( position == -1 ){
				list_favo_product.push( product_id );
				set_favo('insert', product_id);
			} else {
				// hapus dari daftar cookie
				list_favo_product.splice(position, 1);
				set_favo('delete', product_id);
			}
			var json_str = JSON.stringify(list_favo_product);
			Cookies.set('favo_product', json_str);
		} else {
			var arr = [product_id];
			var json_str = JSON.stringify(arr);
			Cookies.set('favo_product', json_str);
			set_favo('insert', product_id);
		}
	}

	/**
	* Send & Record product favo to database
	*/
	function set_favo(action, product_id) {
		$(".favo .favo-loading[data-product-id="+product_id+"]").addClass("on");
		var dataPost = {
	        'action'		: 'update_favo',
	        'fav_action'	: action,
	        'product_id' 	: product_id
	    };
	    jQuery.ajax({
			url : favo_object.ajax_url, 
			type: 'POST', 
			data : dataPost, 
			success: function(response){
				$(".favo .favo-loading").removeClass("on");
				var current_button_favo = $(".favo .favo-button[data-product-id=\""+product_id+"\"]");
				if(current_button_favo.hasClass("on")){
					alert(favo_object.remove_success_message);
					if ( favo_object.button_type == 'text' ) {
						current_button_favo.removeClass("on").addClass("off").html(favo_object.off_val).show();
					} else if ( favo_object.button_type == 'image' ) {
						current_button_favo.removeClass("on").addClass("off").attr("src",favo_object.off_val).show();
					}
				} else {
					alert(favo_object.add_success_message);
					if ( favo_object.button_type == 'text' ) {
						current_button_favo.removeClass("off").addClass("on").html(favo_object.on_val).show();
					} else if ( favo_object.button_type == 'image' ) {
						current_button_favo.removeClass("off").addClass("on").attr("src",favo_object.on_val).show();
					}
				}
			}
		});
	}

	$(".favo .favo-button").on("click", function(){
		if ( favo_object.required_login == '' || ( favo_object.required_login == 'yes' && favo_object.is_login ) ) {
			var product_id = $(this).data("product-id");
			$(this).hide();
			action_favo(product_id);
		} else {
			alert( favo_object.required_login_message );
		}
	});
});