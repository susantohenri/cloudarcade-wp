<?php

defined( 'ABSPATH' ) || exit;

do_action( 'cloudarcade_before_single_game' );

?>

<div class="cloudarcade">
	<div class="single-game">
		<?php do_action( 'cloudarcade_before_game_iframe' ); ?>
		<div class="game-iframe-container">
			<iframe class="game-iframe" src="<?php echo get_post_meta(get_the_ID(), 'game_url', true) ?>" width="<?php echo get_post_meta(get_the_ID(), 'game_width', true) ?>" height="<?php echo get_post_meta(get_the_ID(), 'game_height', true) ?>" scrolling="none" frameborder="0" allowfullscreen></iframe>
		</div>
		<?php do_action( 'cloudarcade_after_game_iframe' ); ?>
		<?php do_action( 'cloudarcade_before_game_info' ); ?>
		<div class="game-info">
			<div class="game-description">
				<h2>Description</h2>
				<?php echo get_the_content(); ?>
			</div>
			<div class="game-instructions">
				<h2>Instructions</h2>
				<?php echo get_post_meta(get_the_ID(), 'game_instructions', true) ?>
			</div>
		</div>
		<?php do_action( 'cloudarcade_after_game_info' ); ?>
<?php
// Get the game_category terms for the current post
$categories = get_the_terms(get_the_ID(), 'game_category');

if ($categories) {
    echo '<div class="game-categories">';
    echo '<h2>Categories</h2>';
    echo '<ul>';
    
    foreach ($categories as $category) {
        $category_link = get_term_link($category);
        if (is_wp_error($category_link)) {
            continue; // Skip if there is an error
        }
        echo '<li><a href="' . esc_url($category_link) . '">' . $category->name . '</a></li>';
    }

    echo '</ul>';
    echo '</div>';
}
?>


	</div>
</div>

<?php do_action( 'cloudarcade_after_single_game' ); ?>