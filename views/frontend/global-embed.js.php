<?php // Check that the WP Admin user specified a Site ID, otherwise log a console error.
if (empty($data['site_id'])) : ?>
<script type="text/javascript">console.error(
  'No Sitka Insights Site ID detected! Please specify one at <?= WP_SITEURL ?>/wp-admin/options-general.php?page=sitka-insights'
);</script>
<?php else : ?>
<!-- Sitka Insights global embed -->
<script type="text/javascript">
  (function (g, e, a, r, l, b) {
    g.gl = g.gl || function () { (g.gl.q = g.gl.q || []).push(arguments) };
    g._glSettings = { id: b };
    r = e.getElementsByTagName('head')[0];
    l = e.createElement('script'); l.async = 1;
    l.src = a + g._glSettings.id;
    r.appendChild(l);
  })(window, document, "<?= $data['feedback_uri'] . '?id=' ?>", null, null, <?= $data['site_id'] ?>);
</script>
<?php endif; ?>