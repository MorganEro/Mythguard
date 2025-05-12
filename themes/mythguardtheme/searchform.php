<form class="search-form" method="get" action="<?php echo esc_url(home_url('/')) ?>">
    <label for="s" class="headline headline--medium">Start your search</label>
    <div class="search-form-row">
        <input class="s" type="search" name="s" id="s" placeholder="What do you seek?" required minlength="2">
        <button class="search-submit" type="submit">Search</button>
    </div>
</form>