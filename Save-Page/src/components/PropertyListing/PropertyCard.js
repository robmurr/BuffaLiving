import React from 'react';

const PropertyCard = ({ property }) => {
  return (
    <div className="property-card">
      <img src={property.image || 'https://via.placeholder.com/150'} alt={property.name} />
      <div className="property-details">
        <h3>{property.title}</h3>
        <p>{property.address}</p>
        <p>{property.rooms} Rooms, {property.bathrooms} Baths</p>
        <p>{property.size} sq ft</p>
        <p>${property.price} / Month</p>
      </div>
    </div>
  );
};

export default PropertyCard;
