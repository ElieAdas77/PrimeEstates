<?php

require_once "php/config.php";


function getApprovedProperties($conn, $listingType) {
    $stmt = $conn->prepare(
        "SELECT p.id, title, location, price, area, bedrooms, bathrooms,
                property_type, amenities, description, images
         FROM properties p
         WHERE status = 'approved' AND listing_type = ?
         ORDER BY created_at DESC"
    );
    $stmt->bind_param("s", $listingType);
    $stmt->execute();
    $result = $stmt->get_result();

    $properties = [];
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
    $stmt->close();

    return $properties;
}


function renderPropertyCard($p, $listingType) {
    $badge = $listingType === "rent" ? "FOR RENT" : "FOR SALE";
    $badgeClass = $listingType === "rent" ? "property-badge rent-badge" : "property-badge";
    $priceDisplay = $listingType === "rent"
        ? "$" . number_format($p["price"]) . "/Month"
        : "$" . number_format($p["price"]);

    $imagePaths = [];
    if (!empty($p["images"])) {
        foreach (explode(",", $p["images"]) as $img) {
            $img = trim($img);
            if ($img !== "") {
                $imagePaths[] = "uploads/properties/" . $img;
            }
        }
    }
    $firstImage = $imagePaths[0] ?? "";
    $imagesAttr = implode(",", $imagePaths);

    $amenitiesDisplay = !empty($p["amenities"])
        ? str_replace(",", ", ", $p["amenities"])
        : "";

    ?>
    <div
      class="property-card fade-in"
      data-location="<?php echo htmlspecialchars($p["location"]); ?>"
      data-price="<?php echo htmlspecialchars($priceDisplay); ?>"
      data-size="<?php echo htmlspecialchars($p["area"]); ?>m&sup2;"
      data-bedrooms="<?php echo (int) $p["bedrooms"]; ?>"
      data-extras="<?php echo htmlspecialchars($amenitiesDisplay); ?>"
      data-listing="<?php echo htmlspecialchars($listingType); ?>"
      data-type="<?php echo htmlspecialchars($p["property_type"]); ?>"
      data-description="<?php echo htmlspecialchars($p["description"]); ?>"
      data-images="<?php echo htmlspecialchars($imagesAttr); ?>"
      data-id="<?php echo (int) $p["id"]; ?>"
    >

      <button class="favorite-btn" data-id="<?php echo (int) $p["id"]; ?>" aria-label="Save to favorites">
        <i class="fa-regular fa-heart"></i>
      </button>
      <span class="<?php echo $badgeClass; ?>"><?php echo $badge; ?></span>
      <?php if ($firstImage !== ""): ?>
        <img src="<?php echo htmlspecialchars($firstImage); ?>" />
      <?php endif; ?>
      <h3><?php echo htmlspecialchars($p["title"]); ?></h3>
      <p>Location: <?php echo htmlspecialchars($p["location"]); ?></p>
      <p>Price: <?php echo htmlspecialchars($priceDisplay); ?></p>
      <div class="icons">
        <span><i class="fa-solid fa-bed"></i><?php echo (int) $p["bedrooms"]; ?></span>
        <span><i class="fa-solid fa-bath"></i><?php echo (int) $p["bathrooms"]; ?></span>
        <span><i class="fa-solid fa-ruler-combined"></i> <?php echo htmlspecialchars($p["area"]); ?>m<sup>2</sup></span>
      </div>
      <button class="view-property-btn">View Property</button>
    </div>
    <?php
}

