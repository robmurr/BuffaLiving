import React, { useState, useEffect } from 'react';
import './App.css'; // Global styles
import { useNavigate } from "react-router-dom";

function StarRating({ rating, onRating }) {
  const [hoverRating, setHoverRating] = useState(0);

  const handleMouseEnter = (index) => {
    setHoverRating(index);
  };

  const handleMouseLeave = () => {
    setHoverRating(0);
  };

  const handleClick = (index) => {
    onRating(index);
  };

  return (
    <div className="star-rating">
      <p>Rate this listing:</p>
      {[1, 2, 3, 4, 5].map((i) => (
        <span
          key={i}
          className={`star ${i <= (hoverRating || rating) ? 'yellow' : ''}`}
          onMouseEnter={() => handleMouseEnter(i)}
          onMouseLeave={handleMouseLeave}
          onClick={() => handleClick(i)}
        >
          &#9733;
        </span>
      ))}
      <span className="rating-text">{(rating).toFixed(1)} / 5</span>
    </div>
  );
}


// Main function
function App() {
  const [apartments, setProperties] = useState([]); // State to store properties
  const [csrfToken, setCsrfToken] = useState(''); // State for CSRF token
  const [menuOpen, setMenuOpen] = useState(false); // State for menu button
  const [user, setUser] = useState(null); 

  const navigate = useNavigate();

  // Fetch CSRF token on component mount
  useEffect(() => {
    // Fetch 4 initial properties and CSRF token on page load
    fetch('https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/saved-page.php',{
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          setCsrfToken(data.csrfToken);  // Set the CSRF token
          setProperties(data.apartments);  // Set the 4 initial properties
        } else {
          console.error('Failed to load initial properties:', data.message);
        }
      })
      .catch(err => console.error('Failed to fetch initial data:', err));
  }, []);

  // Star rating
  const handleRatingChange = (propertyId, newRating) => {
    setProperties(prevProperties =>
      prevProperties.map(property =>
        property.id === propertyId ? { ...property, rating: newRating } : property
      )
    );
      
    // Send updated rating to the backend
    fetch('https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/saved-page.php', {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": csrfToken
      },
      body: JSON.stringify({ type: "updateRating", propertyId, rating: newRating, csrfToken })
    })
    .then(response => response.json())
    .then(data => {
      if (!data.success) {
        console.error('Failed to update rating:', data.message);
      }
    })
    .catch(err => console.error('Error updating rating:', err));

  };

  const handleRemove = (propertyId) => {
    fetch(`https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/saved-page.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": csrfToken
      },
      body: JSON.stringify({ type: "removeProperty", propertyId, csrfToken })
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          setProperties(prevProperties =>
            prevProperties.filter(property => property.id !== propertyId)
          );
        } else {
          console.error('Failed to remove property:', data.message);
        }
      })
      .catch(err => console.error('Error removing property:', err));
  };
  
  
  const toggleMenu = () => {
    setMenuOpen(!menuOpen);
  };

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

  
  return (
    <div className="App">
      {/* Header */}
      <header className="blue-bar">
        <div className='buffaliving'>
        <p>BuffaLiving</p>
        </div>
      {/* Logo on the left */}
      <div className="nav-content">
        <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(interactivemap)/">
          <img
            src="./images/logo_buff.png"
            alt="Company logo"
            className="logo"
          />
        </a>
        
        {/* Menu button on the right */}
        <button className="menu-button" onClick={toggleMenu}>☰</button>
      </div>
      <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
      ></link>
      {/* Dropdown Menu */}
      {menuOpen && (
            <div className="dropdown-menu">
            {/* Profile Greeting */}
            <div className="user-info">
              {user?.photo ? (
                <img src={`data:image/jpeg;base64,${user.photo}`} alt="Profile" className="profile-photo" />
              ) : (
                <div className="profile-photo-placeholder">?</div>
              )}
              <p>Hello, {user?.name}</p>
            </div>
            <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(ProfilePage)/">
              <i class="fas fa-user"></i> Account
            </a>
            <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(Listing)/">
              <i class="fas fa-home"></i> Properties
            </a>
            <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(CompPg)/">
              <i class="fas fa-balance-scale"></i> Compare
            </a>
            <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/fa24-semesterproject-noproblems-1/Templates/ChatUser.php">
              <i class="fas fa-comments"></i> Chat
            </a>
            <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(Saved)/">
              <i class="fas fa-heart"></i> Saved
            </a>
            <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(createAListing)/">
              <i class="fas fa-list"></i> List
            </a>
            <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/react-welcome-page/logout.php">
              <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
          </div>
          )}
    </header>
      {/* Background */}
      <div className="property-listing"></div>
      {/* Gray box */}
      <div className="gray-overlay"> 
          
          

        <main className="flex flex-col items-center mt-36 w-full max-w-[1120px] max-md:mt-10 max-md:max-w-full relative z-10">
          <section className="flex flex-col w-full max-md:max-w-full">
            <h1 className="available-props">Saved Properties</h1>
          </section>
        </main>

        <section className="property-list relative z-10">
          {apartments.length > 0 ? (
            apartments.map((property) => (
              <div className="property-card" key={property.id}>
                

                <img
                  className="property-image"
                  src={property.image}
                  alt={property.title}
                />
                <div className="property-details">
                  <h2>{property.title}</h2>
                  <p>{property.address}</p>
                  <p>Rooms: {property.bed}</p>
                  <p>Bathrooms: {property.bath}</p>
                  <p>Size: {property.sqft} sq ft</p>
                  <p>Price: ${property.price}</p>
                  <StarRating 
                    rating={property.rating}
                    onRating={(newRating) => handleRatingChange(property.id, newRating)}
                  />
                </div>
                <button className="remove-button" onClick={() => handleRemove(property.id)}>
                    Remove
                  </button>
              </div>
            ))
          ) : (
            <p>No Saved Listings Found.</p>
          )}
        </section>
      
      </div>
    </div>
  );
}

export default App;

