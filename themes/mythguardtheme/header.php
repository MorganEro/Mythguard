<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?> style="--theme-url: '<?php echo get_theme_file_uri(); ?>';">
  <header class="site-header">
    <div class="container">
      <div class="site-header__inner">
        <div class="site-header__mobile-group">
          <h1 class="agency-logo-text">
            <a href="<?php echo site_url(); ?>"><strong>Myth<span class="c-gold">Guard</span></strong></a>
          </h1>
          <a href="<?php echo site_url('/search'); ?>" class="js-search-trigger site-header__search-trigger forMobile"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></a>
          <i class="site-header__menu-trigger fa-solid fa-bars forMobile" aria-hidden="true"></i>
        </div>
        <div class="site-header__menu">
          <nav class="main-navigation">
            <ul class="main-navigation__list">
              <li <?php if (get_post_type() == 'program') echo 'class="current-menu-item"' ?>><a href="<?php echo get_post_type_archive_link('program'); ?>">Programs</a></li>
              <li <?php if (get_post_type() == 'guardian')  echo 'class="current-menu-item"' ?>><a href="<?php echo get_post_type_archive_link('guardian'); ?>">Guardians</a></li>
              <?php if (is_user_logged_in()) : ?>
                <li <?php if (get_post_type() == 'contract')  echo 'class="current-menu-item"' ?>><a href="<?php echo esc_url(site_url('/contracts')); ?>">Contracts</a></li>
              <?php endif; ?>
              <li <?php if (get_post_type() == 'gathering' or is_page('past-gatherings'))  echo 'class="current-menu-item"' ?>><a href="<?php echo get_post_type_archive_link('gathering'); ?>">Gatherings</a></li>
              <li <?php if (get_post_type() == 'post') echo 'class="current-menu-item"' ?>><a href="<?php echo site_url('/blog'); ?>">Chronicles</a></li>
              <li <?php if (get_post_type() == 'location') echo 'class="current-menu-item"' ?>><a href="<?php echo get_post_type_archive_link('location'); ?>">Locations</a></li>
              <li <?php if (is_page('about-us') or wp_get_post_parent_id(0) == 16) echo 'class="current-menu-item"' ?>><a href="<?php echo site_url('/about-us'); ?>">About Us</a></li>

            </ul>
          </nav>
          <div class="site-header__util">
            <?php if (is_user_logged_in()) {
            ?>
              <a href="<?php echo esc_url(site_url('/codex')); ?>" class="btn btn--small btn--yellow push-right">My Codex</a>
              <a href="<?php echo wp_logout_url(); ?>" class="btn btn--small btn--orange btn--with-photo">
                <span class="site-header__avatar"><?php echo get_avatar(get_current_user_id(), 60); ?></span>
                <span class="btn__text">Logout</span></a>
            <?php } else { ?>
              <a href="<?php echo wp_login_url(); ?>" class="btn btn--small btn--yellow push-right">Login</a>
              <a href="<?php echo wp_registration_url(); ?>" class="btn btn--small btn--orange">Sign Up</a>
            <?php } ?>

            <a href="<?php echo esc_url(site_url('/search')); ?>" class="js-search-trigger search-trigger"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></a>

          </div>
        </div>
      </div>
    </div>
  </header>

  <?php if (is_user_logged_in()) : ?>
    <?php get_template_part('template-parts/content-calendar-widget'); ?>
  <?php endif; ?>