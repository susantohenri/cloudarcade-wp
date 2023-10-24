<?php
function cawp_sync_games() {
    global $wpdb, $second_db;  // access both databases

    // if wp error
    if( is_wp_error( $second_db->error ) ){
        return '';
    }
    
    $count_newly_added_games = 0;
    $count_exist_games = 0;
    // Get all games from the second database
    $games = $second_db->get_results("SELECT * FROM games");

    $game_categories = [];
    foreach ($second_db->get_results("SELECT * FROM categories") as $cat) $game_categories[$cat->name] = $cat;

    // Loop through each game
    foreach($games as $game) {
        // Check if the game post already exists in WordPress based on some unique identifier (e.g., game ID)
        $existing_game = get_posts(array(
            'post_type'      => 'game',
            'meta_key'       => 'game_id',  // Adjust to the actual meta key for the unique identifier
            'meta_value'     => $game->id,  // Adjust to the actual value of the unique identifier
            'posts_per_page' => 1,
        ));

        if ($existing_game) {
            if ($existing_game[0]->post_modified != $game->last_modified) {
                $count_exist_games++;
                // Game post already exists, update the custom fields
                $game_post_id = $existing_game[0]->ID;

                update_post_meta($game_post_id, 'game_instructions', $game->instructions);
                update_post_meta($game_post_id, 'game_thumb1', $game->thumb_1);
                update_post_meta($game_post_id, 'game_thumb2', $game->thumb_2);
                update_post_meta($game_post_id, 'game_url', $game->url);
                update_post_meta($game_post_id, 'game_width', $game->width);
                update_post_meta($game_post_id, 'game_height', $game->height);
                // Update other custom fields as needed

                // Assign categories
                if (!empty($game->category)) {
                    $categories = explode(',', $game->category);
                    $term_ids = array();

                    foreach ($categories as $category) {
                        $category_slug = sanitize_title($category);
                        $term = term_exists($category, 'game_category');

                        if ($term) {
                            $term_ids[] = $term['term_id'];
                        } else {
                            $new_term = wp_insert_term($category, 'game_category', array('slug' => $category_slug, 'description' => $game_categories[$category]->description));
                            if (!is_wp_error($new_term) && isset($new_term['term_id'])) {
                                $term_ids[] = $new_term['term_id'];
                            }
                        }
                    }

                    wp_set_post_terms($game_post_id, $term_ids, 'game_category', false);
                }
            }
        } else {
            $count_newly_added_games++;
            // Game post doesn't exist, create a new game post and set the custom fields
            $new_game = array(
                'post_title'   => $game->title,
                'post_name'    => $game->slug,
                'post_content' => $game->description,
                'post_type'    => 'game',
                'post_status'  => 'publish',
                // Add other fields here
            );

            $new_game_id = wp_insert_post($new_game);

            if (!is_wp_error($new_game_id)) {
                // Set the custom fields for the new game post
                update_post_meta($new_game_id, 'game_instructions', $game->instructions);
                update_post_meta($new_game_id, 'game_thumb1', $game->thumb_1);
                update_post_meta($new_game_id, 'game_thumb2', $game->thumb_2);
                update_post_meta($new_game_id, 'game_url', $game->url);
                update_post_meta($new_game_id, 'game_width', $game->width);
                update_post_meta($new_game_id, 'game_height', $game->height);
                update_post_meta($new_game_id, 'game_id', $game->id);
                // Set other custom fields as needed

                // Assign categories
                if (!empty($game->category)) {
                    $categories = explode(',', $game->category);
                    $term_ids = array();

                    foreach($categories as $category) {
                        $category_slug = sanitize_title($category);
                        $term = term_exists($category, 'game_category');
                        if ($term) {
                            $term_ids[] = $term['term_id'];
                        } else {
                            $new_term = wp_insert_term($category, 'game_category', array('slug' => $category_slug, 'description' => $game_categories[$category]->description));
                            if (!is_wp_error($new_term) && isset($new_term['term_id'])) {
                                $term_ids[] = $new_term['term_id'];
                            }
                        }
                    }

                    wp_set_post_terms($new_game_id, $term_ids, 'game_category', false);
                }
            }
        }
    }
    // Return a success message
    cawp_delete_unused_categories();
    return $count_newly_added_games." games added. ".$count_exist_games." already exist!";
}



function cawp_delete_all_game_posts() {
    // Query all game posts
    $args = array(
        'post_type'      => 'game',
        'posts_per_page' => -1, // Get all posts
    );

    $game_posts = get_posts($args);

    // Delete each game post
    foreach ($game_posts as $game_post) {
        wp_delete_post($game_post->ID, true);
    }

    // Return a success message
    return count($game_posts) . ' games removed.';
}

function cawp_delete_unused_categories () {
    $terms = get_terms( [
        'taxonomy'               => 'game_category',
        'hide_empty'             => false,
    ] );

    foreach ( $terms as $t ) {
        if ( 0 === $t->count ) {
            wp_delete_term( $t->term_id, 'game_category' );
        }
    }
}