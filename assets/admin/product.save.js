jQuery(document).ready(function(){
	jQuery('input#publish, input#save-post').click(function(){
		var postURL = ajax_object.post_url;
		var data = jQuery('form#post').serializeArray();
		data.push({name : 'wooahan_doing_ajax', value: 'true'});
		data.push({name : 'post_title', value: jQuery('div#wooahan-wrap').find('input.product-title').val()});
		data.push({name : 'excerpt', value: jQuery('div#wooahan-wrap').find('textarea.product-excerpt').val()});
		data.push({name : 'post_content', value: jQuery('textarea#wooahan_editor').val()});
        // Replaces wp.autosave.initialCompareString
        var ajax_updated = false;

        /**
         * Supercede the WP beforeunload function to remove
         * the confirm dialog when leaving the page (if we saved via ajax)
         * 
         * The following line of code SHOULD work in $.post.done(), but 
         *     for some reason, wp.autosave.initialCompareString isn't changed 
         *     when called from wp-includes/js/autosave.js
         * wp.autosave.initialCompareString = wp.autosave.getCompareString();
         */
        $(window).unbind('beforeunload.edit-post');
        $(window).on( 'beforeunload.edit-post', function() {
                var editor = typeof tinymce !== 'undefined' && tinymce.get('content');

                // Use our ajax_updated var instead of wp.autosave.initialCompareString

                if ( ( editor && !editor.isHidden() && editor.isDirty() ) ||
                        ( wp.autosave && wp.autosave.getCompareString() != ajax_updated) ) { 
                        return postL10n.saveAlert;
                }   
        });

        //console.log(data);

		jQuery.post(postURL, data, function(response){
			//console.log(response);
			if(response.success){
				jQuery('div#wooahan-wrap').find('span.save-progress').html('');
                                wooahan_toast_alert('save-success', 'Saved successfully', '성공적으로 저장 되었습니다.');
                                // Mark TinyMCE as saved
                                if (typeof tinyMCE !== 'undefined') {
                                        for (id in tinyMCE.editors) {
                                                var editor = tinyMCE.get(id);
                                                editor.isNotDirty = true;
                                        }   
                                }
                                // Update the saved content for the beforeunload check
                                ajax_updated = wp.autosave.getCompareString();

			} else {

			}
		});
		return false;
	});
});

