<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <div id="modalDialog" class="modal" style="display: none">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5 class="modal-title">Notifications</h5>
                <button type="button" class="close close-icon" onclick="closeModal()">
                    <span class="material-icons-sharp">close</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="modal_container">
                <div class="alert-message_container">
                    <div class="alert-message-list" id="notificationList">
                        <!-- Notifications will be loaded here -->
                    </div>
                </div>
            </div>
            <!-- End of Modal Body -->
        </div>
    </div>

    <script>
        fetchNotifications();

        function fetchNotifications() {
            fetch('fetch_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const notificationList = document.getElementById('notificationList');
                    notificationList.innerHTML = '';

                    if (data.message) {
                        const noNotificationsMessage = document.createElement('div');
                        noNotificationsMessage.className = 'no-alert-messages';
                        noNotificationsMessage.innerHTML = `
                            <p>${data.message}</p>
                        `;
                        notificationList.appendChild(noNotificationsMessage);
                    } else {
                        data.forEach(notification => {
                            const notificationElement = document.createElement('div');
                            notificationElement.className = 'alert-message ' + (notification.is_read ? 'read' : 'unread');
                            notificationElement.innerHTML = `
                                <p class="alert-message_text">${notification.notification_message}</p>
                                <span class="date">${notification.notification_date}</span>
                            `;
                            notificationElement.addEventListener('click', () => markAsRead(notification.notification_id, notificationElement));

                            notificationList.appendChild(notificationElement);
                        });
                    }
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }

        function markAsRead(notification_id, notificationElement) {
            fetch('mark_as_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    notification_id: notification_id
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        notificationElement.classList.remove('unread');
                        notificationElement.classList.add('read');
                    } else {
                        console.error('Failed to mark notification as read:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function closeModal() {
            document.getElementById('modalDialog').style.display = 'none';
        }
    </script>
</body>

</html>

<!-- CSS -->
<style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap");

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
    }

    .animate-top {
        position: relative;
        animation: animatetop 0.4s;
    }

    @keyframes animatetop {
        from {
            top: -300px;
            opacity: 0;
        }

        to {
            top: 0;
            opacity: 1;
        }
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
        /* Semi-transparent background */
        backdrop-filter: blur(5px);
        /* Apply blur effect */
    }

    .modal-content {
        margin: 8% auto;
        border: 1px solid #888;
        max-width: 700px;
        background-color: #fff;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 10px;
        outline: 0;
        max-height: 80%;
        /* Ensure modal height doesn't exceed the viewport */
        display: flex;
        flex-direction: column;
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        border-top-left-radius: 0.3rem;
        border-top-right-radius: 0.3rem;
        position: sticky;
        top: 0;
        background-color: #fff;
        z-index: 10;
    }

    .modal-title {
        margin-bottom: 0;
        line-height: 1.5;
        margin-top: 0;
        font-size: 1.25rem;
        color: #666;
    }

    .close {
        float: right;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        opacity: 0.5;
        cursor: pointer;
        background-color: transparent;
        border: 0;
    }

    .modal_container {
        padding: 5px;
        overflow-y: auto;
        flex-grow: 1;
        /* Ensure the container takes up remaining space */
    }

    .alert-message_container {
        width: 100%;
        max-width: 100%;
        margin: 2px auto;
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
    }

    .alert-message-list {
        padding: 20px;
    }

    .alert-message {
        background-color: #f1f1f1;
        padding: 15px;
        margin-bottom: 10px;
        border-left: 5px solid #6c9bcf;
        border-radius: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.3s, color 0.3s;
    }

    .alert-message.read {
        background-color: #e0e0e0;
        border-left-color: #ccc;
    }

    .alert-message .alert-message_text {
        margin: 0;
        font-size: 16px;
        color: #333;
    }

    .alert-message .date {
        font-size: 14px;
        color: #666;
    }

    .no-alert-messages {
        background-color: #f1f1f1;
        padding: 15px;
        border-radius: 5px;
        text-align: center;
        color: #666;
    }

    /* Hover effect for notifications */
    .alert-message:hover {
        background-color: #d1e7ff;
        color: #0056b3;
    }

    .alert-message:hover .alert-message_text {
        color: #0056b3;
    }

    .alert-message:hover .date {
        color: #0056b3;
    }

    ::-webkit-scrollbar {
        height: 5px;
        width: 6px;
    }

    ::-webkit-scrollbar-track {
        box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.3);
    }

    ::-webkit-scrollbar-thumb {
        box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.3);
    }
</style>
<!-- End of CSS -->
