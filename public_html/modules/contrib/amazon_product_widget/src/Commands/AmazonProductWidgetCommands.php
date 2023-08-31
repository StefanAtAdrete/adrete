<?php

namespace Drupal\amazon_product_widget\Commands;

use Drupal\amazon_product_widget\Batch\UpdateDealsBatch;
use Drupal\amazon_product_widget\BatchProductMapUpdateService;
use Drupal\amazon_product_widget\DealFeedService;
use Drupal\amazon_product_widget\Exception\AmazonApiDisabledException;
use Drupal\amazon_product_widget\Exception\AmazonRequestLimitReachedException;
use Drupal\amazon_product_widget\ProductService;
use Drupal\amazon_product_widget\ProductUsageService;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Drupal\Core\Queue\RequeueException;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drush\Commands\DrushCommands;
use Symfony\Component\Process\Process;

/**
 * Class AmazonProductWidgetCommands.
 *
 * Provides custom drush commands for queueing and updating product
 * information.
 *
 * @package Drupal\amazon_product_widget\Commands
 */
class AmazonProductWidgetCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * ProductService.
   *
   * @var \Drupal\amazon_product_widget\ProductService
   */
  protected $productService;

  /**
   * Queue.
   *
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected $queue;

  /**
   * QueueWorkerManagerInterface.
   *
   * @var \Drupal\Core\Queue\QueueWorkerManagerInterface
   */
  protected $queueWorker;

  /**
   * DealFeedService.
   *
   * @var \Drupal\amazon_product_widget\DealFeedService
   */
  protected $dealFeedService;

  /**
   * ProductUsageService.
   *
   * @var \Drupal\amazon_product_widget\ProductUsageService
   */
  protected $productUsage;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * File system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected FileSystemInterface $fileSystem;

  /**
   * The state.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected StateInterface $state;

  /**
   * AmazonProductWidgetCommands constructor.
   *
   * @param \Drupal\amazon_product_widget\ProductService $productService
   *   ProductService.
   * @param \Drupal\Core\Queue\QueueFactory $queue
   *   QueueFactory.
   * @param \Drupal\Core\Queue\QueueWorkerManagerInterface $queueWorker
   *   QueueWorkerManagerInterface.
   * @param \Drupal\amazon_product_widget\DealFeedService $dealFeedService
   *   Deal feed service.
   * @param \Drupal\amazon_product_widget\ProductUsageService $productUsage
   *   Allows adding or deleting product usages.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   File system interface.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state.
   */
  public function __construct(
    ProductService $productService,
    QueueFactory $queue,
    QueueWorkerManagerInterface $queueWorker,
    DealFeedService $dealFeedService,
    ProductUsageService $productUsage,
    EntityTypeManagerInterface $entityTypeManager,
    FileSystemInterface $fileSystem,
    StateInterface $state
  ) {
    parent::__construct();
    $this->productService = $productService;
    $this->queue = $queue;
    $this->queueWorker = $queueWorker;
    $this->dealFeedService = $dealFeedService;
    $this->productUsage = $productUsage;
    $this->entityTypeManager = $entityTypeManager;
    $this->fileSystem = $fileSystem;
    $this->state = $state;
  }

  /**
   * Queues all products for renewal.
   *
   * @command apw:queue-product-renewal
   */
  public function queueProductRenewal() {
    $asins = amazon_product_widget_get_all_asins();

    if (!empty($asins)) {
      try {
        $this->productService->queueProductRenewal($asins);
        $count = count($asins);
        $this->output()->writeln("$count products have been queued for renewal.");
      }
      catch (\Exception $exception) {
        $this->output()->writeln("An unrecoverable error has occurred:");
        $this->output()->writeln($exception->getMessage());
      }
    }
  }

  /**
   * Updates all product data.
   *
   * @command apw:run-product-renewal
   *
   * @throws \Exception
   */
  public function updateProductData() {
    $queue = $this->queue->get('amazon_product_widget.product_data_update');
    if ($this->productService->getProductStore()->hasStaleData()) {
      $this->productService->queueProductRenewal();

      /** @var \Drupal\amazon_product_widget\Plugin\QueueWorker\ProductDataUpdate $queueWorker */
      $queueWorker = $this->queueWorker
        ->createInstance('amazon_product_widget.product_data_update');
      while ($item = $queue->claimItem()) {
        try {
          $queueWorker->processItem($item->data);
          $queue->deleteItem($item);
        }
        catch (RequeueException $exception) {
          $this->io()->writeln(
            "Update limit reached.
            Run the command again to update more products."
          );
        }
        catch (SuspendQueueException $exception) {
          $queue->releaseItem($item);
          break;
        }
        catch (\Exception $exception) {
          watchdog_exception('amazon_product_widget', $exception);
        }
      }

      if ($this->productService->getProductStore()->hasStaleData()) {
        $outdated = $this->productService->getProductStore()->getOutdatedKeysCount();
        $this->output()->writeln("There are $outdated products still remaining.");
      }
      else {
        $this->output()->writeln("All items have been processed.");
      }
    }
    else {
      $this->output()->writeln("There is nothing to update.");
    }
  }

  /**
   * Gets the number of products due for renewal.
   *
   * @command apw:stale
   */
  public function itemsDueForRenewal() {
    $outdated = $this->productService->getProductStore()->getOutdatedKeysCount();
    $this->output()->writeln("There are $outdated products waiting for renewal.");
  }

  /**
   * Gets overrides for a specific Amazon product.
   *
   * @param string $asin
   *   The ASIN to get the overrides for.
   *
   * @command apw:overrides
   * @usage apw:overrides AE91ECBUDA
   */
  public function getOverridesForProduct($asin) {
    try {
      $productData = $this->productService->getProductData([$asin]);
      if (isset($productData[$asin]['overrides']) && $productData[$asin]['overrides'] !== FALSE) {
        $this->output()->writeln("The following overrides were found for: $asin");
        $this->output()->writeln(var_export($productData[$asin]['overrides'], TRUE));
      }
      else {
        $this->output()->writeln("No overrides for ASIN $asin have been found.");
      }
    }
    catch (\Exception $exception) {
      $this->output()->writeln("An unexpected error has occurred:");
      $this->output()->writeln($exception->getMessage());
    }
  }

  /**
   * Resets all renewal times so all products are stale.
   *
   * @command apw:reset-all-renewals
   */
  public function resetAllRenewals() {
    $this->productService->getProductStore()->resetAll();
    $this->output()->writeln("All products have been marked for renewal.");
  }

  /**
   * Prints the number of active deals.
   *
   * @command apw:deals:active-deals
   */
  public function dealsCount() {
    $count = $this->dealFeedService->getDealStore()->getActiveCount();
    $this->output()->writeln("There are $count active deals in the database.");
  }

  /**
   * Updates the deals in the storage.
   *
   * @param string|null $path
   *   Path to the CSV to be used to update the store, otherwise calls the API.
   *
   * @command apw:deals:update
   */
  public function dealsUpdate(?string $path = NULL) {
    $batch = [
      'title' => $this->t('Updating deals store'),
      'finished' => [UpdateDealsBatch::class, 'finished'],
      'operations' => [],
    ];

    try {
      if ($path) {
        $importPath = $path;
      }
      else {
        $importPath = $this->dealFeedService->downloadDealsCsv();
      }

      if (!file_exists($importPath) || is_dir($importPath)) {
        $this->output()->writeln("Path '$importPath' is either a directory or does not exist.");
        return;
      }

      $this->output()->writeln("File to import: $importPath");
      $this->output()->writeln('Now importing...');

      $entries = file($importPath);
      $totalEntries = count($entries) - 1;
      $start = 0;
      $entriesPerRound = 1000;
      $entryChunks = array_chunk($entries, $entriesPerRound);
      $lastKey = array_key_last($entryChunks);

      foreach ($entryChunks as $key => $chunk) {
        $numEntries = count($chunk);
        if ($key === $lastKey) {
          $numEntries -= 1;
        }

        $batch['operations'][] = [
          [UpdateDealsBatch::class, 'process'],
          [$importPath, $start, $numEntries, $totalEntries],
        ];

        $start += $numEntries;
      }

      $this->dealFeedService->getDealStore()->deleteAll();
      batch_set($batch);
      drush_backend_batch_process();
    }
    catch (\Throwable $exception) {
      $this->output()->writeln("Error occurred while importing deals:");
      $this->output()->writeln($exception->getMessage());
    }
  }

  /**
   * Downloads and imports the deals from the API using INFILE import.
   *
   * @param string|null $drushPath
   *   (optional) Path to the drush executable.
   *
   * @command apw:deals:import-infile
   */
  public function importDealsInfile(?string $drushPath = NULL): void {
    try {
      $csvPath = $this->dealFeedService->downloadDealsCsv();
      $outputPath = $this->getTemporaryFile('processed', 'csv');
      $this->io()->writeln("Downloaded deals CSV to {$csvPath}, now processing...");
      if ($this->dealFeedService->prepareCsvForImport($csvPath, $outputPath)) {
        $this->io()->writeln("Saved processed CSV to '{$outputPath}', now importing...");
        $this->dealFeedService->getDealStore()->deleteAll();
        $statement = $this->dealFeedService->getDealStore()->importInfileStatement($outputPath);
        $temporarySqlFile = $this->getTemporaryFile('apw_sql', 'sql');
        if (!file_put_contents($temporarySqlFile, $statement)) {
          $this->io()->writeln("Could not write SQL to temporary file '{$temporarySqlFile}'.");
          return;
        }

        $drushCommand = $drushPath ?? 'drush';
        $process = Process::fromShellCommandline("$drushCommand sql-cli < $temporarySqlFile");
        $process->run();

        if ($process->getExitCode() === 0) {
          $this->state->set('amazon_product_widget.deal_cron_last_run', time());
          $this->io()->writeln("Successfully imported deals.");
        }
        else {
          $this->io()->writeln("Error occurred while importing deals:");
          $this->io()->writeln($process->getErrorOutput());
        }
      }
    }
    catch (\Throwable $exception) {
      $this->output()->writeln("An unexpected error has occurred:");
      $this->output()->writeln($exception->getMessage());
    }
  }

  /**
   * Gets the temporary file path.
   *
   * @param string $prefix
   *   Prefix to use in the filename.
   * @param string $extension
   *   Extension to use in the filename.
   *
   * @return string
   *   The path to the file.
   */
  protected function getTemporaryFile(string $prefix, string $extension): string {
    $temporaryDirectory = $this->fileSystem->getTempDirectory();
    $filename = $prefix . '_' . substr(uniqid(), 0, 8) . '.' . ltrim($extension, '.');
    return $temporaryDirectory . '/' . $filename;
  }

  /**
   * Gets deal information for an ASIN.
   *
   * @param string $asin
   *   Amazon Standard Identification Number.
   *
   * @command apw:deals:info
   */
  public function dealInfo(string $asin) {
    $deal = $this->dealFeedService->getDealStore()->getByAsin($asin);
    $deal = $this->dealFeedService->getDealStore()->prettifyDeal($deal);
    $this->output()->writeln("Deal information for $asin:");
    $this->output()->writeln(var_export($deal, TRUE));
  }

  /**
   * Gets product information for the given ASIN.
   *
   * @param string $asin
   *   Amazon Standard Identification Number.
   * @param array $options
   *   (optional) Force an update and get the product data directly from Amazon.
   *
   * @command apw:product-info
   */
  public function getProductInfo(string $asin, array $options = ['renew' => FALSE]) {
    try {
      $productData = $this->productService->getProductData([$asin], $options['renew']);
      if ($productData[$asin] === FALSE) {
        $this->io()->writeln("No product information could be retrieved for ASIN $asin.");
      }
      else {
        $this->io()->writeln("Got the following product information for ASIN $asin:");
        $this->io()->writeln(var_export($productData[$asin], TRUE));
      }
    }
    catch (AmazonApiDisabledException $exception) {
      $this->io()->error("The Amazon API is disabled by configuration.");
    }
    catch (AmazonRequestLimitReachedException $exception) {
      $this->io()->error("You have reached the Amazon API request limit.");
    }
  }

  /**
   * Updates the Node - ASIN map.
   *
   * @command apw:update-asin-map
   */
  public function updateAsinMap() {
    $batch = [
      'title'        => $this->t('Updating Product Node mapping'),
      'init_message' => $this->t('Preparing to sync @count nodes...'),
      'finished'     => [BatchProductMapUpdateService::class, 'finished'],
    ];

    try {
      $nodeIds = $this->entityTypeManager->getStorage('node')
        ->getQuery()
        ->accessCheck(FALSE)
        ->execute();
      $totalNodes = count($nodeIds);
      $nodeIdsChunked = array_chunk($nodeIds, 20);
      foreach ($nodeIdsChunked as $chunk) {
        $batch['operations'][] = [
          [BatchProductMapUpdateService::class, 'update'], [
            $chunk,
            $totalNodes,
          ],
        ];
      }

      batch_set($batch);
      $batch = &batch_get();
      $batch['progressive'] = FALSE;

      drush_backend_batch_process();
    }
    catch (\Exception $exception) {
      watchdog_exception('amazon_product_widget', $exception);
    }
  }

  /**
   * Gets products in the given entity.
   *
   * @param string $entityId
   *   The ID of the entity.
   * @param string $entityType
   *   (optional) The entity type. Defaults to 'node'.
   *
   * @command apw:entity-products
   */
  public function getProductsForEntity(string $entityId, string $entityType = 'node') {
    $storage = NULL;
    try {
      $storage = $this->entityTypeManager->getStorage($entityType);
    }
    catch (\Exception $exception) {
      $this->io()->error("No storage class found for '$entityType'.");
      return;
    }

    $entity = $storage->load($entityId);
    if (!$entity) {
      $this->io()->error("Could not load entity with ID: $entityId");
      return;
    }

    $asins = $this->productUsage->getAsinsByEntity($entity);
    if (empty($asins)) {
      $this->io()->writeln("No products found in this entity.");
    }
    else {
      $this->io()->writeln("The following products were found:");
      foreach ($asins as $asin) {
        $this->io()->writeln("- $asin");
      }
    }
  }

  /**
   * Returns entity IDs and types that contain the given product.
   *
   * @param string $asin
   *   The ASIN of the product.
   *
   * @command apw:product-entities
   */
  public function getEntitiesForProduct(string $asin) {
    $entities = $this->productUsage->getEntitiesByAsin($asin);
    $header = ['entity_type', 'entity_id'];
    $rows = [];
    foreach ($entities as $type => $id) {
      $rows[] = [$type, $id];
    }
    $this->io()->table($header, $rows);
  }

}