$saleProperties = getApprovedProperties($conn, "sale");
$rentProperties = getApprovedProperties($conn, "rent");

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Real Estate Website</title>

    <link rel="stylesheet" href="main22.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />
    <!-- lal icons -->
  </head>

  <body>
    <!-- Navigation Bar -->
    <header class="header">
      <div class="logo">PrimeEstates</div>

      <!-- Hamburger button -->
      <button class="menu-toggle" id="mobileMenuBtn">
        <i class="fa-solid fa-bars"></i>
      </button>

      <nav id="mainNav">
        <a href="#properties">Properties</a>
        <a href="#about">About</a>
        <a href="#testimonial">Reviews</a>
        <a href="#contact">Contact</a>

        <a href="#" class="nav-btn" id="addPropertyBtn">
          <i class="fa-solid fa-plus"></i> Add Property
        </a>

        <div class="user-menu" id="userMenu">
          <a href="#" class="nav-btn" id="registerBtn">Register</a>

          <div class="user-dropdown hidden" id="userDropdown">
            <a href="#" id="myPropertiesBtn">
              <i class="fa-solid fa-house"></i> My Properties
            </a>
            <a href="#" id="favoritesBtn">
              <i class="fa-solid fa-heart"></i> Favorites
            </a>
            <a href="#" id="logoutBtn">
              <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
          </div>
        </div>
      </nav>
    </header>

    <!-- Section 2: Title Only -->
    <section id="hero">
      <div class="slideshow-container">
        <!--pics slideshow-->
        <div
          class="slide-image active-slide"
          style="background-image: url(&quot;heroooo.jpg&quot;)"
        ></div>
        <div
          class="slide-image"
          style="background-image: url(&quot;2piccc.avif&quot;)"
        ></div>
        <div
          class="slide-image"
          style="background-image: url(&quot;3piccccccccc.jpg&quot;)"
        ></div>
        <div
          class="slide-image"
          style="background-image: url(&quot;herop444.jpeg&quot;)"
        ></div>
      </div>

      <div class="hero-overlay"></div>

      <div class="hero-content fade-in">
        <p class="hero-label">PREMIUM REAL ESTATE</p>

        <h1 class="hero-title">
          Let us bring the <span>world</span> to your door
        </h1>

        <p class="hero-description">
          Discover exceptional properties, premium rentals, and valuable
          investments with PrimeEstates.
        </p>

        <div class="hero-buttons">
          <a href="#properties" class="hero-btn">Explore Properties</a>
          <a href="#contact" class="hero-btn secondary-btn">Contact Agent</a>
        </div>
      </div>
    </section>

    <!-- -------------**********************--------------- -->

    <!-- Section 3: Properties -->
    <section id="properties">
      <h2>Featured Properties</h2>

      <!-- Toggle Buttons -->
      <div class="toggle-buttons">
        <button id="btnSale" class="active">For Sale</button>
        <button id="btnRent">For Rent</button>
      </div>

      <!-- search bar section**************************-->
      <section id="propertySearch">
        <div class="search-header">
          <p>FIND YOUR PROPERTY</p>
          <h2>Find Your Perfect Home</h2>
          <span>Search through our available properties</span>
        </div>

        <div class="search-box">
          <div class="search-field">
            <label for="searchInput">Location</label>
            <div class="input-wrapper">
              <i class="fa-solid fa-location-dot"></i>
              <input
                type="text"
                id="searchInput"
                placeholder="Beirut, Lebanon..."
              />
            </div>
          </div>

          <div class="search-field">
            <label for="propertyType">Property Type</label>
            <div class="input-wrapper">
              <i class="fa-solid fa-house"></i>
              <select id="propertyType">
                <option value="all">Any Type</option>
                <option value="house">House</option>
                <option value="apartment">Apartment</option>
                <option value="villa">Villa</option>
                <option value="office">Office</option>
              </select>
            </div>
          </div>

          <div class="search-field">
            <label for="priceFilter">Maximum Price</label>
            <div class="input-wrapper">
              <i class="fa-solid fa-tag"></i>
              <select id="priceFilter">
                <option value="all">Any Price</option>
                <option value="200000">Under $200,000</option>
                <option value="300000">Under $300,000</option>
                <option value="500000">Under $500,000</option>
                <option value="1000000">Under $1,000,000</option>
              </select>
            </div>
          </div>

          <div class="search-field">
            <label for="bedroomFilter">Bedrooms</label>
            <div class="input-wrapper">
              <i class="fa-solid fa-bed"></i>
              <select id="bedroomFilter">
                <option value="all">Any</option>
                <option value="1">1+</option>
                <option value="2">2+</option>
                <option value="3">3+</option>
                <option value="4">4+</option>
              </select>
            </div>
          </div>

          <button id="searchBtn" class="advanced-search-btn">
            <i class="fa-solid fa-magnifying-glass"></i>
            Search
          </button>
        </div>

        <button id="clearFilters" class="clear-filters">
          Clear all filters
        </button>

        <p id="resultsCount" class="results-count">Showing all properties</p>
      </section>

      <!-- FOR SALE PROPERTIES -->
      <div id="saleProperties" class="property-list">
        <?php foreach ($saleProperties as $p): ?>
          <?php renderPropertyCard($p, "sale"); ?>
        <?php endforeach; ?>
      </div>

      <!-- FOR RENT PROPERTIES************** -->
      <div id="rentProperties" class="property-list hidden">
        <?php foreach ($rentProperties as $p): ?>
          <?php renderPropertyCard($p, "rent"); ?>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Section 4: About Us -->
    <section id="about">
      <h2>Why Choose PrimeEstates?</h2>
      <div class="card-container">
        <div class="content-card fade-in">
          <div class="card-image card-image-1"></div>
          <div class="card-text-box">
            <h3>Our Mission Statement</h3>
            <p>
              PrimeEstates is dedicated to redefining real estate through
              exceptional service, integrity, and local expertise. We strive to
              make every client's property journey seamless and successful.
            </p>
          </div>
        </div>
        <div class="content-card fade-in">
          <div class="card-image card-image-2"></div>
          <div class="card-text-box">
            <h3>Local Experts, Global Reach</h3>
            <p>
              Our team consists of highly-trained local specialists who provide
              deep market insights. We combine this expertise with a network
              that offers unparalleled opportunities, locally and abroad.
            </p>
          </div>
        </div>
        <div class="content-card fade-in">
          <div class="card-image card-image-3"></div>
          <div class="card-text-box">
            <h3>The Prime Difference</h3>
            <p>
              We believe in building lasting relationships. Our commitment is to
              treat every client like family, ensuring you receive personalized
              advice and the best possible outcome for your investment.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- statssssssssssssssssssssssssssssssssss-->
    <section id="stats">
      <div class="stats-container">
        <div class="stat">
          <span class="stat-number">250+</span>
          <span class="stat-label">Properties Listed</span>
        </div>

        <div class="stat">
          <span class="stat-number">120+</span>
          <span class="stat-label">Happy Clients</span>
        </div>

        <div class="stat">
          <span class="stat-number">15+</span>
          <span class="stat-label">Years Experience</span>
        </div>

        <div class="stat">
          <span class="stat-number">25+</span>
          <span class="stat-label">Expert Agents</span>
        </div>
      </div>
    </section>

    <!-- reviewsssssssssss--------------------------->
    <section id="testimonial">
      <h2 class="section-title">What Our Customers Think</h2>

      <div class="testimonial-wrapper">
        <div class="testimonial-card fade-in">
          <div class="stars">★★★★★</div>
          <p>
            Thank you, PrimeEstates, and especially Rachelle Fahed. I hope to
            work with you again in the near future and will definitely recommend
            you to my friends.
          </p>
          <span class="author">Jean Rhana</span>
          <span class="date">Dec 2025</span>
        </div>

        <div class="testimonial-card fade-in">
          <div class="stars">★★★★★</div>
          <p>
            A very special thanks here to Carl without whom this would have not
            been possible.
          </p>
          <span class="author">Shorfi Samnoul</span>
          <span class="date">Dec 2025</span>
        </div>

        <div class="testimonial-card fade-in">
          <div class="stars">★★★★★</div>
          <p>
            I would like to thank you and especially express my sincere
            appreciation to john who did an excellent job helping us rent this
            facility.
          </p>
          <span class="author">Hady Khoury</span>
          <span class="date">Oct 2025</span>
        </div>
      </div>
    </section>

    <!-- Section 5: Contact Us -->
    <section id="contact">
      <div class="contact-header">
        <p class="contact-label">GET IN TOUCH</p>
        <h2>Let's Find Your Perfect Property</h2>
        <p>
          Have a question about a property or looking for your next investment?
          Our team is ready to help.
        </p>
      </div>

      <div class="contact-container">
        <!-- Contact Information -->
        <div class="contact-info">
          <h3>Contact Us</h3>

          <p class="contact-intro">
            Whether you're buying, renting, or investing, our team is here to
            guide you through every step.
          </p>

          <div class="contact-item">
            <div class="contact-icon">
              <i class="fa-solid fa-location-dot"></i>
            </div>

            <div>
              <h4>Our Location</h4>
              <p>Beirut, Lebanon</p>
            </div>
          </div>

          <div class="contact-item">
            <div class="contact-icon">
              <i class="fa-solid fa-envelope"></i>
            </div>

            <div>
              <h4>Email Us</h4>
              <p>info@primeestates.com</p>
            </div>
          </div>

          <div class="contact-item">
            <div class="contact-icon">
              <i class="fa-solid fa-phone"></i>
            </div>

            <div>
              <h4>Call Us</h4>
              <p>+961 1 234 567</p>
            </div>
          </div>

          <div class="contact-hours">
            <h4>Office Hours</h4>
            <p>Monday – Friday: 9:00 AM – 6:00 PM</p>
            <p>Saturday: 9:00 AM – 2:00 PM</p>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="contact-form-box">
          <h3>Send Us a Message</h3>

          <form id="contactForm">
            <div class="form-row">
              <div class="form-group">
                <label for="contactName">Full Name</label>
                <input
                  type="text"
                  id="contactName"
                  placeholder="Your name"
                  required
                />
              </div>

              <div class="form-group">
                <label for="contactEmail">Email</label>
                <input
                  type="email"
                  id="contactEmail"
                  placeholder="Your email"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="contactSubject">Subject</label>
              <input
                type="text"
                id="contactSubject"
                placeholder="What can we help you with?"
                required
              />
            </div>

            <div class="form-group">
              <label for="contactMessage">Message</label>
              <textarea
                id="contactMessage"
                name="message"
                rows="5"
                placeholder="Write your message..."
                required
              ></textarea>
            </div>

            <button type="submit" class="contact-submit">
              <i class="fa-solid fa-paper-plane"></i>
              Send Message
            </button>
          </form>
        </div>
      </div>
    </section>

    <!-- Footerrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrr -->

    <footer>
      <div class="footer-container">
        <!-- Company -->
        <div class="footer-company">
          <h2>PRIMEESTATES</h2>

          <p>
            Discover exceptional properties and find a place that feels like
            home. Your trusted partner in real estate.
          </p>

          <div class="footer-social">
            <a href="#">
              <i class="fab fa-facebook-f"></i>
            </a>

            <a href="#">
              <i class="fab fa-instagram"></i>
            </a>

            <a href="#">
              <i class="fab fa-linkedin-in"></i>
            </a>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-column">
          <h3>Quick Links</h3>

          <a href="#hero">Home</a>
          <a href="#properties">Properties</a>
          <a href="#about">About Us</a>
          <a href="#testimonial">Testimonials</a>
          <a href="#contact">Contact</a>
        </div>

        <!-- Properties -->
        <div class="footer-column">
          <h3>Properties</h3>

          <a href="#properties">Luxury Homes</a>
          <a href="#properties">Apartments</a>
          <a href="#properties">Villas</a>
          <a href="#properties">Properties for Rent</a>
        </div>

        <!-- Contact -->
        <div class="footer-column footer-contact">
          <h3>Contact Us</h3>

          <p>
            <i class="fas fa-map-marker-alt"></i>
            Beirut, Lebanon
          </p>

          <p>
            <i class="fas fa-phone"></i>
            +961 70 123 456
          </p>

          <p>
            <i class="fas fa-envelope"></i>
            info@primeestates.com
          </p>

          <p>
            <i class="fas fa-clock"></i>
            Mon - Sat: 9AM - 6PM
          </p>
        </div>
      </div>

      <!-- Bottom -->

      <div class="footer-bottom">
        <p>© 2026 PrimeEstates. All rights reserved.</p>

        <p>
          <a href="#">Privacy Policy</a>
          &nbsp; | &nbsp;
          <a href="#">Terms of Service</a>
        </p>
      </div>
    </footer>

    <!-- Modal pops up -->
    <div id="propertyModal" class="modal hidden">
      <div class="modal-content">
        <button class="close-modal">&times;</button>

        <div class="modal-image-container">
          <img id="modalImg" src="" alt="Property Image" />
          <span class="modal-badge">PROPERTY</span>
        </div>

        <div class="modal-info">
          <p class="modal-location" id="modalLocation"></p>

          <h3 id="modalTitle"></h3>

          <p class="modal-price" id="modalPrice"></p>

          <div id="modalIcons" class="modal-icons"></div>

          <div class="modal-details">
            <div>
              <span class="detail-label">Size</span>
              <span id="modalSize"></span>
            </div>

            <div>
              <span class="detail-label">Bedrooms</span>
              <span id="modalBedrooms"></span>
            </div>

            <div>
              <span class="detail-label">Features</span>
              <span id="modalExtras"></span>
            </div>
          </div>

          <div class="modal-description">
            <h4>About this property</h4>
            <p id="modalDescription"></p>
          </div>
          <div class="modal-actions">
            <button id="contactAgentBtn" class="modal-contact-btn">
              <i class="fa-solid fa-phone"></i>
              Contact Agent
            </button>

            <button id="inquireBtn" class="modal-inquire-btn">
              <i class="fas fa-envelope"></i>
              Inquire
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- REGISTER / LOGIN MODALllllllllllllllllllllllllllll -->
    <div id="authModal" class="auth-modal hidden">
      <div class="auth-box">
        <button class="auth-close" id="authClose">&times;</button>

        <div class="auth-header">
          <h2 id="authTitle">Create Account</h2>
          <p id="authSubtitle">
            Create an account to save your favorite properties.
          </p>
        </div>

        <!-- REGISTER FORM -->
        <form id="registerForm" action="php/register.php" method="POST">
          <div class="auth-field">
            <label>Full Name</label>
            <input
              type="text"
              id="registerName"
              name="fullname"
              placeholder="Enter your name"
              required
            />
          </div>

          <div class="auth-field">
            <label>Email</label>
            <input
              type="email"
              id="registerEmail"
              name="email"
              placeholder="Enter your email"
              required
            />
          </div>

          <div class="auth-field">
            <label>Password</label>
            <input
              type="password"
              id="registerPassword"
               name="password"
              placeholder="Create a password"
              required
            />
          </div>

          <button type="submit" class="auth-submit">Create Account</button>

          <p class="auth-switch">
            Already have an account?
            <button type="button" id="showLogin">Sign In</button>
          </p>
        </form>

        <!-- LOGIN FORM -->

        <form id="loginForm" class="hidden">
          <div class="auth-field">
            <label>Email</label>
            <input
              type="email"
              id="loginEmail"
              placeholder="Enter your email"
              required
            />
          </div>

          <div class="auth-field">
            <label>Password</label>
            <input
              type="password"
              id="loginPassword"
              placeholder="Enter your password"
              required
            />
          </div>

          <button type="submit" class="auth-submit">Sign In</button>

          <p class="auth-switch">
            <button type="button" id="showForgotPassword" style="color:#777; font-weight:500;">
              Forgot your password?
            </button>
          </p>

          <p class="auth-switch">
            Don't have an account?
            <button type="button" id="showRegister">Register</button>
          </p>
        </form>
      </div>
    </div>

    <!-- FORGOT PASSWORD -->
    <div id="forgotPasswordModal" class="auth-modal hidden">
      <div class="auth-box">
        <button class="auth-close" id="forgotPasswordClose">&times;</button>

        <div class="auth-header">
          <h2>Reset Your Password</h2>
          <p>Enter your email and we'll send you a reset link.</p>
        </div>

        <form id="forgotPasswordForm">
          <div class="auth-field">
            <label>Email</label>
            <input type="email" id="forgotPasswordEmail" placeholder="Enter your email" required />
          </div>

          <button type="submit" class="auth-submit">Send Reset Link</button>
        </form>
      </div>
    </div>

    <!-- ADD PROPERTY LOGIN POPUP -->
    <div id="addPropertyModal" class="auth-modal hidden">
      <div class="auth-modal-content">
        <button class="auth-close" id="addPropertyClose">&times;</button>

        <div class="auth-icon">
          <i class="fa-solid fa-house"></i>
        </div>

        <h2>List Your Property</h2>

        <p>
          Sign in or create an account to add your property to PrimeEstates.
        </p>

        <button class="auth-btn" id="signInBtn">
          <i class="fa-solid fa-right-to-bracket"></i>
          Sign In
        </button>

        <button class="auth-btn secondary-auth" id="signUpBtn">
          <i class="fa-solid fa-user-plus"></i>
          Create Account
        </button>
      </div>
    </div>

    <!-- ADD PROPERTY FORM -->
    <div id="propertyFormModal" class="auth-modal hidden">
      <div class="property-form-box">
        <button class="auth-close" id="propertyFormClose">&times;</button>

        <div class="property-form-header">
          <h2>Add Your Property</h2>
          <p>
            Include as much detail as possible to help buyers find your
            property.
          </p>
        </div>

        <form id="propertyForm">
          <!-- IMAGES -->
          <div class="form-section">
            <h3>Property Images</h3>

            <label>Upload Up to 10 Files</label>

            <input type="file" id="propertyImages" accept="image/*" multiple />

            <small>You can select up to 10 images.</small>

            <div id="imagePreview" class="image-preview"></div>
          </div>

          <!-- VIDEO -->
          <div class="form-section">
            <h3>Property Video</h3>

            <label for="videoLink">Add Video Link</label>

            <input
              type="url"
              id="videoLink"
              placeholder="https://youtube.com/..."
            />
          </div>

          <!-- BASIC INFORMATION -->
          <div class="form-section">
            <h3>Basic Information</h3>

            <div class="property-form-grid">
              <div class="property-form-group">
                <label for="propertyLocation">Location</label>

                <input
                  type="text"
                  id="propertyLocation"
                  placeholder="Beirut, Lebanon"
                  required
                />
              </div>

              <div class="property-form-group">
                <label for="propertyTypeForm"> Property Type </label>

                <select id="propertyTypeForm" required>
                  <option value="">Select Type</option>

                  <option value="apartment">Apartment</option>

                  <option value="villa">Villa</option>

                  <option value="land">Land</option>

                  <option value="house">House</option>

                  <option value="commercial">Commercial</option>

                  <option value="chalet">Chalet & Cabin</option>

                  <option value="building">Buildings & Multiple Units</option>
                </select>
              </div>

              <div class="property-form-group">
                <label for="propertyPrice"> Price (USD) </label>

                <input
                  type="number"
                  id="propertyPrice"
                  placeholder="250000"
                  required
                />
              </div>

              <div class="property-form-group">
                <label for="propertyTitle"> Title </label>

                <input
                  type="text"
                  id="propertyTitle"
                  placeholder="Modern Family House"
                  required
                />
              </div>

              <div class="property-form-group">
                <label for="propertyArea"> Area (m²) </label>

                <input
                  type="number"
                  id="propertyArea"
                  placeholder="180"
                  required
                />
              </div>
            </div>
          </div>

          <!-- SALE OR RENTAL -->
          <div class="form-section">
            <h3>Sale or Rental</h3>

            <div class="radio-options">
              <label>
                <input type="radio" name="listingType" value="sale" checked />
                Sale
              </label>

              <label>
                <input type="radio" name="listingType" value="rent" />
                Rental
              </label>
            </div>
          </div>

          <!-- PAYMENT TYPE -->
          <div class="form-section">
            <h3>Payment Type</h3>

            <div class="checkbox-options">
              <label>
                <input type="checkbox" name="paymentType" value="lease" />
                Lease to Own
              </label>

              <label>
                <input
                  type="checkbox"
                  name="paymentType"
                  value="installments"
                />
                Installments Available
              </label>

              <label>
                <input type="checkbox" name="paymentType" value="cash" />
                Cash
              </label>

              <label>
                <input type="checkbox" name="paymentType" value="cheque" />
                Cheque
              </label>
            </div>
          </div>

          <!-- PROPERTY DETAILS -->
          <div class="form-section">
            <h3>Property Details</h3>

            <div class="property-form-grid">
              <div class="property-form-group">
                <label for="bedrooms"> Number of Bedrooms </label>

                <input type="number" id="bedrooms" min="0" placeholder="3" />
              </div>

              <div class="property-form-group">
                <label for="bathrooms"> Number of Bathrooms </label>

                <input type="number" id="bathrooms" min="0" placeholder="2" />
              </div>

              <div class="property-form-group">
                <label for="floor"> Floor </label>

                <input type="number" id="floor" placeholder="4" />
              </div>

              <div class="property-form-group">
                <label for="parking"> Number of Parking Spaces </label>

                <input type="number" id="parking" min="0" placeholder="2" />
              </div>

              <div class="property-form-group">
                <label for="terrace"> Terrace Size (m²) </label>

                <input type="number" id="terrace" placeholder="20" />
              </div>

              <div class="property-form-group">
                <label for="garden"> Garden Size (m²) </label>

                <input type="number" id="garden" placeholder="100" />
              </div>

              <div class="property-form-group">
                <label for="monthlyFee"> Expected Monthly Fee </label>

                <input type="number" id="monthlyFee" placeholder="200" />
              </div>

              <div class="property-form-group">
                <label for="yearBuilt"> Year Built </label>

                <input type="number" id="yearBuilt" placeholder="2025" />
              </div>
            </div>
          </div>

          <!-- FURNISHED -->
          <div class="form-section">
            <h3>Furnished</h3>

            <div class="radio-options">
              <label>
                <input
                  type="radio"
                  name="furnished"
                  value="unfurnished"
                  checked
                />
                Unfurnished
              </label>

              <label>
                <input type="radio" name="furnished" value="fully" />
                Fully Furnished
              </label>

              <label>
                <input type="radio" name="furnished" value="appliances" />
                Appliances Only
              </label>
            </div>
          </div>

          <!-- CONDITION -->
          <div class="form-section">
            <h3>Condition</h3>

            <div class="radio-options">
              <label>
                <input
                  type="radio"
                  name="condition"
                  value="under-construction"
                />
                Under Construction
              </label>

              <label>
                <input type="radio" name="condition" value="ready" checked />
                Ready
              </label>
            </div>
          </div>

          <!-- OWNERSHIP -->
          <div class="form-section">
            <h3>Ownership</h3>

            <div class="radio-options">
              <label>
                <input type="radio" name="ownership" value="private" checked />
                Private
              </label>

              <label>
                <input type="radio" name="ownership" value="contractor" />
                Contractor
              </label>
            </div>
          </div>

          <!-- AMENITIES -->
          <div class="form-section">
            <h3>Amenities</h3>

            <div class="checkbox-options">
              <label>
                <input type="checkbox" name="amenities" value="pool" />
                Swimming Pool
              </label>

              <label>
                <input type="checkbox" name="amenities" value="gym" />
                Gym
              </label>

              <label>
                <input type="checkbox" name="amenities" value="elevator" />
                Elevator
              </label>

              <label>
                <input type="checkbox" name="amenities" value="security" />
                Security
              </label>

              <label>
                <input type="checkbox" name="amenities" value="balcony" />
                Balcony
              </label>

              <label>
                <input type="checkbox" name="amenities" value="parking" />
                Parking
              </label>
            </div>
          </div>

          <!-- REFERENCE -->
          <div class="form-section">
            <label for="propertyReference"> Reference </label>

            <input type="text" id="propertyReference" placeholder="PE-001" />
          </div>

          <!-- DESCRIPTION -->
          <div class="form-section">
            <label for="propertyDescription"> Description </label>

            <textarea
              id="propertyDescription"
              rows="6"
              placeholder="Describe your property..."
              required
            ></textarea>
          </div>

          <!-- SUBMIT -->
          <button type="submit" class="property-submit-btn">
            <i class="fa-solid fa-house-circle-check"></i>
            Submit Property
          </button>
        </form>

        <div
          id="propertySuccessMessage"
          class="property-success-message hidden"
        >
          <i class="fa-solid fa-circle-check"></i>
          <h3>Property Submitted for Review</h3>
          <p id="propertySuccessText">
            Your property has been received, but it won't go live until an
            agent reviews it. Send us a quick message so an agent can follow
            up and approve your listing.
          </p>
          <button type="button" id="notifyAgentBtn" class="property-submit-btn">
            <i class="fa-solid fa-paper-plane"></i>
            Message an Agent Now
          </button>
          <button type="button" id="successCloseBtn">Close</button>
        </div>
      </div>
    </div>

    <div id="myPropertiesModal" class="modal hidden">
      <div class="my-properties-box">
        <button class="close-modal" id="myPropertiesClose">&times;</button>

        <h2>My Properties</h2>

        <div id="myPropertiesList" class="my-properties-list">
          <p>Loading your properties...</p>
        </div>
      </div>
    </div>

    <!-- EDIT PROPERTY -->
    <div id="editPropertyModal" class="modal hidden">
      <div class="my-properties-box">
        <button class="close-modal" id="editPropertyClose">&times;</button>

        <h2>Edit Property</h2>
        <p style="color:#777; font-size:0.9rem; margin-bottom:15px;">
          Saving changes sends this listing back to admin review before it's public again.
        </p>

        <form id="editPropertyForm">
          <input type="hidden" id="editPropertyId" />

          <div class="form-group">
            <label for="editPropertyTitle">Title</label>
            <input type="text" id="editPropertyTitle" required />
          </div>

          <div class="form-group">
            <label for="editPropertyPrice">Price (USD)</label>
            <input type="number" id="editPropertyPrice" required />
          </div>

          <div class="form-group">
            <label for="editPropertyDescription">Description</label>
            <textarea id="editPropertyDescription" rows="5" required></textarea>
          </div>

          <div class="form-group">
            <label for="editPropertyImages">Replace Images (optional)</label>
            <input type="file" id="editPropertyImages" accept="image/*" multiple />
            <small>Leave empty to keep the current images.</small>
          </div>

          <button type="submit" class="auth-submit">Save Changes</button>
        </form>
      </div>
    </div>


<div class="favorites-overlay hidden" id="favoritesModal">
  <div class="favorites-box">
    <span class="favorites-close" id="favoritesClose">&times;</span>
    <h2>My Favorites</h2>
    <div id="favoritesList"></div>
  </div>
</div>



    <!--jssssssssssssssss-->

    <script>
      // Sent with every state-changing fetch request so the server
      // can confirm it actually came from this page's session.
      window.CSRF_TOKEN = "<?php echo htmlspecialchars(getCsrfToken()); ?>";
    </script>
    <script src="js/propertyToggle.js"></script>
<script src="js/propertyPagination.js"></script>
<script src="js/animations.js"></script>
<script src="js/smoothScroll.js"></script>
<script src="js/propertyModal.js"></script>
<script src="js/heroSlider.js"></script>
<script src="js/contact.js"></script>
<script src="js/search.js"></script>
<script src="js/loadingState.js"></script>
<script src="js/escapeHtml.js"></script>
<script src="js/toast.js"></script>
<script src="js/auth.js"></script>
<script src="js/Favorites.js"></script>
<script src="js/addProperty.js"></script>
<script src="js/mobileMenu.js"></script>
<script src="js/main.js"></script>
  </body>
</html>