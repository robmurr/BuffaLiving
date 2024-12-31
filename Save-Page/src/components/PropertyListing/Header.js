import React from 'react';

const Header = () => {
  return (
    <header className="blue-bar">
      {/* Logo on the left */}
      <div className="nav-content">
        <img
          src="./images/Home.svg"
          alt="Company logo"
          className="logo"
        />
        
        {/* Menu button on the right */}
        <button className="menu-button">☰</button>
      </div>
    </header>
  );
};

export default Header;
