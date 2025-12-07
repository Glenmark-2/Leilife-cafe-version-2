<?php
function infoCard($logo, $title, $text) {
    return "
    <div class='info-card-container'>
      <div class='info-card'>
        <div class='info-logo'>{$logo}</div>
        <h3 class='info-title'>{$title}</h3>
        <p class='info-text'>{$text}</p>
      </div>
    </div>
    ";
}
?>
