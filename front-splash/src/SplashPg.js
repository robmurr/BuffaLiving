import React, {useRef} from "react";
import "./SplashPg.css";

function SplashPg() {
    // Create a reference for the contact section
    const contactSectionRef = useRef(null);

    // Function to scroll to the contact section
    const scrollToContact = () => {
      contactSectionRef.current.scrollIntoView({ behavior: "smooth" });
    };
  
  return (
    <div className="front-page">
      <div className="div">
        {/* Contact Information Section */}
        {/* <div className="overlap"> */}
        <div className="overlap" ref={contactSectionRef}>
          <div className="frame">
            <div className="contact-us">
              <img
                className="element-ic-fluent"
                alt="Email Icon"
                src="./svgfiles/8725946_fast_mail_icon.svg"
              />
              <div className="text-wrapper">customerservice@gmail.com</div>
            </div>
            <div className="frame-3">
              <div className="text-wrapper-2">Privacy Policy</div>
              <div className="text-wrapper-2">Terms &amp; Conditions</div>
            </div>
          </div>
        </div>


        {/* Contact Us Section */}
        <div className="overlap-group">
          <div className="frame-4">
          <button
                className="menu-wrapper2 button2"
                onClick={scrollToContact} // Scrolls to the contact section
              >
                <div className="menu-2">Contact Us</div>
              </button>
            <div className="frame-2">
              <button
                className="menu-wrapper button"
                onClick={() =>
                  (window.location.href =
                    "https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(LoginPage)/")
                }
              >
                <div className="menu">Sign In</div>
              </button>
              <button
                className="menu-wrapper button"
                onClick={() =>
                  (window.location.href =
                    "https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(CreatePage)/")
                }
              >
                <div className="menu">Sign Up</div>
              </button>
            </div>
          </div>
          <img
            className="untitled"
            alt="Untitled"
            src="./svgfiles/logo_buff.png"
          />
        </div>

        {/* Intro Section */}
        <div className="intro-text-wrapper">
          <div className="intro-text">Welcome to BuffaLiving</div>
        </div>

        {/* Cards Section */}
        <div class="overlap-2">
          <div class="card-container">
            <div class="rent-section">
              <div class="rent-card">
                <img src="./photos/rent.png" alt="Rent" class="rent-photo" />
                <h4 class="rent-label">Rent</h4>
                <p class="rent-text">
                  {" "}
                  Discover a wide range of rental properties listed directly by
                  individual owners, offering unique living spaces and
                  personalized leasing opportunities.
                </p>
              </div>
            </div>
            <div class="lease-section">
              <div class="lease-card">
                <img src="./photos/lease.png" alt="Lease" class="lease-photo" />
                <h4 class="lease-label">Lease</h4>
                <p class="lease-text">
                  Find and lease your ideal home effortlessly with a wide
                  selection of rental properties that match your preferences and
                  budget
                </p>
              </div>
            </div>
            <div class="list-section">
              <div class="list-card">
                <img src="./photos/list.png" alt="List" class="list-photo" />
                <h4 class="list-label">List</h4>
                <p class="list-text">
                  Easily list your property directly on our platform, connecting
                  with potential renters and showcasing your space to a wide
                  audience
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default SplashPg;
