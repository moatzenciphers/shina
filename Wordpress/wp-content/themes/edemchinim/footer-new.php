</div>
<?php
use Edemchinim\Calculator\Config;

/** Shared footer for every page. */
if (! Config::isPage()) get_template_part('template-parts/layout/footer');
?>
</div>
<?php 
if (! Config::isPage()) get_template_part('template-parts/forms/quick-call');
wp_footer();
?>
</body>
</html>
