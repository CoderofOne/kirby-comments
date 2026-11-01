<?php

use Kirby\Cms\App;
use Kirby\Cms\Page;

/**
 * Email addresses from the `notify` option.
 * Accepts one address, a comma-separated string, or a list.
 */
function nomadCommentsRecipients(mixed $notify): array
{
  $items = [];

  if (is_string($notify)) {
    $items = preg_split('/\s*,\s*/', $notify) ?: [];
  } elseif (is_array($notify)) {
    foreach ($notify as $item) {
      if (is_string($item)) {
        $items[] = $item;
      }
    }
  }

  $emails = [];

  foreach ($items as $item) {
    $item = trim($item);

    if ($item !== '' && filter_var($item, FILTER_VALIDATE_EMAIL)) {
      $emails[$item] = $item;
    }
  }

  return array_values($emails);
}

/**
 * True when the notify option is trying to turn mail on.
 * `false`, null, and an empty string or list leave mail off.
 */
function nomadCommentsNotifyEnabled(mixed $notify): bool
{
  return $notify !== false && $notify !== null && $notify !== '' && $notify !== [];
}

function nomadCommentsOneLine(string $value): string
{
  $value = preg_replace('/\s+/u', ' ', $value) ?? '';

  return trim($value);
}

/**
 * Subject line. Placeholders: {title} {author} {site} {email}
 */
function nomadCommentsSubject(mixed $subject, array $vars): string
{
  $template = is_string($subject) && trim($subject) !== ''
    ? $subject
    : 'New comment on {title}';

  $replace = [];

  foreach ($vars as $key => $value) {
    $replace['{' . $key . '}'] = nomadCommentsOneLine((string) $value);
  }

  $subject = nomadCommentsOneLine(strtr($template, $replace));

  return $subject !== '' ? $subject : 'New comment';
}

function nomadCommentsEscape(string $value): string
{
  return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Plain-text and HTML bodies for a new pending comment.
 */
function nomadCommentsMessage(
  string $title,
  string $url,
  string $panelUrl,
  string $author,
  string $email,
  string $comment
): array {
  $text = implode("\n", [
    'A new comment is waiting for review on "' . $title . '".',
    '',
    'Author: ' . $author,
    'Email: ' . $email,
    'Page: ' . $url,
    '',
    $comment,
    '',
    'Review in the Panel:',
    $panelUrl,
  ]);

  $html = '<p>A new comment is waiting for review on <strong>'
    . nomadCommentsEscape($title)
    . '</strong>.</p>'
    . '<p>Author: ' . nomadCommentsEscape($author)
    . '<br>Email: ' . nomadCommentsEscape($email)
    . '<br>Page: <a href="' . nomadCommentsEscape($url) . '">'
    . nomadCommentsEscape($url)
    . '</a></p>'
    . '<blockquote style="margin:1rem 0;padding:0.75rem 1rem;border-left:3px solid #d6d3d1">'
    . nl2br(nomadCommentsEscape($comment), false)
    . '</blockquote>'
    . '<p><a href="' . nomadCommentsEscape($panelUrl) . '">Review in the Panel</a></p>';

  return [
    'text' => $text,
    'html' => $html,
  ];
}

/**
 * Sender address. An explicit valid `from` wins. Otherwise use
 * noreply@ plus the site host, which matches Kirby's own login mail.
 */
function nomadCommentsSenderAddress(mixed $from, string $host): ?string
{
  if (is_string($from)) {
    $from = trim($from);

    if (filter_var($from, FILTER_VALIDATE_EMAIL)) {
      return $from;
    }
  }

  $host = trim($host);
  $hostIsName = $host !== '' && filter_var(
    $host,
    FILTER_VALIDATE_DOMAIN,
    FILTER_FLAG_HOSTNAME
  );

  if ($hostIsName) {
    $address = 'noreply@' . $host;

    if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
      return $address;
    }
  }

  return null;
}

/**
 * Resolve the `styles` option.
 *
 * false                         no plugin CSS
 * true                          base stylesheet
 * string                        base stylesheet, then this CSS
 * ['css' => '...']              base stylesheet, then this CSS
 * ['base' => false, 'css' => '...'] only the CSS string
 */
