<?php
if (!empty($_SESSION['messages'])) {
?>
  <div class="messages">
    <?php foreach ($_SESSION['messages'] as $message) : ?>
      <div class="message <?php echo htmlspecialchars($message['type'], ENT_QUOTES, 'UTF-8'); ?>">
        <?php
        $icon = 'fa-check';
        if ($message['type'] === 'error') {
          $icon = 'fa-times';
        } elseif ($message['type'] === 'info') {
          $icon = 'fa-info-circle';
        }
        echo "<i class='icon fa $icon'></i>";
        ?>
        <span><?php echo htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
    <?php endforeach; ?>
    <?php $_SESSION['messages'] = []; ?>
  </div>
<?php
}
?>