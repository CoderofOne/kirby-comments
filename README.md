# Nomad Kirby Comments (v1.0.0)

A drop-in, database-free comment system for **Kirby CMS 5+** with moderation, a pending-only inbox view in the Panel, email notifications, and spam protection.

## ✨ Features

- Frontend comment form (posts to the article URL)
- Panel moderation (approve / deny / delete)
- **Pending-only inbox** at the Site level
- Approved / pending comment counts
- Spam protection (honeypot + rate limiting)
- CSRF protection
- Optional email notifications
- Git-friendly content storage (no database)
- Composer / Packagist support

---

## 🧭 Moderation Workflow

1. Visitors submit comments → saved as **pending**
2. Editors open **Site → Pending Comments**
3. Click the article → approve / deny comments
4. Approved comments appear on the frontend

## 📦 Manual Installation

Download Latest Release Zip File

Unzip file

Copy the plugin folder to:
`site/plugins/nomad-kirby-comments`

## Panel: Article blueprint
Add the comments field and the hidden pending flag to your **article blueprint** (`site/blueprints/pages/article.yml`):

```yaml
comments:
  extends: fields/comments

hasPendingComments:
  type: hidden
```

> `hasPendingComments` is automatically maintained by the plugin and is used for the pending-only inbox filter.

## Panel: Pending-only inbox (Site tab)
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
        query: site.index
          .filterBy("intendedTemplate", "article")
          .filterBy("hasPendingComments", "true")
        info: >
          {{ page.pendingCommentsCount }} pending ·
          {{ page.approvedCommentsCount }} approved
        limit: 50
        create: false
        sortable: false
        duplicate: false
        status: false
```

## Frontend: Show comments + form
In your article template:

```php
<?php snippet('comments') ?>
```

Only **approved** comments display publicly; new submissions are saved as **pending**.

## Frontend: Show counts on lists
In your blog/home list loop:

```php
<?php snippet('comment-meta', ['page' => $article]) ?>
```

## Email notifications (optional)
In `site/config/config.php`:

```php
return [
  'nomad.kirby.comments.notify' => true,
  'nomad.kirby.comments.email'  => 'you@example.com',
];
```

## Storage
Comments are stored in the article content file as a YAML structure field.

## License
MIT
