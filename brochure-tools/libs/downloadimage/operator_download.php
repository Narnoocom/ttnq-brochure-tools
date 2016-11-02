<?php
$error_msg_prefix = __( 'Brochure Tools product information shortcode error: ',  NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );

extract( shortcode_atts( array(
	'operator' 	=> '', 	// Region [required]
	'image'		=> '',	// [required]

), $atts ) );

//Check for required attributes - If non output an error message
if( empty($operator) || empty($image) ){

	echo $error_msg_prefix . __( 'Need to set an operator id and an image id', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
	return;
}

$request 	= Brochure_Tools_Helper::init_api('operator');
$response 	= $request->downloadImage( $operator,$image );

?>

<div>
<?php 

//Lets do some error checking of the response - 1 ) No response returned

if(empty($response)){ 
			echo $error_msg_prefix . __( 'There has been an error requesting this information', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
			return;
}else{
?>
<pre>
<?php print_r($response); ?>
</pre>
</div>
<? 
}