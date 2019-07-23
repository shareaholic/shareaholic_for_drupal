<?php

namespace Drupal\shareaholic;

/**
 * Interface NoticeServiceInterface.
 */
interface NoticeServiceInterface {

  /**
   * Injects markup for notice on admin pages
   *
   * @return mixed
   */
  public function notice();

}
