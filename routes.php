<?php

use Kirby\Cms\App;
use Kirby\Data\Yaml;
use Kirby\Toolkit\V;

return [
  [
    'pattern' => '(:all)',
    'method'  => 'POST',
    'action'  => function ($path) {

      if (!get('comment_submit')) return false;
      if (!csrf(get('csrf_token'))) return false;
      if (!empty(get('website'))) return false;

      // Collect data
      $data = [
        'author' => trim(get('author')),
        'email'  => trim(get('email')),
        'text'   => trim(get('text')),
      ];

      // Validate
      $rules = [
        'author' => ['required', 'minLength' => 2],
        'email'  => ['required', 'email'],
        'text'   => ['required', 'minLength' => 5],
      ];

      $messages = [
        'author' => 'Please enter your name (at least 2 characters).',
        'email'  => 'Please enter a valid email address.',
        'text'   => 'Your comment must be at least 5 characters long.',
      ];

      $errors = [];

      foreach ($rules as $field => $fieldRules) {
        foreach ($fieldRules as $rule => $value) {
          if (is_int($rule)) {
            if (!V::$value($data[$field])) {
              $errors[$field] = $messages[$field];
            }
          } else {
            if (!V::$rule($data[$field], $value)) {
              $errors[$field] = $messages[$field];
            }
          }
        }
      }

      // If validation fails → redirect back with errors + old input
      if (!empty($errors)) {
        return go($path . '?' . http_build_query([
          'commentError' => 1,
          'errors' => $errors,
          'old' => $data
        ]));
      }

      $kirby = App::instance();
      $page  = page($path);
      if (!$page) return false;

      // Save comment
      $comment = [
        'author' => esc($data['author']),
        'email'  => esc($data['email']),
        'text'   => esc($data['text']),
        'status' => 'pending',
        'date'   => date('Y-m-d H:i:s'),
      ];

      $comments = $page->comments()->toStructure()->toArray();
      $comments[] = $comment;

      $kirby->impersonate('kirby');

      try {
        $page->update(['comments' => Yaml::encode($comments)]);
      } finally {
        $kirby->impersonate(null);
      }

      nomadCommentsNotify($kirby, $page, $data);

      go($page->url() . '#comments');
    }
  ]
];
