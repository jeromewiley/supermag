<?php

namespace Drupal\fb_instant_articles\Normalizer;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;
use Drupal\fb_instant_articles\Transformer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Normalize FieldItemList object into an Instant Article object.
 */
class FieldItemListNormalizer extends SerializerAwareNormalizer implements NormalizerInterface {

  const FORMAT = ['fbia', 'fbia_rss'];

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * FBIA SDK transformer object.
   *
   * @var \Drupal\fb_instant_articles\Transformer
   */
  protected $transformer;

  /**
   * FieldItemListNormalizer constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\fb_instant_articles\Transformer $transformer
   *   FBIA SDK transformer object.
   */
  public function __construct(RendererInterface $renderer, Transformer $transformer) {
    $this->renderer = $renderer;
    $this->transformer = $transformer;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    // Only consider this normalizer if we are trying to normalize a field item
    // list into the 'fbia' format.
    return in_array($format, static::FORMAT) && $data instanceof FieldItemListInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    /** @var \Drupal\Core\Field\FieldItemListInterface $object */
    if (!isset($context['instant_article'])) {
      return;
    }
    /** @var \Facebook\InstantArticles\Elements\InstantArticle $article */
    $article = $context['instant_article'];

    // If we're given an entity_view_display object as context, use that as a
    // mapping to guide the normalization.
    if (isset($context['entity_view_display'])) {
      /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay $display */
      $display = $context['entity_view_display'];
      $formatter = $display->getRenderer($object->getName());
      if ($formatter instanceof InstantArticleFormatterInterface) {
        $component = $display->getComponent($object->getName());
        $formatter->viewInstantArticle($object, $article, $component['region']);
      }
      elseif ($formatter instanceof FormatterInterface) {
        $formatter->prepareView([$object->getEntity()->id() => $object]);
        $render_array = $formatter->view($object);
        if ($markup = (string) $this->renderer->renderPlain($render_array)) {

          // Pass the markup through the Transformer.
          $document = new \DOMDocument();
          // Before loading into DOMDocument, setup for success by taking care
          // of encoding issues.  Since we're dealing with HTML snippets, it
          // will always be missing a <meta charset="utf-8" /> or equivalent.
          $markup = '<!doctype html><html><head><meta charset="utf-8" /></head><body>' . $markup . '</body></html>';
          @$document->loadHTML(Html::decodeEntities($markup));

          // Note that we are putting the content of these fields into the body.
          $this->transformer->transform($article, $document);
        }
      }
    }
  }

}
