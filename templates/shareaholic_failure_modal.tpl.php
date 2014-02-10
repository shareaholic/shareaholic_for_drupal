<div class='reveal-modal blocking-modal api-key-modal' id='failed_to_create_api_key'>
  <h4><?php print t('Setup Shareaholic') ?></h4>
  <div class="content pal">
  <div class="line pvl">
    <div class="unit size3of3">
      <p>
        <?php print t('It appears that we are having some trouble setting up Shareaholic for WordPress right now. This is usually temporary. Please revisit this section after a few minutes or click "retry" now.'); ?>
      </p>
    </div>
  </div>
  <div class="pvl">
    <?php print $variables['shareaholic_failure_modal']['hidden'] ?>
    <?php print $variables['shareaholic_failure_modal']['submit'] ?>
    <br /><br />
    <a href='/admin' style="font-size:12px; font-weight:normal;"><?php print t('or, try again later'); ?></a>
  </div>
  </div>
</div>