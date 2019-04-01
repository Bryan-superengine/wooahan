/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.toolbarCanCollapse = true;
	config.height = 800;
	config.filebrowserUploadMethod = 'form';
	config.filebrowserUploadUrl = ajaxurl+'?action=ckeditor_image_upload';
	//console.log(config.filebrowserUploadUrl);
	//config.filebrowserUploadUrl = '/wp-content/plugins/wooahan/ckeditor-image-upload.php';
};
