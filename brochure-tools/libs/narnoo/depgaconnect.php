<?php
/*
*
* Class to connect with the brochuretools API endpoint..
* @date_modified: 12-10-16
* @change: Updated brochuretools API endpoint URI
*
*/
class Depgaconnect extends WebClient {

    public $url = 'https://connect.narnoo.com/brochuretools/';
    public $authen;

    public function __construct($authenticate) {

        $this->authen = $authenticate;
    }

    public function getOperators($type,$location=NULL,$experience=NULL,$region=NULL,$language=NULL) {

        $method = 'operators';

        //build tracker query
        $params = array(
            'type'              => $type,
            'location'          => $location,
            'experience'        => $experience,
            'region'            => $region,
            'language'          => $language
        );

        $this->setUrl( $this->url . $method .'?'.http_build_query( $params )  );
        $this->setGet( );
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function builder($op_id,$region,$images=NULL,$social=NULL) {

        $method = 'builder';

        //build tracker query
        $params = array(
            'operator'          => $op_id,
            'region'            => $region,
            'images'            => $images,
            'social'            => $social
        );

        $this->setUrl( $this->url . $method .'?'.http_build_query( $params )  );
        $this->setGet( );
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }


    public function getBrochures( $bro_id,$page=NULL ) {

        $method = 'brochures';
        $params = array(
            'brochures'        => $bro_id
        );
        if(!empty($page)){
            $params['page'] = $page;
        }

        $this->setUrl( $this->url . $method .'?'.http_build_query( $params )  );
        $this->setGet( );
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }


    public function getMaps( $region,$location,$bro_num = NULL ) {

        $method = 'depga_maps';

        $params = array(
            'region'        => $region,
            'location'      => $location,
            'maps'          => $bro_num
        );


        $this->setUrl( $this->url . $method .'?'.http_build_query( $params )  );
        $this->setGet( );
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getActivity( $year,$month,$region=NULL,$type=NULL ) {

        $method = 'activity';

        $params = array(
            'year'           => $year,
            'month'          => $month,
            'region'         => $region,
            'media'           => $type
        );

        $this->setUrl( $this->url . $method .'?'.http_build_query( $params )  );
        $this->setGet( );
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getAlbumImages($album_id,$page=NULL) {

        $method = 'album_images';

        if( empty($page) ){
          $this->setUrl($this->url . $method .'/' .urlencode( $album_id ) );
        }else{
          $this->setUrl($this->url . $method .'/' .urlencode( $album_id ).'?page='.$page );
        }

        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }



}

?>
