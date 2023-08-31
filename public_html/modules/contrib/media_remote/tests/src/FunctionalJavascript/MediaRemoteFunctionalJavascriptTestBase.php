<?php

namespace Drupal\Tests\media_remote\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * Base class for Media Remote Functional Javascript tests.
 *
 * @group media_remote
 */
abstract class MediaRemoteFunctionalJavascriptTestBase extends WebDriverTestBase {

  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'field',
    'field_ui',
    'node',
    'system',
    'media',
    'media_library',
    'media_remote',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * An admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * The media type under test.
   *
   * @var \Drupal\media\MediaTypeInterface
   */
  protected $mediaType;

  /**
   * The node type that will point to the media type under test.
   *
   * @var \Drupal\node\NodeTypeInterface
   */
  protected $nodeType;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Place some blocks to make our lives easier down the road.
    $this->drupalPlaceBlock('system_breadcrumb_block');
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('page_title_block');

    // Create a media type using our source.
    // Tests extending this are expected to configure the formatter of this
    // type in the _default_ viewmode and set the formatter under test.
    $this->mediaType = $this->createMediaType('media_remote', [
      'id' => 'media_remote_type',
      'label' => 'Media Remote Type',
    ]);
    $this->mediaType->save();

    // Create a node type and field referencing our media type.
    $this->nodeType = $this->drupalCreateContentType([
      'type' => 'node_type',
      'name' => 'Node Type',
    ]);
    $storage = FieldStorageConfig::create([
      'field_name' => 'field_node__remote_media',
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'media',
      ],
    ]);
    $storage->save();
    FieldConfig::create([
      'bundle' => $this->nodeType->id(),
      'entity_type' => 'node',
      'field_name' => 'field_node__remote_media',
      'label' => 'Remote media',
      'settings' => [
        'handler' => 'default:media',
        'handler_settings' => [
          'target_bundles' => [
            $this->mediaType->id() => $this->mediaType->id(),
          ],
          'auto_create' => FALSE,
        ],
      ],
    ])->save();
    // Use the Media Library for this widget.
    \Drupal::service('entity_display.repository')
      ->getFormDisplay('node', $this->nodeType->id(), 'default')
      ->setComponent('field_node__remote_media', [
        'type' => 'media_library_widget',
      ])
      ->save();
    // Show this field as rendered entity.
    \Drupal::service('entity_display.repository')
      ->getViewDisplay('node', $this->nodeType->id(), 'default')
      ->setComponent('field_node__remote_media', [
        'type' => 'entity_reference_entity_view',
      ])
      ->save();

    // Log in as admin.
    $this->adminUser = $this->drupalCreateUser(array(
      'access content',
      'view media',
      'create media',
      'administer media',
      'administer nodes',
      'bypass node access',
      'administer node display',
      'administer media display',
    ));
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Clicks "Save" button and waits for AJAX completion.
   *
   * @param bool $expect_errors
   *   Whether validation errors are expected after the "Save" button is
   *   pressed. Defaults to FALSE.
   */
  protected function pressSaveButton($expect_errors = FALSE) {
    $this->getSession()->wait(200);
    $buttons = $this->assertElementExistsAfterWait('css', '.ui-dialog-buttonpane');
    $buttons->pressButton('Save');

    // If no validation errors are expected, wait for the "Insert selected"
    // button to return.
    if (!$expect_errors) {
      $result = $buttons->waitFor(10, function ($buttons) {
        /** @var \Behat\Mink\Element\NodeElement $buttons */
        return $buttons->findButton('Insert selected');
      });
      $this->assertNotEmpty($result);
    }

    // assertWaitOnAjaxRequest() required for input "id" attributes to
    // consistently match their label's "for" attribute.
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Clicks a button that opens a media widget and confirms it is open.
   *
   * @param string $field_name
   *   The machine name of the field for which to open the media library.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The NodeElement found via $after_open_selector.
   */
  protected function openMediaLibraryForField($field_name) {
    $this->assertElementExistsAfterWait('css', "#$field_name-media-library-wrapper.js-media-library-widget")
      ->pressButton('Add media');
    $this->waitForText('Add or select media');
  }

  /**
   * Presses the modal's "Insert selected" button.
   *
   * @param string $expected_announcement
   *   (optional) The expected screen reader announcement once the modal is
   *   closed.
   */
  protected function pressInsertSelected($expected_announcement = NULL) {
    $this->assertSession()
      ->elementExists('css', '.ui-dialog-buttonpane')
      ->pressButton('Insert selected');
    $this->waitForNoText('Add or select media');

    if ($expected_announcement) {
      $this->waitForText($expected_announcement);
    }
    $this->getSession()->wait(300);
  }

  /**
   * Asserts that text appears on page after a wait.
   *
   * @param string $text
   *   The text that should appear on the page.
   * @param int $timeout
   *   Timeout in milliseconds, defaults to 10000.
   */
  protected function waitForText($text, $timeout = 10000) {
    $result = $this->assertSession()->waitForText($text, $timeout);
    $this->assertNotEmpty($result, "\"$text\" not found");
  }

  /**
   * Asserts that text does not appear on page after a wait.
   *
   * @param string $text
   *   The text that should not be on the page.
   * @param int $timeout
   *   Timeout in milliseconds, defaults to 10000.
   */
  protected function waitForNoText($text, $timeout = 10000) {
    $page = $this->getSession()->getPage();
    $result = $page->waitFor($timeout / 1000, function ($page) use ($text) {
      $actual = preg_replace('/\s+/u', ' ', $page->getText());
      $regex = '/' . preg_quote($text, '/') . '/ui';
      return (bool) !preg_match($regex, $actual);
    });
    $this->assertNotEmpty($result, "\"$text\" was found but shouldn't be there.");
  }

  /**
   * Waits for the specified selector and returns it if not empty.
   *
   * @param string $selector
   *   The selector engine name. See ElementInterface::findAll() for the
   *   supported selectors.
   * @param string|array $locator
   *   The selector locator.
   * @param int $timeout
   *   Timeout in milliseconds, defaults to 10000.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The page element node if found. If not found, the test fails.
   */
  protected function assertElementExistsAfterWait($selector, $locator, $timeout = 10000) {
    $element = $this->assertSession()->waitForElement($selector, $locator, $timeout);
    $this->assertNotEmpty($element);
    return $element;
  }

  /**
   * Debugger method to save additional HTML output.
   *
   * The base class will only save browser output when accessing page using
   * ::drupalGet and providing a printer class to PHPUnit. This method
   * is intended for developers to help debug browser test failures and capture
   * more verbose output.
   */
  protected function saveHtmlOutput() {
    $out = $this->getSession()->getPage()->getContent();
    // Ensure that any changes to variables in the other thread are picked up.
    $this->refreshVariables();
    if ($this->htmlOutputEnabled) {
      $html_output = '<hr />Ending URL: ' . $this->getSession()->getCurrentUrl();
      $html_output .= '<hr />' . $out;
      $html_output .= $this->getHtmlOutputHeaders();
      $this->htmlOutput($html_output);
    }
  }

}

