<?php
if ($messages !== []) {
?>
  <div class="messages">
    <?php foreach ($messages as $message) : ?>
      <div class="message <?php echo htmlspecialchars($message['type'], ENT_QUOTES, 'UTF-8'); ?>">
        <?php
        if ($message['type'] === 'error') {
          echo "<i class='icon fa fa-times'></i>";
        } else if ($message['type'] === 'info') {
          echo "<i class='icon fa fa-info-circle'></i>";
        } else {
          echo "<i class='icon fa fa-check'></i>";
        }
        ?>
        <span><?php echo htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
    <?php endforeach; ?>
  </div>
<?php
}
?>