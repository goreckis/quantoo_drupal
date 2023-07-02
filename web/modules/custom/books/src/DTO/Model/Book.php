<?php

declare(strict_types=1);

namespace Drupal\books\DTO\Model;

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * The book model model.
 */
class Book {

  /**
   * The book entity.
   *
   * @var Drupal\node\NodeInterface|null
   */
  protected ?NodeInterface $book;

  /**
   * Book id.
   *
   * @var int|null
   */
  protected ?int $id;

  /**
   * Book title.
   *
   * @var string|null
   */
  public ?string $title;

  /**
   * Number of pages.
   *
   * @var int|null
   */
  public ?int $pages;

  /**
   * Publisher name.
   *
   * @var string|null
   */
  public ?string $publisher;

  /**
   * Available status.
   *
   * @var bool|null
   */
  public ?bool $available;

  /**
   * List of book authors.
   *
   * @var array|null
   */
  public ?array $author;

  /**
   * Creates book instance.
   *
   * @param \Drupal\node\NodeInterface $book
   *   The book node.
   */
  public function __construct(?NodeInterface $book = NULL) {
    if ($book instanceof NodeInterface) {
      $this->book = $book;
      $this->id = (int) $book->id();
      $this->title = $book->getTitle();
      $this->pages = (int) $book->get('field_pages')->value;
      $this->publisher = $book->get('field_publisher')->value;
      $this->available = (bool) $book->get('field_available')->value;
      $this->author = [];
    }
  }

  /**
   * Serialize object into API structure.
   *
   * @return array
   *   Serialized book.
   */
  public function serialize(): array {
    $serialized = [];
    foreach ($this->getProperties() as $property) {
      $serialized[$property] = $this->$property;
    }

    return $serialized;
  }

  /**
   * Deserialize data into API model.
   *
   * @param array $data
   *   Array to serialize.
   */
  protected function deserialize(array $data): void {
    foreach ($this->getProperties() as $property) {
      $this->$property = $data[$property] ?? NULL;
    }
  }

  /**
   * Get list of the model properties.
   *
   * @return array
   *   Properties list.
   */
  protected function getProperties(): array {
    $properties = [];
    $reflection = new \ReflectionClass(static::class);
    foreach ($reflection->getProperties() as $property) {
      if (!$property->isPublic()) {
        continue;
      }
      $properties[] = $property->name;
    }

    return $properties;
  }

  /**
   * Saves a changes to a node.
   */
  public function save(array $data): void {
    $this->deserialize($data);

    if (!($this->book instanceof NodeInterface && $this->book->getType() === 'book')) {
      return;
    }

    $this->book->set('title', $this->title)
      ->set('field_available', $this->available)
      ->set('field_pages', $this->pages)
      ->set('field_publisher', $this->publisher)
      ->save();

    // @todo implement author update.
    $this->book->save();
  }

  /**
   * Creates a new node from the data.
   *
   * @return \Drupal\node\NodeInterface
   *   The book node.
   */
  public function create(array $data): self {
    $this->deserialize($data);

    $node = Node::create([
      'type' => 'book',
      'title' => $this->title,
      'field_available' => $this->available,
      'field_pages' => $this->pages,
      'field_publisher' => $this->publisher,
    ]);

    foreach ($this->author as $author) {
      $author = Node::load($author['id']);
      if ($author instanceof NodeInterface && $author->getType() === 'author') {
        $node->set('field_author')->$author;
      }
    }

    $node->save();

    return new self($node);
  }

}
