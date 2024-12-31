<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Listing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .listing-form {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .listing-form h2 {
            margin-bottom: 20px;
        }
        .listing-form .form-group {
            margin-bottom: 15px;
        }
        .listing-form .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .listing-form .form-group input,
        .listing-form .form-group textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .listing-form .form-group button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 4px;
        }
        .listing-form .form-group button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="listing-form">
        <h2>Post a Listing</h2>
        <form action="https://se-dev.cse.buffalo.edu/CSE442/2024-Fall/justindv/post_listing.php" method="POST">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" required>
            </div>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required>
            </div>
            <div class="form-group">
                <label for="image_url">Image URL:</label>
                <input type="url" id="image_url" name="image_url">
            </div>
            <div class="form-group">
                <button type="submit">Post Listing</button>
            </div>
        </form>
    </div>
</body>
</html>

<script>
    document.querySelector('form').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent form submission

        const formData = {
            title: document.getElementById('title').value,
            description: document.getElementById('description').value,
            price: document.getElementById('price').value,
            location: document.getElementById('location').value,
            image_url: document.getElementById('image_url').value,
        };

        fetch('post_listing.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData),
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
        })
        .catch(error => console.error('Error:', error));
    });
</script>
