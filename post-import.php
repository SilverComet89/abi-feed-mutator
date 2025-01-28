<?php
add_action( 'pmxi_saved_post', 'update_gallery_ids', 10, 3 );
add_action( 'pmxi_saved_post', 'getlatlong', 10, 3 );

function update_gallery_ids( $id ) {

    // Declare our variable for the image IDs.
    $image_ids = array();

    // Retrieve all attached images.
    $media = get_attached_media( 'image', $id );

    // Ensure there are images to process.
    if( !empty( $media ) ) {
        $i = 1;
        // Process each image.
        foreach( $media as $item ) {
            if($i > 60) {
                continue;
            }
            // Add each image ID to our array.
            add_post_meta( $id, 'fave_property_images', $item->ID );
            $i++;
        }

        // Convert our array of IDs to a comma separated list.
        $image_ids_str = implode( ',',$image_ids );

        // Save the IDs to the _image_field custom field.
        update_post_meta( $id, 'fave_property_images', $image_ids );
        
    }
}


function getlatlong($id) {
    $metaData = get_post_custom($id);
    if ($metaData['property_latitude'][0] == '28.30285748710253') {
        $params = [
            'addressdetails' => 1,
            //'country' => 'us',
            'format' => 'json',
            'email' => 'ben@silvercomet.co.uk',
            'q' => $metaData['property_address'][0] . ' ' . $metaData['property_zip'][0]
        ];
        
        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query($params);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
    
        if (curl_errno($ch)) { 
            $error = curl_error($ch); 
            var_dump('error', $error);
        } else { 
            curl_close($ch); 
        }
    
        $body = json_decode($data, true);
        if(isset($body[0]['lat'])) {
            update_field('property_latitude', $body[0]['lat'], $id);
            update_field('property_longitude', $body[0]['lon'], $id);
        } elseif (stripos($metaData['property_address'][0], 'Draw St')) { 
            update_field('property_latitude', '28.2832662', $id);
            update_field('property_longitude', '-81.6065561', $id);
        } elseif (stripos($metaData['property_address'][0], 'Putting Green')) {
            update_field('property_latitude', '28.283123', $id);
            update_field('property_longitude', '-81.6061899', $id);
        } elseif (stripos($metaData['property_address'][0], 'Centre Court Ridge')) {
            update_field('property_latitude', '28.2691135', $id);
            update_field('property_longitude', '-81.5948489', $id);
        } elseif (stripos($metaData['property_address'][0], 'Sandy Ridge Dr')) {
            update_field('property_latitude', '28.275142', $id);
            update_field('property_longitude', '-81.5972309', $id);
        } elseif (stripos($metaData['property_address'][0], 'Palmilla Court')) {
            update_field('property_latitude', '28.2854993', $id);
            update_field('property_longitude', '-81.6082714', $id);
        } elseif (stripos($metaData['property_address'][0], 'Corolla Court')) {
            update_field('property_latitude', '28.2638537', $id);
            update_field('property_longitude', '-81.5899776', $id);
        } elseif (stripos($metaData['property_address'][0], 'Jack Nicklaus')) {
            update_field('property_latitude', '28.2775105', $id);
            update_field('property_longitude', '-81.605131', $id);
        } elseif (stripos($metaData['property_address'][0], 'Whisper Way')) {
            update_field('property_latitude', '28.2716878', $id);
            update_field('property_longitude', '-81.5972702', $id);
        } elseif (stripos($metaData['property_address'][0], 'Gathering Drive')) {
            update_field('property_latitude', '28.2718363', $id);
            update_field('property_longitude', '-81.5882055', $id);
        } elseif (stripos($metaData['property_address'][0], 'Sunset View')) {
            update_field('property_latitude', '28.2761451', $id);
            update_field('property_longitude', '-81.5944859', $id);
        } elseif (stripos($metaData['property_address'][0], 'Driving Range Court')) {
            update_field('property_latitude', '28.2833903', $id);
            update_field('property_longitude', '-81.606158', $id);
        } elseif (stripos($metaData['property_address'][0], 'Mourning Dove Circle')) {
            update_field('property_latitude', '28.2614239', $id);
            update_field('property_longitude', '-81.589184', $id);
        } elseif (stripos($metaData['property_address'][0], 'Excitement Dr')) {
            update_field('property_latitude', '28.2674769', $id);
            update_field('property_longitude', '-81.5857613', $id);
        } elseif (stripos($metaData['property_address'][0], 'Heritage Crossing')) {
            update_field('property_latitude', '28.260267', $id);
            update_field('property_longitude', '-81.5964986', $id);
        } elseif (stripos($metaData['property_address'][0], 'Seven Eagles')) {
            update_field('property_latitude', '28.2695263', $id);
            update_field('property_longitude', '-81.59141', $id);
        } elseif (stripos($metaData['property_address'][0], 'Cabana Court')) {
            update_field('property_latitude', '28.2728176', $id);
            update_field('property_longitude', '-81.5954331', $id);
        }
    }
    
    if($metaData['property_action_category'][0] == 'Villa') {
        update_field('prop_featured', '1', $id);
    }
    if($metaData['property_action_category'][0] == 'Mansion') {
        update_field('prop_featured', '1', $id);
    }
    
    $postForSave = get_post($id);
    $original = $postForSave->post_title;
    $postForSave->post_title = $postForSave->post_title . ' ';
    wp_update_post($id);
    
    $postForSave->post_title = $original;
    wp_update_post($id);
}
	
?>
