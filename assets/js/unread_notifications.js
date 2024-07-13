document.addEventListener("DOMContentLoaded", function() {
    fetchUnreadCount();
});

function fetchUnreadCount() {
    fetch('count_unread_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.unread_count !== undefined) {
                document.getElementById('unreadCount').innerText = data.unread_count;
            } else {
                console.error('Error fetching unread count:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}