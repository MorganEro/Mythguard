<footer class="site-footer">
  <div class="site-footer__inner container container--narrow">
    <div class="site-footer__col-one">
      <h1 class="agency-logo-text agency-logo-text">
        <a href="<?php echo site_url(); ?>"><strong>Myth<span class="c-gold">Guard</span></strong></a>
      </h1>
      <p><a class="site-footer__link" href="#">555.555.5555</a></p>
    </div>

    <div class="site-footer__col-two">
      <h3 class="headline headline--small">Explore</h3>
      <nav class="nav-list">
        <ul>
          <li><a href="<?php echo site_url('/about-us'); ?>">About Us</a></li>
          <li><a href="<?php echo site_url('/programs'); ?>">Programs</a></li>

          <li><a href="<?php echo site_url('/locations'); ?>">Locations</a></li>
          <li><a href="<?php echo site_url('/guardians'); ?>">Guardians</a></li>
        </ul>
      </nav>
    </div>

    <div class="site-footer__col-three" id="footer-col-three">
      <h3 class="headline headline--small">Learn</h3>
      <nav class="nav-list">
        <ul>
          <li><a href="<?php echo get_post_type_archive_link('event'); ?>">Events</a></li>
          <li><a href="<?php echo site_url('/blog'); ?>">Chronicles</a></li>
        </ul>
      </nav>
    </div>


    <div class="site-footer__col-four">
      <h3 class="headline headline--small">Connect With Us</h3>
      <nav>
        <ul class="min-list social-icons-list group">
          <li>
            <a href="#" class="social-color-facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
          </li>
          <li>
            <a href="#" class="social-color-twitter"><i class="fa-brands fa-twitter" aria-hidden="true"></i></a>
          </li>
          <li>
            <a href="#" class="social-color-youtube"><i class="fa-brands fa-youtube" aria-hidden="true"></i></a>
          </li>
          <li>
            <a href="#" class="social-color-linkedin"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></a>
          </li>
          <li>
            <a href="#" class="social-color-instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</footer>



<?php wp_footer(); ?>
</body>

</html>