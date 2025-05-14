<?php
$program = $args['program'];
?>
<li class="program-list-item">
    <a href="<?php echo get_the_permalink($program); ?>">
        <?php
        $icon_url = get_field('program_icon', $program);
        if ($icon_url && str_ends_with($icon_url, '.svg')) {
            $icon_path = str_replace(home_url('/'), ABSPATH, $icon_url);

            if (file_exists($icon_path)) {
                echo '<div class="program-icon">';
                echo file_get_contents($icon_path);
                echo '</div>';
            }
        }
        ?>
        <?php echo get_the_title($program); ?>
    </a>
</li>