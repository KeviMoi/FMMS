document.addEventListener("DOMContentLoaded", function() {
    fetchUnreadCount();
});

function fetchUnreadCount() {
    fetch('count_unread_notifications.php')
        .then(response => response.json())
        .then(data => {
            const unreadCountElement = document.getElementById('unreadCount');
            if (data.unread_count !== undefined) {
                if (data.unread_count > 0) {
                    unreadCountElement.innerText = data.unread_count;
                    unreadCountElement.style.display = 'inline'; // or 'block' depending on your CSS
                } else {
                    unreadCountElement.style.display = 'none';
                }
            } else {
                console.error('Error fetching unread count:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}
