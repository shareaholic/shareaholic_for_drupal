<?php

namespace Drupal\shareaholic\Helper;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Class DiagnosticsProvider
 */
class DiagnosticsProvider {

  /** @var ModuleHandlerInterface */
  private $moduleHandler;

  /** @var ThemeManagerInterface */
  private $themeManager;

  public function __construct(ModuleHandlerInterface $moduleHandler, ThemeManagerInterface $themeManager) {
    $this->moduleHandler = $moduleHandler;
    $this->themeManager = $themeManager;
  }

  /**
   * Returns true if Drupal instance is configured for multiple websites.
   *
   * @return bool
   */
  public function isMultisite(): bool {
    // That way we can check if Drupal is configured for Multisite.
    $sitesFile = DRUPAL_ROOT . '/sites/sites.php';
    $multisite = FALSE;
    if (is_file($sitesFile)) {
      include $sitesFile;
      $multisite = !empty($sites);
    }

    return $multisite;
  }

  /**
   * Returns list of active plugins.
   *
   * @return array
   */
  public function getActivePlugins(): array {
    return array_keys($this->moduleHandler->getModuleList());
  }

  /**
   * Returns name of the active theme.
   *
   * @return string
   */
  public function getActiveTheme(): string {
    return $this->themeManager->getActiveTheme()->getName();
  }


}
