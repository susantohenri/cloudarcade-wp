<?php
/**
 * single game block output for archives
 */
 	
$out = '
<li id="post-' . get_the_ID() . '" class="' . implode(' ', get_post_class()) . '">
    <a href="'.get_the_permalink().'" class="archive-game-link">
        <img src="'.get_post_meta(get_the_ID(), 'game_thumb2', true).'">
        <h2 class="loop-game-title">'.get_the_title().'</h2>
    </a>
</li>';
echo $out;

?>
