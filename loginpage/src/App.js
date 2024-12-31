import React, { useState, useEffect } from "react";
import './App.css';
import logo from './imgs/logo.svg';

const App = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loggedIn, setLoggedIn] = useState(false);
  const [loginMessage, setLoginMessage] = useState('');
  const [csrfToken, setCsrfToken] = useState(""); // Store CSRF token
  // Fetch CSRF token when the component loads
  useEffect(() => {
    fetch('https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/login/index.php', {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    })
      .then((response) => response.json())
      .then((data) => setCsrfToken(data.csrfToken)) // Store token in state
      .catch((error) => console.error("Error fetching CSRF token:", error));
  }, []);
//               <b href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/create-account/create_account.php" className="signup-btn signup-text">Sign Up</b>
  const handleSubmit = (e) => {
    e.preventDefault();

    fetch('https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/login/index.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({email, password, csrfToken}),
    })
      .then(response => response.text()) // Fetch the raw text response instead of JSON
      .then((text) => {
        console.log("Raw Response:", text); // Log the raw text
        try {
          const data = JSON.parse(text); // Attempt to parse JSON
          if (data.success) {
            setLoggedIn(true);
            setLoginMessage(data.message);
          } else {
            setLoggedIn(false);
            setLoginMessage(data.message);
          }
        } catch (error) {
          console.error("JSON Parsing Error:", error, "Response Text:", text);
          setLoginMessage('The server returned an unexpected response. Please try again.');
        }
      })
      .catch((error) => {
        console.error('Network Error:', error);
        setLoginMessage('Something went wrong, please try again.');
      });
  };

  return (
    <div className="login-container">
      <div className="login-box">
      <img src={logo} alt="Housing Logo" className="logo" />
        {loggedIn ? (
          <>
          <h1>{loginMessage}</h1>
          <button className="go-home-btn" onClick={() => window.location.href = 'https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/Logout/build(tempMains)/'}>
            Go Home
          </button>
        </>
        ) : (
          <>
            <h1>Sign In</h1>
            <form onSubmit={handleSubmit}>
              <div className="input-group">
                <label htmlFor="email">Email Address</label>
                <input 
                  type="email" 
                  id="email" 
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="Enter your email"
                  required
                />
              </div>
              <div className="input-group">
                <label htmlFor="password">Password</label>
                <input 
                  type="password" 
                  id="password" 
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="Enter your password"
                  required
                />
              </div>
              <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/fa24-semesterproject-noproblems-1/Templates/PasswordReset.php" className="forgot-password-btn forget-text">Forgot Password?</a>
              <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(CreatePage)/" className="signup-btn signup-text">Sign Up</a>
              <button type="submit" className="login-btn">Login</button>
              {loginMessage && <p>{loginMessage}</p>}
            </form>
          </>
        )}
      </div>
    </div>
  );
};

export default App;