<?php

require_once __DIR__ . '/helpers.php';

Kirby::plugin('nomad/kirby-comments', [

  // Defaults. Override any of these in site/config/config.php
  // under the key `nomad.kirby-comments`.
  'options' => [
    // Email address, a comma-separated list, or an array of addresses.
    // false sends nothing.
    'notify' => false,

    // Sender address. Falls back to noreply@ plus the site host.
    'from' => null,

    // Sender name. Falls back to the site title.
    'fromName' => null,

    // Placeholders: {title} {author} {site} {email}
    'subject' => 'New comment on {title}',

    // false, true, a CSS string, or ['base' => bool, 'css' => string].
    'styles' => false,
  ],

  // Convenience methods for Panel blueprints/snippets
  'pageMethods' => [
    'approvedCommentsCount' => function () {
      return $this->comments()
        ->toStructure()
        ->filterBy('status', 'approved')
        ->count();
    },

    'pendingCommentsCount' => function () {
      return $this->comments()
        ->toStructure()
        ->filterBy('status', 'pending')
        ->count();
    },
  ],

  'routes' => require __DIR__ . '/routes.php',

  'blueprints' => [
    'fields/comments' => __DIR__ . '/blueprints/fields/comments.yml',
  ],

  'snippets' => [
    'comments'     => __DIR__ . '/snippets/comments.php',
    'comment-meta' => __DIR__ . '/snippets/comment-meta.php',
  ],
]);
