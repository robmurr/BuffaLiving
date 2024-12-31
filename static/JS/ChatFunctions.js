function toggleMenu() {
    const dropdown = document.getElementById('dropdown');
    if (dropdown.classList.contains('open')) {
        dropdown.classList.remove('open');
    } else {
        dropdown.classList.add('open');
    }
}
const fetchUserData = () => {
    fetch("https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/accountback.php", {
      credentials: "include",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data && !data.error) {
          setUser(data); // Set the user data for profile info
        } else {
          console.error("Error fetching user data:", data.error);
        }
      })
      .catch((error) => {
        console.error("Error fetching user data:", error);
      });
  };

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('chat-form');
    const messageInput = document.getElementById('message');
    const chatBox = document.querySelector('.chat-box');

    form.addEventListener('submit', function(event) {
        event.preventDefault();  // Prevent traditional form submission

        const message = messageInput.value;
        const tablename = document.getElementById('tablename').value; // Get the table name

        if (message) {
            // Send message to the server via AJAX
            fetch('ChatFunctions.php', {  // Use dedicated PHP endpoint
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `message=${encodeURIComponent(message)}&tablename=${encodeURIComponent(tablename)}`
            })
            .then(response => response.json()) // Expect JSON response
            .then(result => {
                if (result.status === 'success') {
                    messageInput.value = '';  // Clear the input field

                    // Append the new message to the chat-box
                    const newMessage = document.createElement('div');
                    newMessage.classList.add('chat-bubble', 'current-user-bubble'); // Assuming this is for the current user
                    newMessage.textContent = message;
                    chatBox.appendChild(newMessage);

                    // Scroll to the bottom of the chat box
                    chatBox.scrollTop = chatBox.scrollHeight;
                } else {
                    console.error('Error:', result.message); // Log error from server response
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
});

