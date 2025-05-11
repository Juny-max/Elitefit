<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EliteFit - Premium Fitness Experience</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6C63FF;
            --secondary: #FF6584;
            --dark: #2D3748;
            --light: #F7FAFC;
            --accent: #4FD1C5;
            --gradient: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark);
            background-color: var(--light);
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px 0;
            z-index: 1000;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            background-color: rgba(247, 250, 252, 0.8);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        header.scrolled {
            padding: 15px 0;
            background-color: rgba(247, 250, 252, 0.95);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 28px;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 10px;
            font-size: 32px;
        }

        .nav-links {
            display: flex;
            list-style: none;
        }

        .nav-links li {
            margin-left: 30px;
        }

        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient);
            transition: width 0.3s;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .cta-button {
            background: var(--gradient);
            color: white;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
            border: none;
            outline: none;
            text-decoration: none;
        }

        .cta-button:focus, .cta-button:active {
            outline: none;
            border: none;
            text-decoration: none;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(108, 99, 255, 0.4);
        }

        .mobile-menu {
            display: none;
            font-size: 24px;
            cursor: pointer;
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            background: url('https://images.pexels.com/photos/1954524/pexels-photo-1954524.jpeg') no-repeat center center/cover;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
            width: 100%;
            padding: 0 20px;
            text-align: center;
            color: white;
            transform: translateY(50px);
            opacity: 0;
            animation: slideUp 1s forwards 0.5s;
        }

        @keyframes slideUp {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .hero h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
            line-height: 1.6;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        /* Section Styles */
        section {
            padding: 100px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .section-title p {
            color: #718096;
            max-width: 700px;
            margin: 0 auto;
            font-size: 1.1rem;
        }

        /* About Section */
        .about-content {
            display: flex;
            align-items: center;
            gap: 50px;
        }

        .about-text {
            flex: 1;
        }

        .about-text h3 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: var(--dark);
        }

        .about-text p {
            margin-bottom: 20px;
            line-height: 1.7;
            color: #4A5568;
        }

        .about-image {
            flex: 1;
            position: relative;
        }

        .about-image img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .about-stats {
            display: flex;
            margin-top: 30px;
            gap: 30px;
        }

        .stat-item {
            text-align: center;
            flex: 1;
        }

        .stat-item i {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .stat-item h4 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .stat-item p {
            color: #718096;
            font-size: 0.9rem;
        }

        /* Features Section */
        .features {
            background-color: #F8F9FA;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .feature-icon i {
            font-size: 1.8rem;
            color: white;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--dark);
        }

        .feature-card p {
            color: #718096;
            line-height: 1.6;
        }

        /* Testimonials Section */
        .testimonials-slider {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin: 0 15px;
            text-align: center;
        }

        .testimonial-card .quote {
            font-size: 1.2rem;
            color: #4A5568;
            font-style: italic;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .client-info {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .client-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }

        .client-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .client-details h4 {
            font-size: 1.2rem;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .client-details p {
            color: #718096;
            font-size: 0.9rem;
        }

        .rating {
            color: #F6AD55;
            margin-top: 5px;
        }

        /* Contact Section */
        .contact {
            background-color: #F8F9FA;
        }

        .contact-container {
            display: flex;
            gap: 50px;
        }

        .contact-info {
            flex: 1;
        }

        .contact-info h3 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--dark);
        }

        .contact-info p {
            margin-bottom: 30px;
            color: #718096;
            line-height: 1.7;
        }

        .contact-details {
            margin-bottom: 30px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .contact-item i {
            width: 40px;
            height: 40px;
            background: var(--gradient);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1rem;
        }

        .contact-item span {
            color: #4A5568;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.2rem;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .social-links a:hover {
            background: var(--gradient);
            color: white;
            transform: translateY(-3px);
        }

        .contact-form {
            flex: 1;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 60px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: white;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #CBD5E0;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #A0AEC0;
            font-size: 0.9rem;
        }

        /* Animations */
        [data-aos="fade-up"] {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s ease;
        }

        [data-aos="fade-up"].aos-animate {
            opacity: 1;
            transform: translateY(0);
        }

        [data-aos="fade-left"] {
            opacity: 0;
            transform: translateX(50px);
            transition: all 0.8s ease;
        }

        [data-aos="fade-left"].aos-animate {
            opacity: 1;
            transform: translateX(0);
        }

        [data-aos="fade-right"] {
            opacity: 0;
            transform: translateX(-50px);
            transition: all 0.8s ease;
        }

        [data-aos="fade-right"].aos-animate {
            opacity: 1;
            transform: translateX(0);
        }

        /* Success Message Styles */
        .success-message {
            display: none;
            background: #e8f5e9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
        }

        .success-message.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .success-message i {
            color: #4caf50;
            font-size: 2.5rem;
            margin-bottom: 15px;
            animation: bounce 0.5s ease;
        }

        .success-message p {
            color: #2e7d32;
            margin: 0;
            font-size: 1rem;
        }

        @keyframes bounce {
            0% { transform: scale(0.8); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* Intro Screen */
        .intro {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: var(--light);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            overflow: hidden;
            transform: translateY(0);
            opacity: 1;
            transition: transform 1.2s cubic-bezier(0.65, 0, 0.35, 1), opacity 1.2s ease-in-out;
        }
        .intro-bg-anim {
            position: absolute;
            width: 100vw;
            height: 100vh;
            top: 0; left: 0;
            background: radial-gradient(circle at 60% 40%, #6C63FF22 0%, transparent 70%),
                        radial-gradient(circle at 30% 70%, #FF658422 0%, transparent 60%),
                        linear-gradient(135deg, #6C63FF11 0%, #FF658411 100%);
            z-index: 1;
            animation: intro-bg-move 2.5s cubic-bezier(.4,1.6,.6,1) forwards;
        }
        @keyframes intro-bg-move {
            from { filter: blur(0px) brightness(1); }
            to { filter: blur(8px) brightness(1.2); }
        }
        .intro-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 18px;
        }
        .intro-logo {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 8px;
            animation: intro-logo-pop 1s cubic-bezier(.4,1.6,.6,1);
        }
        @keyframes intro-logo-pop {
            0% { transform: scale(0.5) rotate(-30deg); opacity: 0; }
            60% { transform: scale(1.2) rotate(10deg); opacity: 1; }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }
        .intro h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 8vw;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: 2px;
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
            margin-bottom: 0;
        }
        .intro h1::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(108, 99, 255, 0.3), transparent);
            transform: translateX(-100%);
            animation: shine 3s infinite;
        }
        .intro-tagline {
            font-size: 1.3rem;
            font-weight: 500;
            color: var(--secondary);
            letter-spacing: 1px;
            margin-bottom: 16px;
            text-shadow: 0 2px 8px #fff8;
        }
        .intro-loader {
            display: flex;
            gap: 7px;
            margin-top: 18px;
        }
        .intro-loader span {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary);
            opacity: 0.7;
            animation: intro-loader-bounce 1.2s infinite alternate;
        }
        .intro-loader span:nth-child(2) {
            animation-delay: 0.2s;
            background: var(--secondary);
        }
        .intro-loader span:nth-child(3) {
            animation-delay: 0.4s;
            background: var(--accent);
        }
        @keyframes intro-loader-bounce {
            from { transform: translateY(0); opacity: 0.7; }
            to { transform: translateY(-18px); opacity: 1; }
        }
        .intro.hide {
            transform: translateY(-100%);
            opacity: 0;
            pointer-events: none;
        }
        /* Main page zoom-in effect */
        body.main-fade-slide-in {
            animation: main-fade-slide-in 1.7s cubic-bezier(.45,1.4,.6,1);
        }
        @keyframes main-fade-slide-in {
            0% { opacity: 0; transform: translateY(40px) scale(0.98); }
            100% { opacity: 1; transform: none; }
        }

        @keyframes shine {
            100% {
                transform: translateX(100%);
            }
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .about-content {
                flex-direction: column;
            }
            
            .contact-container {
                flex-direction: column;
            }
            
            .hero h1 {
                font-size: 3rem;
            }
        }

        @media (max-width: 768px) {
            .mobile-menu {
                display: block;
            }
            
            .nav-links {
                position: fixed;
                top: 80px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 80px);
                background: white;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                transition: all 0.5s ease;
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            }
            
            .nav-links.active {
                left: 0;
            }
            
            .nav-links li {
                margin: 15px 0;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .about-stats {
                flex-direction: column;
                gap: 20px;
            }

            .intro h1 {
                font-size: 15vw;
            }
        }

        @media (max-width: 576px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .glass-card {
                padding: 30px 20px;
            }
            
            section {
                padding: 60px 0;
            }
        }
    </style>
</head>
<body>
    <div class="intro">
        <div class="intro-bg-anim"></div>
        <div class="intro-content">
            <div class="intro-logo">
                <i class="fas fa-dumbbell"></i>
            </div>
            <h1>ELITEFIT</h1>
            <p class="intro-tagline">Premium Fitness Experience</p>
            <div class="intro-loader">
                <span></span><span></span><span></span>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header id="header">
        <div class="container">
            <nav>
                <a href="#" class="logo"><i class="fas fa-dumbbell"></i>EliteFit</a>
                <ul class="nav-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#testimonials">Testimonials</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <a href="index.php" class="cta-button">Sign In</a>
                <div class="mobile-menu">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <div class="glass-card">
                <h1>Transform Your Body, Elevate Your Life</h1>
                <p>Join EliteFit today and experience personalized training programs, state-of-the-art facilities, and a community that supports your fitness journey.</p>
                <a href="register.php" class="cta-button">Register Now</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>About EliteFit</h2>
                <p>Discover what makes us different and how we can help you achieve your fitness goals</p>
            </div>
            <div class="about-content">
                <div class="about-text" data-aos="fade-right">
                    <h3>Premium Fitness Experience</h3>
                    <p>EliteFit was founded with one goal in mind: to provide a premium fitness experience that delivers real results. Our team of certified trainers and nutrition experts work together to create customized programs tailored to your unique needs.</p>
                    <p>We combine cutting-edge training techniques with proven methodologies to help you build strength, improve mobility, and enhance overall wellness.</p>
                    <div class="about-stats">
                        <div class="stat-item">
                            <i class="fas fa-users"></i>
                            <h4><span class="count" data-target="5000">0</span>+</h4>
                            <p>Happy Members</p>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-dumbbell"></i>
                            <h4><span class="count" data-target="50">0</span>+</h4>
                            <p>Training Programs</p>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-trophy"></i>
                            <h4><span class="count" data-target="100">0</span>+</h4>
                            <p>Success Stories</p>
                        </div>
                    </div>
                </div>
                <div class="about-image" data-aos="fade-left">
                    <img src="https://images.pexels.com/photos/841130/pexels-photo-841130.jpeg" alt="Fitness Training">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Our Features</h2>
                <p>Explore what EliteFit has to offer to help you reach your fitness potential</p>
            </div>
            <div class="features-grid">
                <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Personalized Plans</h3>
                    <p>Get customized workout and nutrition plans tailored to your goals, fitness level, and schedule.</p>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Progress Tracking</h3>
                    <p>Monitor your progress with detailed analytics and adjust your program as you improve.</p>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h3>Virtual Training</h3>
                    <p>Access live and on-demand workouts from anywhere with our virtual training platform.</p>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h3>Nutrition Guidance</h3>
                    <p>Receive expert nutrition advice and meal plans to complement your fitness routine.</p>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Community Support</h3>
                    <p>Join a supportive community of like-minded individuals working toward similar goals.</p>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3>Expert Coaches</h3>
                    <p>Learn from certified trainers with years of experience helping clients achieve results.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Success Stories</h2>
                <p>Hear from our members who transformed their lives with EliteFit</p>
            </div>
            <div class="testimonials-slider" data-aos="fade-up">
                <div class="testimonial-card">
                    <p class="quote">"EliteFit completely changed my approach to fitness. The personalized training and nutrition plan helped me lose 25 pounds and gain muscle I never thought possible!"</p>
                    <div class="client-info">
                        <div class="client-image">
                            <img src="https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg" alt="Sarah J.">
                        </div>
                        <div class="client-details">
                            <h4>Emerald O.</h4>
                            <p>Member since 2021</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Get In Touch</h2>
                <p>Have questions? We're here to help you start your fitness journey</p>
            </div>
            <div class="contact-container">
                <div class="contact-info" data-aos="fade-right">
                    <h3>Contact Information</h3>
                    <p>Fill out the form or reach out to us directly. Our team is ready to answer any questions you have about our programs and membership options.</p>
                    <div class="contact-details">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>East Legon, Boundary Road, Accra, Ghana.</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone-alt"></i>
                            <span>+233 (0)549837285</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>junyappteam@gmail.com</span>
                        </div>
                    </div>
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="contact-form" data-aos="fade-left">
                    <form id="contactForm">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" placeholder="Enter your name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" placeholder="Enter your phone number">
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="What's this about?">
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" placeholder="How can we help you?" required></textarea>
                        </div>
                        <button type="submit" class="cta-button">Send Message</button>
                    </form>
                    <div id="successMessage" class="success-message">
                        <i class="fas fa-check-circle"></i>
                        <p>Dear <span id="userName"></span>, your message has been sent successfully. We'll contact you shortly.</p>
                    </div>
                    <div id="errorMessage" class="alert alert-error" style="display: none;">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>There was an error sending your message. Please try again later.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>EliteFit</h3>
                    <p>Premium fitness solutions designed to help you achieve your health and wellness goals through personalized training and expert guidance.</p>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#testimonials">Testimonials</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Programs</h3>
                    <ul class="footer-links">
                        <li><a href="#">Personal Training</a></li>
                        <li><a href="#">Group Classes</a></li>
                        <li><a href="#">Nutrition Plans</a></li>
                        <li><a href="#">Online Coaching</a></li>
                        <li><a href="#">Corporate Wellness</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Support</h3>
                    <ul class="footer-links">
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Membership</a></li>
                        <li><a href="#">Help Center</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 EliteFit. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/emailjs-com@3.2.0/dist/email.min.js"></script>
    <script>
        // Modern Intro Screen Animation
        $(document).ready(function() {
            // Start transition immediately after DOM ready
            $('body').addClass('main-fade-slide-in');
            $('.intro').addClass('hide');
            // Wait for both animations to finish, then clean up
            setTimeout(function() {
                $('.intro').remove();
                $('body').removeClass('main-fade-slide-in');
            }, 1700); // match new CSS animation duration
        });

        // Initialize EmailJS with your User ID
        (function() {
            emailjs.init("J7eH552YZ1UzGixM4"); // Replace with your EmailJS user ID
        })();

        // Initialize AOS (Animate On Scroll) library
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Mobile Menu Toggle Functionality
        $('.mobile-menu').click(function() {
            $('.nav-links').toggleClass('active');
            $(this).find('i').toggleClass('fa-times fa-bars');
        });

        // Smooth Scrolling for All Navigation Links
        $('a[href*="#"]').not('[href="#"]').not('[href="#0"]').not('[href="index.php"]').not('[href="register.php"]').click(function(e) {
            if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') && 
                location.hostname === this.hostname) {
                
                let target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                
                if (target.length) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 800, 'swing', function() {
                        window.location.hash = target.selector;
                    });
                    
                    // Close mobile menu if open
                    $('.nav-links').removeClass('active');
                    $('.mobile-menu i').removeClass('fa-times').addClass('fa-bars');
                }
            }
        });

        // Header Scroll Effect - Adds shadow when scrolling down
        $(window).scroll(function() {
            if ($(this).scrollTop() > 100) {
                $('#header').addClass('scrolled');
            } else {
                $('#header').removeClass('scrolled');
            }
        });

        // Contact Form Submission with EmailJS
        $('#contactForm').submit(function(e) {
            e.preventDefault();
            
            // Get form values
            const formData = {
                name: $('#name').val().trim(),
                email: $('#email').val().trim(),
                phone: $('#phone').val().trim(),
                subject: $('#subject').val().trim(),
                message: $('#message').val().trim()
            };
            
            // Simple validation
            if (!formData.name || !formData.email || !formData.message) {
                alert('Please fill in all required fields');
                return;
            }

            // Show loading state
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Sending...').prop('disabled', true);
            
            // Send email using EmailJS
            emailjs.send('service_zv3753o', 'template_jvz2qzq', formData)
                .then(function(response) {
                    console.log('SUCCESS!', response.status, response.text);
                    
                    // Display success message
                    $('#userName').text(formData.name.split(' ')[0]); // First name only
                    const successMessage = $('#successMessage');
                    
                    // Hide form and show success message
                    $('#contactForm').hide();
                    $('#errorMessage').hide();
                    successMessage.css('display', 'block');
                    
                    setTimeout(() => {
                        successMessage.addClass('show');
                    }, 10);
                    
                    // Reset form after animation
                    setTimeout(() => {
                        $('#contactForm')[0].reset();
                        submitBtn.html('Send Message').prop('disabled', false);
                    }, 500);
                    
                    // Hide message and show form after 5 seconds
                    setTimeout(() => {
                        successMessage.removeClass('show');
                        setTimeout(() => {
                            successMessage.hide();
                            $('#contactForm').show();
                        }, 500);
                    }, 5000);

            

                }, function(error) {
                    console.log('FAILED...', error);
                    submitBtn.html('Send Message').prop('disabled', false);
                    $('#errorMessage').fadeIn().delay(5000).fadeOut();
                });
        });

        // Hero Section Button Click Handlers
        $('.hero .cta-button').click(function(e) {
            // Allow default behavior for register button
            if ($(this).attr('href') === 'register.php') {
                return;
            }
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('#contact').offset().top - 80
            }, 800);
        });

        // Navbar CTA Button Click Handler
        $('nav .cta-button').click(function(e) {
            // Allow default behavior for sign-in button
            if ($(this).attr('href') === 'index.php') {
                return;
            }
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('#contact').offset().top - 80
            }, 800);
        });

        // Add smooth hover effects to all buttons
        $('.cta-button').hover(
            function() {
                $(this).css('transform', 'translateY(-3px)');
            },
            function() {
                $(this).css('transform', 'translateY(0)');
            }
        );

        // Initialize any sliders (testimonials, etc.)
        $(document).ready(function() {
            // Ensure the success message is hidden on page load
            $('#successMessage').hide();
            
            // Intro animation
            setTimeout(function() {
                document.querySelector('.intro').classList.add('hide');
                
                // Enable scrolling after intro animation
                document.body.style.overflow = 'auto';
            }, 1500); // Reduced from 2500 to 1500 for better UX

            // Hide intro immediately if user tries to scroll
            window.addEventListener('scroll', function() {
                if (window.scrollY > 0 && !document.querySelector('.intro').classList.contains('hide')) {
                    document.querySelector('.intro').classList.add('hide');
                    document.body.style.overflow = 'auto';
                }
            }, { once: true });
        });

        // Animated Counters for About Section
        function animateAboutCounters() {
            const counters = document.querySelectorAll('.about-stats .count');
            counters.forEach(counter => {
                const updateCount = () => {
                    const target = +counter.getAttribute('data-target');
                    const current = +counter.innerText.replace(/,/g, '');
                    const increment = Math.ceil(target / 120);
                    if (current < target) {
                        counter.innerText = (current + increment).toLocaleString();
                        setTimeout(updateCount, 16);
                    } else {
                        counter.innerText = target.toLocaleString();
                    }
                };
                updateCount();
            });
        }
        window.addEventListener('DOMContentLoaded', animateAboutCounters);
    </script>
</body>
</html>