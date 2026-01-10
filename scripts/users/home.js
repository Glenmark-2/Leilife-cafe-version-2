document.addEventListener('DOMContentLoaded', function () {
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        const sendBtn = contactForm.querySelector('button');
        if (sendBtn) {
            sendBtn.addEventListener('click', async function (e) {
                e.preventDefault();

                // Form validation handled by browser 'required' is not automatically triggerred by button type="button"
                // But we can check manually or change button type.
                if (!contactForm.checkValidity()) {
                    contactForm.reportValidity();
                    return;
                }

                const formData = new FormData(contactForm);
                const data = Object.fromEntries(formData.entries());

                try {
                    sendBtn.disabled = true;
                    sendBtn.innerText = 'Sending...';

                    const response = await fetch(`${window.BASE_URL}/backend/api/contact/send_message.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();
                    if (result.success) {
                        alert('Thank you! Your message has been sent.');
                        contactForm.reset();
                    } else {
                        alert(result.message || 'Failed to send message.');
                    }
                } catch (error) {
                    console.error('Error sending message:', error);
                    alert('An error occurred. Please try again later.');
                } finally {
                    sendBtn.disabled = false;
                    sendBtn.innerText = 'Send';
                }
            });
        }
    }
});
