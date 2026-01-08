# Kirby Comments (v1.0.0)

A drop-in, database-free comment system for **Kirby CMS 5+** with moderation, a pending-only inbox view in the Panel, email notifications, and spam protection.

<img width="1554" height="696" alt="Image" src="https://github.com/user-attachments/assets/f5e252d1-de2d-434a-b192-0ba8910fa0ed" />

## ✨ Features
<img width="1469" height="277" alt="Image" src="https://github.com/user-attachments/assets/76a9a3cb-8641-40e7-b9b4-5bad35062070" />

- Frontend comment form (posts to the article URL)
- Panel moderation (approve / deny / delete)
- **Pending-only inbox** at the Site level
- Approved / pending comment counts
- Create new comments or edit existing
- Spam protection (honeypot + rate limiting)
- CSRF protection
---

## 🧭 Moderation Workflow
1. Visitors submit comments → saved as **pending**
2. Editors open **Site → Pending Comments**
3. Click the article → approve / deny comments
4. Approved comments appear on the frontend

<img style="float:left; margin-right:25px;" width="200" height="auto" alt="Image" src="https://github.com/user-attachments/assets/374a1f63-8742-4a4e-b5d7-f9c28bd4cb69" />

<img style="float:left;" width="200" height="auto" alt="Image" src="https://github.com/user-attachments/assets/c0fada5e-f063-4f34-9865-06b2ec0094ec" />

<img style="float:left; margin-right:25px;" width="50%" height="auto" alt="Image" src="https://github.com/user-attachments/assets/926c127d-3490-4386-9a8a-ef4361f44caa" />

<img style="float:left;" width="50%" height="auto" alt="Image" src="https://github.com/user-attachments/assets/d4275d95-9a07-4d17-ba71-82c470210b45" />

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
```

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
        query: site.index.filterBy("intendedTemplate","article").filterBy("comments","*=","pending")
        info: "{{ page.pendingCommentsCount }} pending · {{ page.approvedCommentsCount }} approved"
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

## Storage
Comments are stored in the article content file as a YAML structure field.

## License
MIT
