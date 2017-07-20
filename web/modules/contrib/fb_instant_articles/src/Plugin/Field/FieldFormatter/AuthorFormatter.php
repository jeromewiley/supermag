<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;
use Facebook\InstantArticles\Elements\Author;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Plugin implementation of the 'fbia_author' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_author",
 *   label = @Translation("FBIA Author"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class AuthorFormatter extends FormatterBase implements InstantArticleFormatterInterface {

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, $langcode = NULL) {
    foreach ($items as $delta => $item) {
      $author = Author::create()
        ->withName($item->value);
      // Author's are added to the header of an instant article regardless of
      // the given $region.
      $header = $article->getHeader();
      if (!$header) {
        $header = Header::create();
        $article->withHeader($header);
      }
      $header->addAuthor($author);
    }
  }

}
