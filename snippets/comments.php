<?php nomadCommentsStyles() ?>
<section id="comments">
  <h2 id="comment_header">
    Comments
  </h2>

  <?php
  $comments = $page->comments()
    ->toStructure()
    ->filterBy('status', 'approved')
    ->sortBy('date', 'asc');
  ?>

  <div class="comment-layout">
    <div class="comment-list-column">
      <?php if ($comments->count()): ?>
        <ul id="comment_list">
          <?php foreach ($comments as $comment): ?>
            <li class="comment">
              <div>
                <div>
                  <strong>
                    <?= esc($comment->author()) ?>
                  </strong>

                  <small>
                    <?= $comment->date()->toDate('M d, Y') ?>
                  </small>
                </div>

                <p>
                  <?= esc($comment->text()) ?>
                </p>
              </div>
              <?php if ($comment->reply()->isNotEmpty()): ?>
                <div class="comment-reply">
                  <p><?= esc($comment->reply()) ?></p>
                </div>
              <?php endif ?>
            </li>
          <?php endforeach ?>
        </ul>
      <?php else: ?>
        <p id="comment_none">
          No comments yet.
        </p>
      <?php endif ?>
    </div>

    <div class="comment-form-column">
      <h3 id="leave_comment_header">
        Leave a comment
      </h3>

      <?php
        $errors = get('errors', []);
        $old    = get('old', []);
      ?>

      <?php if (!empty($errors)): ?>
        <div class="comment-errors">
          <ul>
            <?php foreach ($errors as $error): ?>
              <li><?= esc($error) ?></li>
            <?php endforeach ?>
          </ul>
        </div>
      <?php endif ?>

      <form
        id="comment_form"
        method="post"
        action="<?= $page->url() ?>"
      >
        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
        <input type="hidden" name="comment_submit" value="1">

        <!-- Honeypot (spam bots fill this; humans never see it) -->
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0" aria-hidden="true">

        <div>
          <input
            name="author"
            required
            placeholder="Name"
            value="<?= esc($old['author'] ?? '') ?>"
          >

          <input
            name="email"
            type="email"
            required
            placeholder="Email"
            value="<?= esc($old['email'] ?? '') ?>"
          >

          <textarea
            name="text"
            required
            placeholder="Comment"
            rows="5"
          ><?= esc($old['text'] ?? '') ?></textarea>
        </div>

        <div>
          <small>
            Comments will be reviewed before publishing.
          </small>
          <div>
            <input type="submit" value="Post Comment">
          </div>
        </div>
      </form>
    </div>
  </div>
</section>
