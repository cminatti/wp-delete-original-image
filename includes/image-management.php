<?php
/*
 *	Image management functions
 */

class Doi_Images {

	public function __construct() {

	}

	function get_delete_all() {

            $query_images_args = array(
                'post_type' => 'attachment', 
                'post_mime_type' =>'image', 
                'post_status' => 'inherit', 
                'posts_per_page' => -1,
            );

            $query_images = new WP_Query( $query_images_args );
            $this->images = array();

            //delete original images
            echo '<hr> DEBUG <br><br>';
            $sumSizes = 0;
            $count = 0;

            foreach ( $query_images->posts as $image_post) {
	
                $count++;
              
//            if ($image_post->ID !== 6) continue; 
            
//            var_dump($image_post); continue;
            
//            echo 'get_post_meta() ';
//            var_dump(get_post_meta($image_post->ID));
            
//            var_dump(wp_get_attachment_metadata($image_post-ID));
                $data = get_post_meta( $image_post->ID, '_wp_attachment_metadata', true );

                $upload_dir = wp_upload_dir();

                if ( !isset($data['file']) || !isset($data['image_meta']) ) {
                    echo 'Skip attachment that is not image <br>';
                    echo nl2br(print_r($data,true));
                    continue;
                }; //not an image attachment
                if (!isset($data['sizes']['large'])) { //if not a 'large' image, continue
                    echo 'Skipping small file: '.$data['file'].'<br>';
                    continue;
                }
                
                $sizeOrig = 0;
                if ( file_exists($upload_dir['basedir'].'/'.$data['file'])) {
                    $sizeOrig = filesize($upload_dir['basedir'].'/'.$data['file']);
                } else {
                    echo 'Fixed image. '.$data['file'].'<br>';
                }

//                echo nl2br(print_r($data,true)).'<br>';
//                echo 'Reduced '.$data['file'].'<br><br>'; continue; //for debug and do nothing
                    
                $data = doi_replace_uploaded_image($data); //copy the 'large' image to the 'full' (original) and updates $data array
    //            var_dump($data);

                $sizeNew = filesize($upload_dir['basedir'].'/'.$data['file']);

                //update the '_wp_attachment_metadata' field in database (serizlized array $data)
                wp_update_attachment_metadata( $image_post->ID, $data );

                //update the '_wp_attached_file' field in database
                update_post_meta($image_post->ID, '_wp_attached_file', $data['file']);


                echo 'Reduced '.$data['file'].'. Saved: '. round((($sizeOrig-$sizeNew)/1024/1024),2) .' MB <br>';

                $sumSizes += (($sizeOrig-$sizeNew)/1024/1024);
            
            }
        
            echo 'Total space recovered: '.round($sumSizes,2).' MB <br>';
            echo 'Total attachments: '.($count).' <br>';
            echo '<hr>';
    }


}