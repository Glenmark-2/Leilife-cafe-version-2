</div> <!-- end of .container -->
<footer class="site-footer">
  <div class="footer-logo">
    <img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/leilife.png" alt="Logo">
  </div>

  <div class="footer-bottom">
    <!-- Contact Info -->
    <div class="footer-column">
      <h3>Contact Us</h3>
      <p><img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/facebook.png" alt="Facebook"> Leilife Café & Restaurant</p>
      <p><img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/white-pin.png" alt="Address"> Lunduyan Langaray, Brgy 14. Caloocan City</p>
      <p><img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/white-call.png" alt="Phone"> 0912345678</p>
      <p><img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/white-messages.png" alt="Email"> leilifecafe@gmail.com</p>
    </div>

    <!-- Links -->
    <div class="footer-column">
      <h3>Quick Links</h3>
      <p><a href="<?= UrlHelper::getBaseUrl() ?>/public/index.php?page=home">Home</a></p>
      <p><a href="index.php?page=home#about-us">About</a></p>
      <p><a href="index.php?page=home#contact-us">Contact</a></p>
    </div>
  </div>
</footer>
<!-- Bootstrap JS Bundle -->
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<?php if (isset($page) && $page === 'menu'): ?>
  <?php include __DIR__ . '/view_orders_pill.php'; ?>
<?php endif; ?>

</body>

</html>