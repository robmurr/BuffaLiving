import React from 'react';

const SearchBar = () => {
  return (
    <form className="search-bar">
      <label htmlFor="searchInput" className="sr-only"></label>
      <input
        type="text"
        id="searchInput"
        placeholder="Search properties"
        className="search-input"
      />
      <button type="submit" aria-label="Search">
        <img loading="lazy" src="/images/search-button.svg" alt="" className="search-icon" />
      </button>
    </form>
  );
};

export default SearchBar;
