import React from 'react';
import Header from './Header';
import SearchBar from './SearchBar';
import PropertyCard from './PropertyCard';


const properties = [
  {
    id: 1,
    name: "Apartment #1",
    address: "1234 something something str, 15151, NY",
    details: "2 Rooms, 1.5 baths",
    size: "1500 sq ft",
    price: "$1500 / Month",
    listing: "User #83838",
    image: "/images/A1_pic.jpg"
  },
  {
    id: 2,
    name: "Apartment #2",
    address: "6515 yada yada str, 15151, NY",
    details: "1 Rooms, 1 baths",
    size: "1500 sq ft",
    price: "$1000 / Month",
    listing: "User #59852",
    image: "/images/A2_pic.jpg"
  },
  {
    id: 3,
    name: "Apartment #3",
    address: "51651 macarena road, 15151, NY",
    details: "2 Rooms, 1.5 baths",
    size: "1700 sq ft",
    price: "$1800 / Month",
    listing: "User #85613",
    image: "/images/A3_pic.jpg"
  },
  {
    id: 4,
    name: "Apartment #4",
    address: "9498 potato ave, 15151, NY",
    details: "2 Rooms, 1 baths",
    size: "1400 sq ft",
    price: "$1500 / Month",
    listing: "User #86488",
    image: "/images/A4_pic.jpg"
  }
];


const PropertyListing = () => {
  return (
    <div className="property-listing">
      <Header />
      <div className="gray-overlay"> 
        
        <main className="flex flex-col items-center mt-36 w-full max-w-[1120px] max-md:mt-10 max-md:max-w-full relative z-10">
          <section className="flex flex-col w-full max-md:max-w-full">
            <SearchBar />
            <h1 className="available-props">Available Properties</h1>
          </section>
          
        </main>
        {/* Property Listing */}
        
        <section className="property-list relative z-10">
          {properties.map((property) => (
            <div className="property-card" key={property.id}>
              <div className="filter-container relative z-10">
            <span className="filter-button">Filter</span>
            
            </div>
              <img
                className="property-image"
                src={property.image}
                alt={property.name}
              />
              <div className="property-details">
                <h2>{property.name}</h2>
                <p>{property.address}</p>
                <p>{property.details}</p>
                <p>{property.size}</p>
                <p>{property.price}</p>
                <p>{property.listing}</p>
              </div>
            </div>
          ))}
        </section>
        
      </div>
    </div>
  );
};

export default PropertyListing;
