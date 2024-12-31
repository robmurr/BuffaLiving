import React, {useState} from 'react'
import { useNavigate } from 'react-router-dom';
import './Cabox.css';


function Cabox() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [name, setName] = useState('');

  // Initialize useNavigate hook
  const navigate = useNavigate();

  const handleSubmit = (e) => {
    e.preventDefault();
    // Handle the create account logic here
    console.log('Email:', email);
    console.log('Password:', password);
    console.log('Name:', name);

    // After successful account creation, navigate to the next page
    navigate('/welcome'); // Example: Redirect to a 'welcome' page
  };

  return (
    <div className="sign-up-page">
      <div className="overlap-group-wrapper">
        <div className="overlap-group">
          <div className="rectangle" />
          <div className="signup-box">
            <div className="labeling">
              <div className="untitled">
                <img src="/svgfiles/Untitled.svg" alt="Logo or Icon" />
              </div>
              <div className="text-wrapper">Sign Up</div>
            </div>

            <div className="functionality">
              <div className="text-boxes">
                <div className="div">
                <img src="/svgfiles/8396413_id_card_identity_name_identification_icon.svg" alt="id card" className="input-icon"/>
                <input type="text" placeholder="Enter your name" value={name} onChange={(e) => setName(e.target.value)} className="text-input" required/>
              </div>
                <div className="div">
                <img src="/svgfiles/8726038_head_side_icon.svg" alt="head side" className="input-icon" />
                <input type="email" placeholder="Enter your email" value={email} onChange={(e) => setEmail(e.target.value)} className="text-input" required/>
                </div>
                <div className="div">
                <img src="/svgfiles/8726020_lock_alt_icon.svg" alt="lock" className="input-icon" />
                <input type="password" placeholder="Enter your password" value={password} onChange={(e) => setPassword(e.target.value)} className="text-input" required />
                </div>
              </div>
              <div className="account-text-container">
                <span className="account-text">Already have an account?</span>
                <span className="login-link"  onClick={() => navigate('/login')} /* Link to the login page */>
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
;

// const styles = {
//   container: {
//     display: 'flex',
//     flexDirection: 'column',
//     alignItems: 'center',
//     justifyContent: 'center',
//     minHeight: '100vh',
//     backgroundColor: '#f5f5f5',
//   },
//   form: {
//     display: 'flex',
//     flexDirection: 'column',
//     width: '300px',
//     padding: '20px',
//     borderRadius: '8px',
//     backgroundColor: '#fff',
//     boxShadow: '0 2px 10px rgba(0, 0, 0, 0.1)',
//   },
//   inputGroup: {
//     marginBottom: '15px',
//   },
//   button: {
//     padding: '10px 20px',
//     backgroundColor: '#4CAF50',
//     color: '#fff',
//     border: 'none',
//     borderRadius: '4px',
//     cursor: 'pointer',
//     fontSize: '16px',
//   },


export default Cabox
