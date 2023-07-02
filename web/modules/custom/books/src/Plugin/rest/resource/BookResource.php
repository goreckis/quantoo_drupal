<?php

declare(strict_types=1);

namespace Drupal\books\Plugin\rest\resource;

use Drupal\books\DTO\Model\Book;
use Drupal\Component\Serialization\Json;
use Drupal\node\NodeInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\rest\ResourceResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

/**
 * Provides the book resource.
 *
 * @RestResource(
 *   id = "book",
 *   label = @Translation("Book resource"),
 *   uri_paths = {
 *     "canonical" = "/api/book/{book}",
 *     "create" = "/api/book/add",
 *   }
 * )
 */
class BookResource extends ResourceBase {

  /**
   * Responds to book GET request.
   *
   * @param \Drupal\node\NodeInterface $book
   *   The book node.
   *
   * @return \Drupal\rest\ResourceResponseInterface
   *   Book data.
   */
  public function get(NodeInterface $book): ResourceResponseInterface {
    $response = new Book($book);

    return new ResourceResponse($response->serialize());
  }

  /**
   * Responds to book DELETE request.
   *
   * @param \Drupal\node\NodeInterface $book
   *   The book node.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The empty response with 204 satus code if success.
   */
  public function delete(NodeInterface $book): ModifiedResourceResponse {
    $book->delete();

    return new ModifiedResourceResponse(Response::HTTP_NO_CONTENT);
  }

  /**
   * Responds to book PATCH request.
   *
   * @param \Drupal\node\NodeInterface $book
   *   The book node.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The book data.
   */
  public function patch(NodeInterface $book): ModifiedResourceResponse {
    $data = Json::decode(\Drupal::request()->getContent());
    $book = new Book($book);

    $book->save($data);

    return new ModifiedResourceResponse($book->serialize(), Response::HTTP_CREATED);
  }

  /**
   * Responds to book POST request.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The created book data.
   */
  public function post(): ModifiedResourceResponse {
    $data = Json::decode(\Drupal::request()->getContent());
    $book = new Book();
    $book->create($data);

    return new ModifiedResourceResponse($book->serialize());
  }

  /**
   * {@inheritdoc}
   */
  protected function getBaseRoute($cannonical_path, $method): Route {
    $route = parent::getBaseRoute($cannonical_path, $method);

    $route->setDefault('node', 0);
    $route->setOption('parameters', [
      'book' => [
        'type' => 'entity:node',
        'bundle' => ['book'],
      ],
    ]);

    return $route;
  }

  /**
   * {@inheritdoc}
   */
  protected function getBaseRouteRequirements($method) {
    $requirements = parent::getBaseRouteRequirements($method);
    $requirements['_content_type_format'] = 'json';

    return $requirements;
  }

}
