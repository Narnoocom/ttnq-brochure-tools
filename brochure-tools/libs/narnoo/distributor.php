<?php

class Distributor extends WebClient {

    public $distributor_url = 'https://connect.narnoo.com/distributor/';
    public $authen;

    public function __construct($authenticate) {

        $this->authen = $authenticate;
    }
    
    public function getAccount() {

        $method = 'account';

        $this->setUrl($this->distributor_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    public function getImages($page=NULL) {

        $method = 'images';
        if(!empty($page)){
            $this->setUrl($this->distributor_url . $method.'?'.$page);
        }else {
            $this->setUrl($this->distributor_url . $method);
        }

        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function getVideos($page=NULL) {

        $method = 'videos';

        $this->setUrl($this->distributor_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function getBrochures($page=NULL) {

        $method = 'brochures';

        $this->setUrl($this->distributor_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function getBrochureDetails($bro_id) {

        $method = 'brochure_details';
        

        $this->setUrl($this->distributor_url . $method .'/' .$bro_id);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function getAlbums($page=NULL) {

        $method = 'albums';

        $this->setUrl($this->distributor_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    public function getAlbumImages($album_id,$page=NULL) {

        $method = 'album_images';
        

        $this->setUrl($this->distributor_url . $method .'/' .urlencode($album_id));
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
     public function getLogos($dst_id) {

        $method = 'logos';
        

        $this->setUrl($this->distributor_url . $method .'/'. $dst_id );
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function downloadImage($imageId) {

        $method = 'download_image';
        

        $this->setUrl($this->distributor_url. $method .'/'.$imageId);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }  
   
    public function downloadBrochure($bro_id) {
        
        $method = 'download_brochure';
        $this->setUrl($this->distributor_url. $method. '/' .$bro_id);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    
    
}

?>
