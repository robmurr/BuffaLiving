import React, { useState, useEffect } from "react";
import "./App.css";

function App() {
  const [menuOpen, setMenuOpen] = useState(false);
  const [apartment1, setApartment1] = useState(null);
  const [apartment2, setApartment2] = useState(null);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [message, setMessage] = useState(
    "Please log in to access the comparison page."
  );
  const [user, setUser] = useState(null); // User state for profile data

  const toggleMenu = () => {
    setMenuOpen(!menuOpen);
  };

  function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
    return null;
  }

  const checkTokens = () => {
    const csrfToken = getCookie("PHPSESSID");

    if (csrfToken) {
      setIsAuthenticated(true);
      fetchUserData();
    } else {
      setIsAuthenticated(false);
      setMessage("Unauthorized access. Please log in to access this page.");
    }
  };
  

  // Fetch user data for profile info
  const fetchUserData = () => {
    fetch(
      "https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/accountback.php",
      {
        credentials: "include",
      }
    )
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

  const fetchApartments = () => {
    fetch(
      "https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/Compare.php",
      {
        credentials: "include",
      }
    )
      .then((response) => {
        if (response.status === 401) {
          setIsAuthenticated(false);
          setMessage("Unauthorized access. Please log in.");
          return null;
        }
        return response.json();
      })
      .then((data) => {
        if (data) {
          setApartment1(data[0] || null);
          setApartment2(data[1] || null);
        }
      })
      .catch((error) => {
        console.error("Error fetching apartment data:", error);
      });
  };

  useEffect(() => {
    checkTokens();
  }, []);

  useEffect(() => {
    if (isAuthenticated) {
      fetchApartments();
    }
  }, [isAuthenticated]);

  const removeApartment = (apartmentId) => {
    if (!isAuthenticated) {
      alert("Unauthorized action. Please log in.");
      return;
    }

    fetch(
      "https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/Compare.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include",
        body: JSON.stringify({ id: apartmentId }),
      }
    )
      .then((response) => {
        if (response.status === 401) {
          setIsAuthenticated(false);
          setMessage("Unauthorized action. Please log in.");
          return null;
        }
        return response.json();
      })
      .then((data) => {
        if (data && data.success) {
          fetchApartments();
        } else if (data && data.error) {
          console.error("Error removing apartment:", data.error);
        }
      })
      .catch((error) => {
        console.error("Error removing apartment:", error);
      });
  };

  return (
    <div className="compare-page">
      {isAuthenticated ? (
        <div className="compare-page-pt">
          <div className="overlap">
            <div className="white-frame-box">
              <div className="rectangle">
                <div className="text-container">
                  <p className="p">Add property to be compared:</p>
                </div>
              </div>
            </div>

            <div className="top-bar">
              <div className="buffaliving">
                <p>BuffaLiving</p>
              </div>
              <img
                className="untitled"
                alt="Untitled"
                src="./svgfiles/logo_buff.png"
              />
              {/* Menu Button */}
              <button className="menu-btn" onClick={toggleMenu}>
                <img
                  className="element-menu-lines"
                  alt="Element menu lines"
                  src="./svgfiles/134216_menu_lines_hamburger_icon.svg"
                />
              </button>
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
                    <img
                      src={`data:image/jpeg;base64,${user.photo}`}
                      alt="Profile"
                      className="profile-photo"
                    />
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
            {/* categories */}
            <div className="comparison-container">
              <div className="categories">
                <div className="overlap-group">
                  <div className="overlap-2">
                    <div className="text-wrapper-2">Listing</div>
                  </div>
                  <div className="overlap-3">
                    <div className="text-wrapper-3">Address</div>
                  </div>
                  <div className="overlap-4">
                    <div className="text-wrapper-4">Square Footage</div>
                  </div>
                  <div className="overlap-5">
                    <div className="rectangle-2" />
                    <div className="text-wrapper-5">Bed/Bath</div>
                  </div>
                  <div className="overlap-6">
                    <div className="text-wrapper-6">Price</div>
                  </div>
                  <div className="overlap-group-2">
                    <div className="text-wrapper-7">Amenities</div>
                  </div>
                  <div className="overlap-7">
                    <div className="text-wrapper-8">Rating</div>
                  </div>
                </div>
              </div>
              <div className="rectangle-3" />
              <div className="compare">
                {/* Apartment #1 */}
                <div className="overlap-group">
                  <div className="overlap-group-3">
                    <div className="text-wrapper-15">
                      {apartment1 ? apartment1.title : "No Data"}
                    </div>
                  </div>
                  <div className="overlap-8">
                    <div className="text-wrapper-9">
                      {apartment1 ? apartment1.address : "No Data"}
                    </div>
                  </div>
                  <div className="overlap-9">
                    <div className="text-wrapper-10">
                      {apartment1 ? apartment1.sqft : "No Data"} sqft
                    </div>
                  </div>
                  <div className="overlap-10">
                    <div className="text-wrapper-11">
                      {apartment1 ? apartment1.bed : "No Data"} bed/{" "}
                      {apartment1 ? apartment1.bath : "No Data"} bath
                    </div>
                  </div>
                  <div className="overlap-11">
                    <div className="text-wrapper-12">
                      {apartment1 ? apartment1.price : "No Data"} /Mth
                    </div>
                  </div>
                  <div className="overlap-12">
                    <div className="text-wrapper-13">
                      {apartment1 ? apartment1.amenities : "No Data"}
                    </div>
                  </div>
                  <div className="overlap-13">
                    <div className="text-wrapper-14">
                      {apartment1 ? apartment1.rating : "No Data"}
                    </div>
                  </div>
                  {apartment1 && (
                    <button
                      className="remove-btn"
                      onClick={() => removeApartment(apartment1.id)}
                    >
                      Remove
                    </button>
                  )}
                </div>
                {/* Apartment #2 */}
                <div className="overlap-wrapper">
                  <div className="overlap-group">
                    <div className="overlap-15">
                      <div className="text-wrapper-22">
                        {apartment2 ? apartment2.title : "No Data"}
                      </div>
                    </div>
                    <div className="overlap-20">
                      <div className="text-wrapper-16">
                        {apartment2 ? apartment2.address : "No Data"}
                      </div>
                    </div>
                    <div className="overlap-16">
                      <div className="text-wrapper-17">
                        {apartment2 ? apartment2.sqft : "No Data"} sqft
                      </div>
                    </div>
                    <div className="overlap-14">
                      <div className="text-wrapper-18">
                        {apartment2 ? apartment2.bed : "No Data"} bed/{" "}
                        {apartment2 ? apartment2.bath : "No Data"} bath
                      </div>
                    </div>
                    <div className="overlap-19">
                      <div className="text-wrapper-19">
                        {apartment2 ? apartment2.price : "No Data"} /Mth
                      </div>
                    </div>
                    <div className="overlap-17">
                      <div className="text-wrapper-20">
                        {apartment2 ? apartment2.amenities : "No Data"}
                      </div>
                    </div>
                    <div className="overlap-18">
                      <div className="text-wrapper-21">
                        {apartment2 ? apartment2.rating : "No Data"}
                      </div>
                    </div>
                    {apartment2 && (
                      <button
                        className="remove-btn"
                        onClick={() => removeApartment(apartment2.id)}
                      >
                        Remove
                      </button>
                    )}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      ) : (
        <div>{message}</div>
      )}
    </div>
  );
}

export default App;
