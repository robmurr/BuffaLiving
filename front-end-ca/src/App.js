import "./App.css";
import React, { useState, useEffect } from "react";

function App() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [name, setName] = useState("");
  const [errorMessage, setErrorMessage] = useState("");
  const [successMessage, setSuccessMessage] = useState("");
  const [csrfToken, setCsrfToken] = useState(""); // Store CSRF token

  // Fetch CSRF token when the component loads
  useEffect(() => {
    fetch("https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/create_account.php", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    })
      .then((response) => response.json())
      .then((data) => setCsrfToken(data.csrfToken)) // Store token in state
      .catch((error) => console.error("Error fetching CSRF token:", error));
  }, []);

  const handleSubmit = (e) => {
    e.preventDefault();

    fetch("https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/create_account.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ name, email, password, csrfToken }), // Include CSRF token in request
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          setSuccessMessage(data.message);

          // Set the authentication token as a cookie (expires in 30 days)
          document.cookie = `auth_token=${data.authToken}; path=/; max-age=${30 * 24 * 60 * 60}; Secure; HttpOnly;`;

          // Redirect to homepage after setting the cookie
          window.location.href = 'https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(interactivemap)/'; // Replace '/home' with your home page URL
        } else {
          setErrorMessage(data.message);
        }
      })
      .catch((error) => {
        console.error("Network Error:", error);
        setErrorMessage("Something went wrong. Please try again.");
      });
  };

  return (
    <div className="sign-up-page">
      <div className="overlap-group-wrapper">
        <div className="overlap-group">
          <div className="rectangle" />
          <div className="signup-box">
            <div className="labeling">
              <div className="untitled">
                <img src="./svgfiles/Untitled.svg" alt="Logo or Icon" />
              </div>
              <div className="text-wrapper">Sign Up</div>
            </div>

            <div className="functionality">
              <div className="text-boxes">
                <div className="div">
                  <img
                    src="./svgfiles/8396413_id_card_identity_name_identification_icon.svg"
                    alt="id card"
                    className="input-icon"
                  />
                  <input
                    type="text"
                    placeholder="Enter your name"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    className="text-input"
                    required
                  />
                </div>
                <div className="div">
                  <img
                    src="./svgfiles/8726038_head_side_icon.svg"
                    alt="head side"
                    className="input-icon"
                  />
                  <input
                    type="email"
                    placeholder="Enter your email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="text-input"
                    required
                  />
                </div>
                <div className="div">
                  <img
                    src="./svgfiles/8726020_lock_alt_icon.svg"
                    alt="lock"

                    className="input-icon"
                  />
                  <input
                    type="password"
                    placeholder="Enter your password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className="text-input"
                    required
                  />
                </div>
              </div>

              {errorMessage && (
                <div className="error-message">{errorMessage}</div>
              )}
              {successMessage && (
                <div className="success-message">{successMessage}</div>
              )}

              <div className="account-text-container">
                <span className="account-text">Already have an account?</span>
                <span
                  className="login-link"
                  onClick={() =>
                    (window.location.href =
                      "https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(LoginPage)/")
                  }
                >
                  Log in
                </span>
              </div>
              <div className="buttons">
                <div className="sign-up-button">
                  <button className="div-wrapper" onClick={handleSubmit}>
                    <div className="text-wrapper-3">Create Account</div>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default App;