function nomadCommentsStyleConfig(mixed $styles): array
{
  $base = false;
  $css = '';

  if ($styles === true) {
    $base = true;
  } elseif (is_string($styles)) {
    if (trim($styles) !== '') {
      $base = true;
      $css = $styles;
    }
  } elseif (is_array($styles)) {
    $base = array_key_exists('base', $styles) ? $styles['base'] === true : true;

    if (isset($styles['css']) && is_string($styles['css'])) {
      $css = $styles['css'];
    }
  }

  // Config CSS is trusted, but it is printed inside a style tag.
  $css = preg_replace('#</\s*style#i', '', $css) ?? '';
  $css = trim($css);

  return [
    'base' => $base,
    'css' => $css,
  ];
}

function nomadCommentsLog(string $message): void
{
  error_log('kirby-comments: ' . $message);
}

/**
 * Tell the site owner about a new pending comment.
 * A mail failure is logged and does not affect the saved comment.
 */
function nomadCommentsNotify(App $kirby, Page $page, array $data): void
{
  try {
    $notify = option('nomad.kirby-comments.notify', false);

    if (nomadCommentsNotifyEnabled($notify) === false) {
      return;
    }

    $recipients = nomadCommentsRecipients($notify);

    if ($recipients === []) {
      nomadCommentsLog('notify is set, but it has no valid email address.');
      return;
    }

    $host = '';

    try {
      $host = (string) $kirby->url('index', true)->host();
    } catch (Throwable) {
      $host = '';
    }

    $from = nomadCommentsSenderAddress(
      option('nomad.kirby-comments.from'),
      $host
    );

    if ($from === null) {
      nomadCommentsLog('no valid from address. Set nomad.kirby-comments.from in config.php.');
      return;
    }

    $fromName = option('nomad.kirby-comments.fromName');

    if (is_string($fromName) && trim($fromName) !== '') {
      $fromName = nomadCommentsOneLine($fromName);
    } else {
      $fromName = nomadCommentsOneLine($kirby->site()->title()->value());
    }

    $author = (string) ($data['author'] ?? '');
    $email = trim((string) ($data['email'] ?? ''));
    $comment = (string) ($data['text'] ?? '');
    $title = $page->title()->value();
    $siteTitle = $kirby->site()->title()->value();

    $props = [
      'from' => $from,
      'to' => $recipients,
      'subject' => nomadCommentsSubject(
        option('nomad.kirby-comments.subject', 'New comment on {title}'),
        [
          'title' => $title,
          'author' => $author,
          'site' => $siteTitle,
          'email' => $email,
        ]
      ),
      'body' => nomadCommentsMessage(
        $title,
        $page->url(),
        $page->panelUrl(),
        $author,
        $email,
        $comment
      ),
    ];

    if ($fromName !== '') {
      $props['fromName'] = $fromName;
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $props['replyTo'] = $email;
      $replyName = nomadCommentsOneLine($author);

      if ($replyName !== '') {
        $props['replyToName'] = $replyName;
      }
    }

    $kirby->email($props);
  } catch (Throwable $error) {
    nomadCommentsLog($error->getMessage());
  }
}

/**
 * Print the base stylesheet and any CSS from config.php.
 * Called from the comments snippets. Safe to call more than once.
 */
function nomadCommentsStyles(): void
{
  static $done = false;

  if ($done === true) {
    return;
  }

  $done = true;

  $config = nomadCommentsStyleConfig(option('nomad.kirby-comments.styles', false));

  if ($config['base'] === true) {
    $plugin = kirby()->plugin('nomad/kirby-comments');
    $asset = $plugin?->asset('comments.css');

    if ($asset !== null) {
      $tag = css($asset->url());

      if (is_string($tag) && $tag !== '') {
        echo $tag;
      }
    }
  }

  if ($config['css'] !== '') {
    echo '<style id="kirby-comments-styles">' . "\n"
      . $config['css']
      . "\n</style>\n";
  }
}
