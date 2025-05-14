<li class="program-list-item">
                <a href="<?php the_permalink(); ?>">
                    <?php
                    $icon_url = get_field('program_icon');
                    if ($icon_url && str_ends_with($icon_url, '.svg')) {
                        // Convert URL to file path on the server
                        $icon_path = str_replace(home_url('/'), ABSPATH, $icon_url);
                        
                        if (file_exists($icon_path)) {
                            // Output inline SVG
                            echo '<div class="program-icon">';
                            echo file_get_contents($icon_path);
                            echo '</div>';
                        }
                    }
                    ?>
                    <?php the_title(); ?>
                </a>
            </li>