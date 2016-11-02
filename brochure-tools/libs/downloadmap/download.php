<?php
$error_msg_prefix = __( 'Brochure Tools product information shortcode error: ',  NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );

extract( shortcode_atts( array(
	'map'		=> '',	// [required]

), $atts ) );

//Check for required attributes - If non output an error message
if( empty($map) ){

	echo $error_msg_prefix . __( 'Need to set an map id', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
	return;
}

$request 	= Brochure_Tools_Helper::init_api('distributor');
$response 	= $request->downloadBrochure( $map );

?>


<?php 

//Lets do some error checking of the response - 1 ) No response returned

if(empty($response)){ 
	echo $error_msg_prefix . __( 'There has been an error requesting this information', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
	return;
}else{

echo $response->download_brochure_file;

}