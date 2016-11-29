<?php
$error_msg_prefix = __( 'Brochure Tools product information shortcode error: ',  NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );

extract( shortcode_atts( array(
	'region' 		=> '', 	// Region [required]
	'location'		=> '',	// [required]
	'type' 			=> '',  // [required]
	'experience'	=> NULL,// [optional]
	'english'		=> 'yes',	// [optional]

), $atts ) );

/*
*	@date_modified: 28-11-16
* @change log: Edited location as this doesn't reflect with experiences.
*/

//Check for required attributes - If non output an error message
if( empty($region) || empty($type) ){ //need a check for experience or location

	echo $error_msg_prefix . __( 'Need to set a region, location and type', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
	return;
}

$request 	= Brochure_Tools_Helper::init_api('depga');
$cache	 	= Brochure_Tools_Helper::init_cache();

if(empty($experience)){
	$cache_call = lcfirst( $region ).'_'.lcfirst( $location ).'_'.lcfirst( $type );
}else{
	$cache_call = lcfirst( $region ).'_'.lcfirst( $type ).'_'.lcfirst( $experience ) ;
}

$response = $cache->get( $cache_call );

if(empty($response)){
	$response 	= $request->getOperators( $type,$location,$experience,$region );

	if(!empty( $response->success )){
		$cache->set( $cache_call ,$response, 14400);
	}

}

$error = 0;
$output = '<div>';

//Lets do some error checking of the response - 1 ) No response returned
if(empty($response)){
	$error = 1;
	$output = $error_msg_prefix . __( 'There has been an error requesting this information', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
			return;
}elseif(empty( $response->success )) {
	$error = 1;
	$output = $error_msg_prefix . __( 'There are no results for this request - '.$response->message, NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
			return;
}else{
	$output .= '<pre>';
	$output .= print_r($response,true);
	$output .= '</pre>';
}

$output .= '</div>';

echo apply_filters( 'product_listings_output', $output, $response, $atts, $error );
