<?php

namespace Drupal\Tests\media_remote\FunctionalJavascript\Formatters;

use Drupal\Tests\media_remote\FunctionalJavascript\MediaRemoteFunctionalJavascriptTestBase;

/**
 * Tests the Buzzsprout formatter.
 *
 * @group media_remote
 */
class BuzzsproutFormatterTest extends MediaRemoteFunctionalJavascriptTestBase {

  /**
   * Tests the Buzzsprout formatter.
   */
  public function testBuzzsprout() {
    $assert_session = $this->assertSession();
    $session = $this->getSession();
    $page = $session->getPage();

    // Set the formatter on the media source field.
    $source_field_name = $this->mediaType->getSource()->getSourceFieldDefinition($this->mediaType)->getName();
    \Drupal::service('entity_display.repository')
      ->getViewDisplay('media', $this->mediaType->id(), 'default')
      ->setComponent($source_field_name, [
        'type' => 'media_remote_buzzsprout',
      ])
      ->save();

    $node = $this->drupalCreateNode([
      'type' => $this->nodeType->id(),
      'title' => 'Node with a Buzzsprout media',
    ]);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->openMediaLibraryForField('field_node__remote_media');
    // Enter an invalid URL and check the error message.
    $assert_session->elementExists('css', '.media-library-widget-modal input[name="url"]')
      ->setValue('https://drupal.org');
    $assert_session->elementExists('css', '.media-library-widget-modal input[value="Add"]')
      ->press();
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains("The given URL is not valid. Valid values are in the format: https://www.buzzsprout.com/123/456 or https://www.buzzsprout.com/123/456-foo-bar");
    // Enter a valid URL and save the media item.
    $assert_session->elementExists('css', '.media-library-widget-modal input[name="url"]')
      ->setValue('https://www.buzzsprout.com/123/456');
    $assert_session->elementExists('css', '.media-library-widget-modal input[value="Add"]')
      ->press();
    $name_input = $assert_session->waitForElement('css', '.media-library-widget-modal input[name="media[0][fields][name][0][value]"]');
    $this->assertNotNull($name_input);
    $assert_session->pageTextNotContains("The given URL is not valid");
    $this->assertSame('Buzzsprout episode 456 (Podcast ID: 123)', $name_input->getValue());
    // Save the modal and the node form.
    $this->pressSaveButton();
    $this->pressInsertSelected();
    $name_in_widget = $assert_session->elementExists('css', '.field--name-field-node__remote-media .media-library-item__name');
    $this->assertSame('Buzzsprout episode 456 (Podcast ID: 123)', $name_in_widget->getText());
    $page->pressButton('Save');
    $assert_session->pageTextContains("{$this->nodeType->label()} Node with a Buzzsprout media has been updated");
    $assert_session->elementExists('css', 'article.node--type-node-type #buzzsprout-player-456');

    // Test also the default name metadata when there is suffix in the URL.
    $node = $this->drupalCreateNode([
      'type' => $this->nodeType->id(),
      'title' => 'Node with a Buzzsprout media that has name suffix in the URL',
    ]);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->openMediaLibraryForField('field_node__remote_media');
    // Enter an URL with name suffix.
    $assert_session->elementExists('css', '.media-library-widget-modal input[name="url"]')
      ->setValue('https://www.buzzsprout.com/1234/5678-foo-bar-baz');
    $assert_session->elementExists('css', '.media-library-widget-modal input[value="Add"]')
      ->press();
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->pageTextNotContains("The given URL is not valid");
    $name_input = $assert_session->waitForElement('css', '.media-library-widget-modal input[name="media[0][fields][name][0][value]"]');
    $this->assertNotNull($name_input);
    $this->assertSame('Foo bar baz', $name_input->getValue());
    // Save the modal and the node form.
    $this->pressSaveButton();
    $this->pressInsertSelected();
    $name_in_widget = $assert_session->elementExists('css', '.field--name-field-node__remote-media .media-library-item__name');
    $this->assertSame('Foo bar baz', $name_in_widget->getText());
    $page->pressButton('Save');
    $assert_session->pageTextContains("{$this->nodeType->label()} Node with a Buzzsprout media that has name suffix in the URL has been updated");
    $assert_session->elementExists('css', 'article.node--type-node-type #buzzsprout-player-5678');
  }

}

