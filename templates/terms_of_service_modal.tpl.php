<div class='reveal-modal blocking-modal' id='terms_of_service_modal'>
  <h4><?= sprintf(t('Thanks for Installing!')); ?></h4>
  <div class="content pal">
  <p><?= sprintf(t('%sShareaholic%s gives you the essential tools you need to become a successful publisher.'), '<strong>', '</strong>'); ?></p>
  <div class="line pvl">
  <div class="unit size1of3">
    <img src="<?= $variables['image_url'] ?>/sharebuttons@2x.png" alt="Share Buttons" width="200" height="200" />
    <p><?= sprintf(t('%sShare buttons%s enables your readers to spread your content.'), '<strong>', '</strong>'); ?></p>
  </div>
  <div class="unit size1of3">
    <img src="<?= $variables['image_url'] ?>/related_content@2x.png" alt="Related Content" width="200" height="200" />
    <p><?= sprintf(t('%sRelated content%s keeps people on your site and turns visitors into readers.'), '<strong>', '</strong>'); ?></p>
  </div>
  <div class="unit size1of3">
    <img src="<?= $variables['image_url'] ?>/analytics@2x.png" alt="Analytics" width="200" height="200" />
    <p><?= sprintf(t('%sAnalytics%s gives you all the insights you need to grow your site.'), '<strong>', '</strong>'); ?></p>
  </div>
  </div>
  <div class="pvl">
    <?= $variables['shareaholic_tos_modal']['hidden'] ?>
    <?= $variables['shareaholic_tos_modal']['submit'] ?>
    <p><small><?= sprintf(t('By clicking "Get Started" you agree to Shareholic\'s %sTerms of Service%s and %sPrivacy Policy%s.'), '<a href="https://shareaholic.com/terms/?src=wp_admin" target="_new">', '</a>', '<a href="https://shareaholic.com/privacy/?src=wp_admin" target="_new">', '</a>'); ?></p>
  </div>
  </div>
</div>