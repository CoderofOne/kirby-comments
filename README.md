<img width="2000" height="1000" alt="Image" src="https://github.com/user-attachments/assets/edee2aca-fc73-46d4-8306-4615b86c5dce" />

# Kirby Comments (v1.1.0)

A drop-in, database-free comment system for **Kirby CMS 5+** with moderation, replies, a pending-only inbox view in the Panel and spam protection.


## ✨ Features
- Frontend comment form (posts to the article URL)
- Panel moderation (approve / deny / delete)
- **Pending-only inbox** at the Site level
- Approved / pending comment counts
- Create new comments or edit existing
- Add a reply to comment
- Spam protection (honeypot + rate limiting)
- CSRF protection
- Server-side validation
- Email when a new comment is submitted, configured in `config.php`
- Optional base styles, also configured in `config.php`
- Style the IDs and class names from your own stylesheet when the base styles are off

---

## 🧭 Moderation Workflow
1. Visitors submit comments → saved as **pending**
2. Editors open **Site → Pending Comments**
3. Click the article → approve / deny comments
4. Approved comments appear on the frontend

<img width="100%" height="auto" alt="Image" src="https://github.com/user-attachments/assets/76a9a3cb-8641-40e7-b9b4-5bad35062070" />

<img style="float:left;" width="400" height="auto" alt="Image" src="https://github.com/user-attachments/assets/374a1f63-8742-4a4e-b5d7-f9c28bd4cb69" />

<img style="float:left;" width="400" height="auto" alt="Image" src="https://github.com/user-attachments/assets/926c127d-3490-4386-9a8a-ef4361f44caa" />

## 📦 Manual Installation

Download nomad-kirby-comments.zip

Unzip file

Copy the plugin folder to:
`site/plugins/nomad-kirby-comments`

## ⚙️ Configuration

Add a `nomad.kirby-comments` entry to the array already returned by `site/config/config.php`. Both features stay off until you set them.

```php
return [
  'nomad.kirby-comments' => [
    // Who is emailed when a visitor submits a comment.
    // A string, a comma-separated list, or an array of addresses.
    'notify' => 'editor@example.com',

    // Address your mail server is allowed to send from.
    // If you leave this out, the plugin uses noreply@ plus the site host.
    // Set it when the host is not a public domain, such as localhost.
    'from' => 'comments@example.com',
    'fromName' => 'Comments',

    // Placeholders: {title} {author} {site} {email}
    'subject' => 'New comment on {title}',

    // true loads the plugin's base stylesheet.
    'styles' => true,
  ],
];
```

The message includes the comment, the page URL, and a Panel link. Its reply-to address is the commenter's email. The comment is still saved if the email cannot be sent. Kirby sends it with whatever transport you already use. Add an `email.transport` block in this same file when the site should send through SMTP.

`styles` accepts more than `true` and `false`:

```php
// Base stylesheet, then your own rules.
'styles' => <<<'CSS'
#comments {
  --kc-accent: #0f766e;
  --kc-accent-text: #ffffff;
}
CSS,

// Only your CSS, without the base stylesheet.
'styles' => [
  'base' => false,
  'css' => '#comments { font-size: 1rem; }',
],
```

The base sheet matches the comment section on jonathanhazelwood.com: a heading with a bottom border, comments stacked above the form in one column, gray comment cards, and a red submit button. Override `--kc-text`, `--kc-body`, `--kc-muted`, `--kc-empty`, `--kc-line`, `--kc-surface`, `--kc-reply`, `--kc-accent`, `--kc-accent-text`, `--kc-danger`, `--kc-danger-bg`, `--kc-radius`, and `--kc-gap` on `#comments`.

## Panel: Article blueprint
Add the comments field to your **article blueprint** (`site/blueprints/pages/article.yml`) or whichever template you are using for article.

```yaml
comments:
  extends: fields/comments
```

## Panel: Pending-Only Inbox (Site tab)
Add this tab to your `site/blueprints/site.yml`:

```yaml
tabs:
  comments:
    label: Pending Comments
    icon: chat
    sections:
      pendingComments:
        type: pages
        headline: Pending Comments
        layout: table
        query: site.index.filterBy("intendedTemplate","article").filterBy("comments","*=","pending")
        info: "{{ page.pendingCommentsCount }} pending · {{ page.approvedCommentsCount }} approved"
        limit: 50
        create: false
        sortable: false
        duplicate: false
        status: false

```

## Panel: Articles with Comments-Only Inbox (Site tab)
Add this tab within your 'tabs' section to your `site/blueprints/site.yml`:
```yaml
articleswithcomments:
    label: Articles with Comments
    icon: chat
    sections:
      existingcomments:
        type: pages
        headline: Articles with Comments
        query: site.index.filterBy("intendedTemplate", "article").filterBy("comments", "!=", "")
        info: "{{ page.comments.toStructure.count }} comments"
        limit: 50
        create: false
        sortable: false
        duplicate: false
        status: false
```
_**Important**_
- **Site Tab** assumes you are using the template "article". If you use a different named template the "site tab" will not work unless you update the template name within ("intendedTemplate","**article**") in the site.yml file under the **query** line.

## Frontend: Show comments + form
<img style="float:left;" width="500" height="auto" alt="Image" src="https://github.com/user-attachments/assets/d4275d95-9a07-4d17-ba71-82c470210b45" />

In your article template:

```php
<?php snippet('comments') ?>
```

Only **approved** comments display publicly; new submissions are saved as **pending**.

## Frontend: Show counts on lists
<img width="381" height="317" alt="Image" src="https://github.com/user-attachments/assets/455a1e35-d28f-48cf-9420-7cb9a886413b" />

In your article list loop:

```php
<?php snippet('comment-meta', ['page' => $article]) ?>
```
_**Important**_
- This snippet assumes you are using the page "**article**", if you are using a different page name update the **$article** variable to match page name. (i.e **$post**)

## Storage
Comments are stored in the article content txt file as a YAML structure field.

<img width="400" height="auto" alt="Image" src="https://github.com/user-attachments/assets/4852362e-17b0-4211-a024-ca2d4f43d11f" />

## License
MIT

## Updates (v1.1.0)

- **Email notifications.** Set `notify` in `site/config/config.php`. A new comment emails that address with the comment, the page URL, and a Panel link. The reply-to address is the commenter. The comment is still saved if the email fails. Set `from` to an address your mail server is allowed to send from.
- **Base styles.** Set `'styles' => true` to load `assets/comments.css`. A CSS string adds rules after that sheet. `['base' => false, 'css' => '...']` uses only your CSS.
- **One column.** Approved comments stay above the form at every screen width. Name and email still share a row from 768px up.
- **Text placement.** Inside each comment, the name, date, and comment text stack on the left. An admin reply sits in the blue bar under the comment, with the reply text at the left edge of that bar.
- The honeypot field stays hidden, and the comment markup uses `class="comment"` with the reply inside the comment.
