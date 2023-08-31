<?php

namespace Drupal\amazon_product_widget\Batch;

/**
 * Updates deals.
 */
class UpdateDealsBatch {

  /**
   * Batch process callback.
   *
   * @param string $path
   *   The path to the deals CSV.
   * @param int $start
   *   Line number to start from in the CSV.
   * @param int $limit
   *   How many lines to import in the CSV.
   * @param int $total
   *   Total number of lines in the CSV.
   * @param \DrushBatchContext $context
   *   Batch context.
   */
  public static function process(string $path, int $start, int $limit, int $total, \DrushBatchContext $context): void {
    /** @var \Drupal\amazon_product_widget\DealFeedService $dealFeedService */
    $dealFeedService = \Drupal::service('amazon_product_widget.deal_feed_service');
    if (!isset($context['results']['processed'])) {
      $context['results']['processed'] = 0;
      $context['results']['errors'] = 0;
    }

    $state  = $dealFeedService->import($path, $start, $limit);
    $errors = $state->errors;

    $progress = round($start / $total * 100, 2);
    $context['message'] = t("Processed @start / @total (@progress%) with @errors errors.", [
      '@start' => $start + $state->processed,
      '@total' => $total,
      '@progress' => $progress,
      '@errors' => $errors,
    ]);

    $context['results']['processed'] += $state->processed;
    $context['results']['errors'] += $errors;
  }

  /**
   * Batch finished callback.
   *
   * @param bool $success
   *   Whether the batch was successful.
   * @param array $results
   *   The results of the batch.
   * @param array $operations
   *   The operations of the batch.
   */
  public static function finished(bool $success, array $results, array $operations): void {
    \Drupal::messenger()->addMessage(t('Done importing deals, processed @count deals with @errors errors.', [
      '@count' => $results['processed'],
      '@errors' => $results['errors'],
    ]));
  }

}
