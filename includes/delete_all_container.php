<?php $url = basename($_SERVER['PHP_SELF'], '.php'); ?>

<div class="delete-all-container">
  <div class="delete-by-day">
    <label for=<?= $url . "-select" ?>>سڕینەوە بەپێی ڕۆژ</label>
    <select id=<?= $url . "-select" ?>>
      <option value="3">3</option>
      <option value="7">7</option>
      <option value="14">14</option>
      <option value="21">21</option>
    </select>
  </div>

  <div class="instant-delete">
    <button id=<?= $url . "-delete"?> class="red-btn">سڕینەوە هەر ئێستا</button>
  </div>
</div>