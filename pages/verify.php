<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Leilife</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../global_styles.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .card {
            max-width: 400px;
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="card text-center p-4">
    <div class="mb-3" id="icon-container">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <h3 class="mb-3" id="status-title">Verifying...</h3>
    <p class="text-muted" id="status-message">Please wait while we verify your email address.</p>
    <a href="home.php" class="btn btn-primary d-none" id="home-btn">Go to Homepage</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        
        const iconContainer = document.getElementById('icon-container');
        const title = document.getElementById('status-title');
        const message = document.getElementById('status-message');
        const homeBtn = document.getElementById('home-btn');

        if (!token) {
            iconContainer.innerHTML = '<span style="font-size: 3rem;">⚠️</span>';
            title.textContent = 'Invalid Link';
            message.textContent = 'No verification token found.';
            homeBtn.classList.remove('d-none');
            return;
        }

        fetch(`../backend/api/verify_email.php?token=${token}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    iconContainer.innerHTML = '<span style="font-size: 3rem;">✅</span>';
                    title.textContent = 'Verified!';
                    message.textContent = data.message;
                    homeBtn.textContent = 'Login Now';
                    homeBtn.href = '../public/index.php?page=home&login=true';
                } else {
                    iconContainer.innerHTML = '<span style="font-size: 3rem;">❌</span>';
                    title.textContent = 'Verification Failed';
                    message.textContent = data.message;
                    homeBtn.textContent = 'Back to Home';
                    homeBtn.href = '../public/index.php?page=home';
                }
                homeBtn.classList.remove('d-none');
            })
            .catch(error => {
                console.error('Error:', error);
                iconContainer.innerHTML = '<span style="font-size: 3rem;">⚠️</span>';
                title.textContent = 'Error';
                message.textContent = 'Something went wrong. Please try again later.';
                homeBtn.classList.remove('d-none');
            });
    });
</script>

</body>
</html>
