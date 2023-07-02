<?php

declare(strict_types=1);

namespace Drupal\books\Plugin\rest\resource;

use Drupal\books\DTO\Model\Book;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\node\NodeStorageInterface;
use Drupal\rest\ResourceResponseInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the book list resource.
 *
 * @RestResource(
 *   id = "book_list",
 *   label = @Translation("All books list"),
 *   uri_paths = {
 *     "canonical" = "/api/book/all",
 *   }
 * )
 */
class BookListResource extends ResourceBase {

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected NodeStorageInterface $nodeStorage;

  /**
   * Creates a book list resource instance.
   *
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param mixed $parent_parameters
   *   The parent class parameters.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ...$parent_parameters,
  ) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    parent::__construct(...$parent_parameters);
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity_type.manager'),
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
    );
  }

  /**
   * Responds to book list GET request.
   *
   * @return \Drupal\rest\ResourceResponseInterface
   *   Book data.
   */
  public function get(): ResourceResponseInterface {
    $book_list = [];
    $books = $this->nodeStorage->loadByProperties(['type' => 'book']);

    foreach ($books as $book) {
      $response = new Book($book);
      $book_list[] = $response->serialize();
    }

    return new ResourceResponse($book_list);
  }

}
