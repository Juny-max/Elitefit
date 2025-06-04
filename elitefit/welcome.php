<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EliteFit - Premium Fitness Experience</title>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
    
    <!-- Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Animation Libraries -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1A1A1A;
            --primary-dark: #0D0D0D;
            --primary-light: #333333;
            --secondary: #808080;
            --secondary-dark: #4D4D4D;
            --accent: #FF4D4D;
            --dark: #0D0D0D;
            --text: #333333;
            --text-light: #666666;
            --light: #F5F5F5;
            --white: #FFFFFF;
            --danger: #FF4D4D;
            --warning: #FFA726;
            --success: #4CAF50;
            --gradient: linear-gradient(135deg, #1A1A1A 0%, #333333 100%);
            --glass: rgba(255, 255, 255, 0.1);
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.15);
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
            line-height: 1.6;
        }

        /* Skip to main content for accessibility */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--primary);
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 9999;
        }

        .skip-link:focus {
            top: 6px;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Simplified Loading Animation */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--light);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .loader-content {
            text-align: center;
        }

        .loader-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--light);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Header Styles */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 15px 0;
            z-index: 1000;
            transition: var(--transition);
            backdrop-filter: blur(20px);
            background-color: rgba(248, 250, 252, 0.95);
        }

        header.scrolled {
            padding: 10px 0;
            background-color: rgba(248, 250, 252, 0.98);
            box-shadow: var(--shadow);
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
            align-items: center;
        }

        .nav-links li {
            margin-left: 30px;
        }

        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            padding: 8px 0;
        }

        .nav-links a:hover,
        .nav-links a:focus {
            color: var(--primary);
            outline: none;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient);
            transition: width 0.3s;
        }

        .nav-links a:hover::after,
        .nav-links a:focus::after {
            width: 100%;
        }

        .cta-button {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 15px 35px;
            font-size: 1.1rem;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: none;
            outline: none;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            z-index: 1;
            animation: pulse 2s infinite;
        }

        .cta-button:hover,
        .cta-button:focus {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }

        .cta-button.pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); }
            50% { box-shadow: 0 4px 25px rgba(0, 0, 0, 0.4); }
            100% { box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); }
        }

        .mobile-menu {
            display: none;
            font-size: 24px;
            cursor: pointer;
            padding: 12px;
            border-radius: 8px;
            transition: var(--transition);
            background: none;
            border: none;
            color: var(--dark);
        }

        .mobile-menu:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        /* Hero Section - Better Centered */
        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            background: var(--gradient);
            margin-top: 0;
            padding: 80px 20px 0;
        }

        .hero-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
            opacity: 0;
            transition: opacity 1s ease;
        }

        .hero-video.loaded {
            opacity: 0.7;
        }

        .hero-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 2;
        }

        .hero-content {
            position: relative;
            z-index: 3;
            width: 100%;
            max-width: 1200px;
            text-align: center;
            color: white;
            animation: fadeInUp 1s ease-out;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 80px);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
            line-height: 1.6;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-lg);
            width: 100%;
        }

        .trust-badges {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .trust-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
        }

        .trust-badge i {
            font-size: 1.2rem;
            color: var(--accent);
        }

        /* Sticky CTA Bar */
        .sticky-cta {
            position: fixed;
            bottom: -100px;
            left: 0;
            width: 100%;
            background: var(--gradient);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 999;
            transition: bottom 0.3s ease;
            box-shadow: var(--shadow-lg);
        }

        .sticky-cta.show {
            bottom: 0;
        }

        .sticky-cta-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .sticky-cta h3 {
            font-size: 1.1rem;
            margin: 0;
        }

        .sticky-cta p {
            margin: 0;
            opacity: 0.9;
        }

        /* WhatsApp Chat Button */
        .whatsapp-chat {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: #25d366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            text-decoration: none;
            box-shadow: var(--shadow-lg);
            z-index: 998;
            transition: var(--transition);
            animation: bounce 2s infinite;
        }

        .whatsapp-chat:hover {
            transform: scale(1.1);
            background: #128c7e;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        /* Quiz Section */
        .quiz-section {
            background: var(--light);
            padding: 80px 0;
        }

        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--shadow);
        }

        .quiz-question {
            display: none;
            text-align: center;
        }

        .quiz-question.active {
            display: block;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .quiz-question h3 {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: var(--dark);
        }

        .quiz-options {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }

        .quiz-option {
            padding: 15px 20px;
            border: 2px solid var(--light);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            background: var(--white);
            text-align: left;
        }

        .quiz-option:hover,
        .quiz-option:focus {
            border-color: var(--primary);
            background: rgba(0, 0, 0, 0.1);
        }

        .quiz-option.selected {
            border-color: var(--primary);
            border: 2px solid rgba(0, 0, 0, 0.1);
        }

        .quiz-progress {
            width: 100%;
            height: 8px;
            background: rgba(0, 0, 0, 0.1);
            background: var(--light);
            border-radius: 4px;
            margin-bottom: 30px;
            overflow: hidden;
        }

        .quiz-progress-bar {
            height: 100%;
            background: var(--gradient);
            width: 0%;
            transition: width 0.3s ease;
        }

        /* Live Countdown Timer */
        .countdown-section {
            background: var(--dark);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .countdown-timer {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .countdown-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: var(--border-radius);
            min-width: 100px;
        }

        .countdown-number {
            font-size: 2.5rem;
            font-weight: 700;
            display: block;
            color: var(--accent);
        }

        .countdown-label {
            font-size: 0.9rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Gym Tour with Images */
        .gym-tour-section {
            padding: 80px 0;
            background: var(--light);
        }

        .tour-container {
            max-width: 1000px;
            margin: 0 auto;
            text-align: center;
        }

        .tour-viewer {
            width: 100%;
            height: 500px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            margin-top: 30px;
            position: relative;
            background: #f0f0f0;
        }

        .tour-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.5s ease;
        }

        .tour-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            transition: opacity 0.3s ease;
        }

        .tour-overlay-content h3 {
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .tour-overlay-content p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .tour-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .tour-control {
            padding: 10px 15px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .tour-control:hover,
        .tour-control.active {
            background: var(--primary);
        }

        /* Testimonials Carousel */
        .testimonials-section {
            padding: 80px 0;
            background: var(--white);
        }

        .testimonials-carousel {
            position: relative;
            max-width: 1000px;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 20px;
        }

        .testimonial-slide {
            display: none;
            padding: 40px;
            text-align: center;
            background: var(--light);
        }

        .testimonial-slide.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .testimonial-content {
            max-width: 600px;
            margin: 0 auto;
        }

        .testimonial-quote {
            font-size: 1.2rem;
            font-style: italic;
            margin-bottom: 30px;
            line-height: 1.6;
            color: var(--dark);
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .author-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
        }

        .author-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .author-info h4 {
            margin: 0;
            color: var(--dark);
        }

        .author-info p {
            margin: 5px 0 0 0;
            color: var(--primary);
            font-size: 0.9rem;
        }

        .carousel-nav {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .carousel-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: var(--transition);
        }

        .carousel-dot.active {
            background: var(--primary);
        }

        /* Success Stories Grid with Images */
        .success-stories {
            padding: 80px 0;
            background: var(--light);
        }

        .stories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .story-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .story-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .story-image {
            height: 250px;
            position: relative;
            overflow: hidden;
        }

        .story-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .story-card:hover .story-image img {
            transform: scale(1.05);
        }

        .story-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient);
            opacity: 0.8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .story-content {
            padding: 20px;
        }

        .story-content h4 {
            margin-bottom: 10px;
            color: var(--dark);
        }

        .story-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 0.9rem;
            color: var(--primary);
        }

        /* Member Counter */
        .member-counter {
            background: var(--gradient);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .counter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .counter-item {
            text-align: center;
        }

        .counter-number {
            font-size: 3rem;
            font-weight: 800;
            display: block;
            margin-bottom: 10px;
        }

        .counter-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Membership Calculator */
        .calculator-section {
            padding: 80px 0;
            background: var(--white);
        }

        .calculator-container {
            max-width: 600px;
            margin: 0 auto;
            background: var(--light);
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        .calculator-form {
            display: grid;
            gap: 20px;
        }

        .form-group {
            display: grid;
            gap: 8px;
        }

        .form-group label {
            font-weight: 600;
            color: var(--dark);
        }

        .form-group select,
        .form-group input,
        .form-group textarea {
            padding: 12px 15px;
            border: 2px solid rgba(139, 92, 246, 0.2);
            border-radius: var(--border-radius);
            font-family: inherit;
            transition: var(--transition);
            font-size: 1rem;
        }

        .form-group select:focus,
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }

        .form-group.error input,
        .form-group.error textarea,
        .form-group.error select {
            border-color: var(--danger);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .error-message {
            color: var(--danger);
            font-size: 0.875rem;
            margin-top: 5px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .calculator-result {
            background: var(--gradient);
            color: white;
            padding: 20px;
            border-radius: var(--border-radius);
            text-align: center;
            margin-top: 20px;
        }

        .result-price {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        /* Exit Intent Popup */
        .exit-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .popup-content {
            background: var(--white);
            padding: 40px;
            border-radius: 20px;
            max-width: 500px;
            text-align: center;
            position: relative;
            animation: popupSlide 0.3s ease;
        }

        @keyframes popupSlide {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .popup-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--dark);
        }

        .popup-offer {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 15px;
        }

        /* Section Styles */
        section {
            padding: 80px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(2rem, 4vw, 2.5rem);
            color: var(--primary);
            margin-bottom: 15px;
        }

        .section-title p {
            color: var(--text);
            max-width: 700px;
            margin: 0 auto;
            font-size: 1.1rem;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
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
            color: var(--text);
            line-height: 1.6;
        }

        /* Contact Section */
        .contact {
            background: var(--light);
        }

        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: start;
        }

        .contact-form {
            background: var(--white);
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        .contact-form .form-group {
            margin-bottom: 20px;
        }

        .contact-form textarea {
            min-height: 120px;
            resize: vertical;
        }

        .contact-info h3 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--dark);
        }

        .contact-info p {
            margin-bottom: 30px;
            color: var(--text);
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
            color: var(--text);
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 60px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-family: 'Montserrat', sans-serif;
            margin-bottom: 20px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover,
        .footer-links a:focus {
            color: white;
            outline: none;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
        }

        .success-message {
            display: none;
            background: #e8f5e9;
            padding: 20px;
            border-radius: var(--border-radius);
            margin-top: 20px;
            text-align: center;
            color: #2e7d32;
        }

        .success-message.show {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .mobile-menu {
                display: block;
            }

            .nav-links {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: var(--white);
                flex-direction: column;
                justify-content: center;
                transition: left 0.3s ease;
                box-shadow: var(--shadow-lg);
            }

            .nav-links.active {
                left: 0;
            }

            .nav-links li {
                margin: 20px 0;
            }

            .hero {
                padding: 70px 15px 0;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .glass-card {
                padding: 30px 20px;
            }

            .trust-badges {
                gap: 15px;
            }

            .contact-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .countdown-timer {
                gap: 15px;
            }

            .countdown-item {
                min-width: 80px;
                padding: 15px;
            }

            .countdown-number {
                font-size: 2rem;
            }

            .sticky-cta {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }

            .sticky-cta-content {
                flex-direction: column;
                gap: 5px;
            }

            .whatsapp-chat {
                bottom: 80px;
            }

            .tour-viewer {
                height: 300px;
            }

            .stories-grid {
                grid-template-columns: 1fr;
            }

            .counter-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .counter-number {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }

            section {
                padding: 60px 0;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .popup-content {
                margin: 20px;
                padding: 30px 20px;
            }

            .calculator-container,
            .quiz-container {
                margin: 0 15px;
                padding: 30px 20px;
            }
        }

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            :root {
                --primary: #0000ff;
                --secondary: #0080ff;
                --dark: #000000;
                --light: #ffffff;
            }
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <!-- Skip to main content -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <!-- Simplified Page Loader -->
    <div class="page-loader" id="pageLoader">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <p>Loading EliteFit...</p>
        </div>
    </div>

    <!-- Header -->
    <header id="header" role="banner">
        <div class="container">
            <nav role="navigation" aria-label="Main navigation">
                <a href="#" class="logo" aria-label="EliteFit Home">
                    <i class="fas fa-dumbbell" aria-hidden="true"></i>EliteFit
                </a>
                <ul class="nav-links" id="navLinks">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#testimonials">Testimonials</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <a href="index.php" class="cta-button pulse" aria-label="Sign in to your account">
                    <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                    Sign In
                </a>
                <button class="mobile-menu" id="mobileMenu" aria-label="Toggle mobile menu" aria-expanded="false">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main id="main-content">
        <!-- Hero Section - Better Centered -->
        <section class="hero" id="home" role="banner">
            <!-- Hero Background Image -->
            <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" 
                 alt="Modern gym interior" class="hero-image">
            
            <!-- Video Background (loads after page) -->
            <video class="hero-video" id="heroVideo" muted loop playsinline>
                <source src="https://player.vimeo.com/external/371433846.sd.mp4?s=236da2f3c0fd273d2c6d9a064f3ae35579b2bbdf&profile_id=139&oauth2_token_id=57447761" type="video/mp4">
            </video>

            <div class="hero-content">
                <div class="glass-card">
                    <h1>Transform Your Body, Elevate Your Life</h1>
                    <p>Join EliteFit today and experience personalized training programs, state-of-the-art facilities, and a community that supports your fitness journey every step of the way.</p>
                    <a href="register.php" class="cta-button pulse" aria-label="Register for EliteFit membership">
                        <i class="fas fa-rocket" aria-hidden="true"></i>
                        Start Your Journey
                    </a>
                    
                    <!-- Trust Badges -->
                    <div class="trust-badges">
                        <div class="trust-badge">
                            <i class="fas fa-users" aria-hidden="true"></i>
                            <span>5000+ Members</span>
                        </div>
                        <div class="trust-badge">
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <span>4.9/5 Rating</span>
                        </div>
                        <div class="trust-badge">
                            <i class="fas fa-award" aria-hidden="true"></i>
                            <span>Certified Trainers</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Live Class Countdown - Proper Countdown -->
        <section class="countdown-section" aria-labelledby="countdown-title">
            <div class="container">
                <h2 id="countdown-title">Next Live Class Starts In</h2>
                <div class="countdown-timer" id="countdownTimer" role="timer" aria-live="polite">
                    <div class="countdown-item">
                        <span class="countdown-number" id="hours">00</span>
                        <span class="countdown-label">Hours</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="minutes">00</span>
                        <span class="countdown-label">Minutes</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="seconds">00</span>
                        <span class="countdown-label">Seconds</span>
                    </div>
                </div>
                <p style="margin-top: 20px; opacity: 0.8;">High-Intensity Interval Training with Sarah</p>
            </div>
        </section>

        <!-- Enhanced Quiz Section -->
        <section class="quiz-section" aria-labelledby="quiz-title">
            <div class="container">
                <div class="section-title" data-aos="fade-up">
                    <h2 id="quiz-title">Find Your Perfect Plan</h2>
                    <p>Answer a few questions to get a personalized fitness recommendation</p>
                </div>
                
                <div class="quiz-container" data-aos="fade-up">
                    <div class="quiz-progress">
                        <div class="quiz-progress-bar" id="quizProgress"></div>
                    </div>
                    
                    <!-- Question 1 -->
                    <div class="quiz-question active" data-question="1">
                        <h3>What's your primary fitness goal?</h3>
                        <div class="quiz-options">
                            <div class="quiz-option" data-value="weight-loss">
                                <strong>🔥 Weight Loss</strong><br>
                                <small>Burn calories and shed pounds effectively</small>
                            </div>
                            <div class="quiz-option" data-value="muscle-gain">
                                <strong>💪 Muscle Building</strong><br>
                                <small>Increase strength and muscle mass</small>
                            </div>
                            <div class="quiz-option" data-value="endurance">
                                <strong>🏃 Endurance</strong><br>
                                <small>Improve cardiovascular fitness</small>
                            </div>
                            <div class="quiz-option" data-value="general-fitness">
                                <strong>⚡ General Fitness</strong><br>
                                <small>Overall health and wellness</small>
                            </div>
                        </div>
                    </div>

                    <!-- Question 2 -->
                    <div class="quiz-question" data-question="2">
                        <h3>How often can you work out?</h3>
                        <div class="quiz-options">
                            <div class="quiz-option" data-value="2-3-times">
                                <strong>📅 2-3 times per week</strong><br>
                                <small>Perfect for beginners and busy schedules</small>
                            </div>
                            <div class="quiz-option" data-value="4-5-times">
                                <strong>📈 4-5 times per week</strong><br>
                                <small>Ideal for steady progress and results</small>
                            </div>
                            <div class="quiz-option" data-value="6-7-times">
                                <strong>🚀 6-7 times per week</strong><br>
                                <small>For dedicated athletes and fitness enthusiasts</small>
                            </div>
                        </div>
                    </div>

                    <!-- Question 3 -->
                    <div class="quiz-question" data-question="3">
                        <h3>What's your experience level?</h3>
                        <div class="quiz-options">
                            <div class="quiz-option" data-value="beginner">
                                <strong>🌱 Beginner</strong><br>
                                <small>New to fitness or returning after a break</small>
                            </div>
                            <div class="quiz-option" data-value="intermediate">
                                <strong>🎯 Intermediate</strong><br>
                                <small>Some experience with regular workouts</small>
                            </div>
                            <div class="quiz-option" data-value="advanced">
                                <strong>🏆 Advanced</strong><br>
                                <small>Experienced with complex training routines</small>
                            </div>
                        </div>
                    </div>

                    <!-- Question 4 -->
                    <div class="quiz-question" data-question="4">
                        <h3>What type of workouts do you prefer?</h3>
                        <div class="quiz-options">
                            <div class="quiz-option" data-value="cardio">
                                <strong>🏃‍♀️ Cardio Focused</strong><br>
                                <small>Running, cycling, HIIT classes</small>
                            </div>
                            <div class="quiz-option" data-value="strength">
                                <strong>🏋️ Strength Training</strong><br>
                                <small>Weight lifting, resistance training</small>
                            </div>
                            <div class="quiz-option" data-value="flexibility">
                                <strong>🧘 Flexibility & Balance</strong><br>
                                <small>Yoga, Pilates, stretching</small>
                            </div>
                            <div class="quiz-option" data-value="mixed">
                                <strong>🔄 Mixed Training</strong><br>
                                <small>Combination of all workout types</small>
                            </div>
                        </div>
                    </div>

                    <!-- Question 5 -->
                    <div class="quiz-question" data-question="5">
                        <h3>What's your biggest fitness challenge?</h3>
                        <div class="quiz-options">
                            <div class="quiz-option" data-value="time">
                                <strong>⏰ Lack of Time</strong><br>
                                <small>Need efficient, quick workouts</small>
                            </div>
                            <div class="quiz-option" data-value="motivation">
                                <strong>🎯 Staying Motivated</strong><br>
                                <small>Need accountability and support</small>
                            </div>
                            <div class="quiz-option" data-value="knowledge">
                                <strong>📚 Lack of Knowledge</strong><br>
                                <small>Need guidance on proper form and routines</small>
                            </div>
                            <div class="quiz-option" data-value="consistency">
                                <strong>📊 Being Consistent</strong><br>
                                <small>Struggle with maintaining regular habits</small>
                            </div>
                        </div>
                    </div>

                    <!-- Quiz Result -->
                    <div class="quiz-question" id="quizResult">
                        <h3>🎉 Your Personalized Plan</h3>
                        <div id="recommendedPlan"></div>
                        <a href="register.php" class="cta-button" style="margin-top: 20px;">
                            Get Started Now
                        </a>
                    </div>

                    <div class="quiz-navigation" style="text-align: center; margin-top: 20px;">
                        <button class="cta-button" id="quizRestart" style="display: none; background: var(--secondary);">Take Quiz Again</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Virtual Gym Tour with Images -->
        <section class="gym-tour-section" aria-labelledby="tour-title">
            <div class="container">
                <div class="section-title" data-aos="fade-up">
                    <h2 id="tour-title">Virtual Gym Tour</h2>
                    <p>Explore our state-of-the-art facilities</p>
                </div>
                
                <div class="tour-container" data-aos="fade-up">
                    <div class="tour-viewer" id="tourViewer">
                        <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" 
                             alt="Cardio equipment area" class="tour-image" id="tourImage">
                        <div class="tour-overlay" id="tourOverlay">
                            <div class="tour-overlay-content">
                                <h3>Cardio Zone</h3>
                                <p>State-of-the-art treadmills, ellipticals, and bikes with entertainment systems</p>
                            </div>
                        </div>
                        <div class="tour-controls">
                            <button class="tour-control active" data-area="cardio" aria-label="View cardio area">Cardio</button>
                            <button class="tour-control" data-area="weights" aria-label="View weights area">Weights</button>
                            <button class="tour-control" data-area="classes" aria-label="View group classes area">Classes</button>
                            <button class="tour-control" data-area="pool" aria-label="View pool area">Pool</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Member Success Stories with Images -->
        <section class="success-stories" aria-labelledby="stories-title">
            <div class="container">
                <div class="section-title" data-aos="fade-up">
                    <h2 id="stories-title">Member Success Stories</h2>
                    <p>Real transformations from real people</p>
                </div>
                
                <div class="stories-grid">
                    <div class="story-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="story-image">
                            <img src="https://images.unsplash.com/photo-1544717297-fa95b6ee9643?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1169&q=80" 
                                 alt="Sarah's fitness transformation">
                            <div class="story-overlay">
                                <i class="fas fa-trophy"></i>
                            </div>
                        </div>
                        <div class="story-content">
                            <h4>Sarah's Transformation</h4>
                            <p>"Lost 30 pounds and gained confidence I never knew I had! The trainers here are amazing."</p>
                            <div class="story-stats">
                                <span>6 months</span>
                                <span>-30 lbs</span>
                                <span>+15% muscle</span>
                            </div>
                        </div>
                    </div>

                    <div class="story-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="story-image">
                            <img src="https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" 
                                 alt="Mike's muscle building journey">
                            <div class="story-overlay">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                        </div>
                        <div class="story-content">
                            <h4>Mike's Journey</h4>
                            <p>"Built the physique I always dreamed of with expert guidance and personalized nutrition."</p>
                            <div class="story-stats">
                                <span>8 months</span>
                                <span>+25 lbs muscle</span>
                                <span>-10% body fat</span>
                            </div>
                        </div>
                    </div>

                    <div class="story-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="story-image">
                            <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" 
                                 alt="Emma's endurance improvement">
                            <div class="story-overlay">
                                <i class="fas fa-running"></i>
                            </div>
                        </div>
                        <div class="story-content">
                            <h4>Emma's Success</h4>
                            <p>"Improved my marathon time by 45 minutes! The cardio programs here are incredible."</p>
                            <div class="story-stats">
                                <span>4 months</span>
                                <span>45min faster</span>
                                <span>+20% endurance</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Live Member Counter -->
        <section class="member-counter" aria-labelledby="counter-title">
            <div class="container">
                <h2 id="counter-title">Join Our Growing Community</h2>
                <div class="counter-grid">
                    <div class="counter-item">
                        <span class="counter-number" data-target="5247">0</span>
                        <span class="counter-label">Active Members</span>
                    </div>
                    <div class="counter-item">
                        <span class="counter-number" data-target="1250">0</span>
                        <span class="counter-label">Classes This Month</span>
                    </div>
                    <div class="counter-item">
                        <span class="counter-number" data-target="98">0</span>
                        <span class="counter-label">Success Rate %</span>
                    </div>
                    <div class="counter-item">
                        <span class="counter-number" data-target="24">0</span>
                        <span class="counter-label">Expert Trainers</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Carousel -->
        <section class="testimonials-section" aria-labelledby="testimonials-title">
            <div class="container">
                <div class="section-title" data-aos="fade-up">
                    <h2 id="testimonials-title">What Our Members Say</h2>
                    <p>Real feedback from real people</p>
                </div>
                
                <div class="testimonials-carousel" data-aos="fade-up">
                    <div class="testimonial-slide active">
                        <div class="testimonial-content">
                            <p class="testimonial-quote">"EliteFit completely transformed my approach to fitness. The personalized training and nutrition plan helped me achieve results I never thought possible. The community here is incredibly supportive!"</p>
                            <div class="testimonial-author">
                                <div class="author-image">
                                    <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Sarah Johnson" loading="lazy">
                                </div>
                                <div class="author-info">
                                    <h4>Sarah Johnson</h4>
                                    <p>Member since 2021</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-slide">
                        <div class="testimonial-content">
                            <p class="testimonial-quote">"The trainers at EliteFit are exceptional. They pushed me beyond my limits while ensuring I stayed safe. I've gained 20 pounds of muscle and feel stronger than ever!"</p>
                            <div class="testimonial-author">
                                <div class="author-image">
                                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Mike Chen" loading="lazy">
                                </div>
                                <div class="author-info">
                                    <h4>Mike Chen</h4>
                                    <p>Member since 2020</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-slide">
                        <div class="testimonial-content">
                            <p class="testimonial-quote">"As a busy mom, I thought I'd never find time for fitness. EliteFit's flexible scheduling and virtual options made it possible. I'm in the best shape of my life!"</p>
                            <div class="testimonial-author">
                                <div class="author-image">
                                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Emma Davis" loading="lazy">
                                </div>
                                <div class="author-info">
                                    <h4>Emma Davis</h4>
                                    <p>Member since 2022</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="carousel-nav">
                    <span class="carousel-dot active" data-slide="0" aria-label="View testimonial 1"></span>
                    <span class="carousel-dot" data-slide="1" aria-label="View testimonial 2"></span>
                    <span class="carousel-dot" data-slide="2" aria-label="View testimonial 3"></span>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features" id="features" aria-labelledby="features-title">
            <div class="container">
                <div class="section-title" data-aos="fade-up">
                    <h2 id="features-title">Why Choose EliteFit</h2>
                    <p>Discover what makes us the premier fitness destination</p>
                </div>
                
                <div class="features-grid">
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="feature-icon">
                            <i class="fas fa-user-md" aria-hidden="true"></i>
                        </div>
                        <h3>Personal Training</h3>
                        <p>One-on-one sessions with certified trainers who create customized workout plans tailored to your specific goals and fitness level.</p>
                    </div>

                    <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line" aria-hidden="true"></i>
                        </div>
                        <h3>Progress Tracking</h3>
                        <p>Advanced analytics and detailed progress reports help you monitor your improvements and stay motivated on your fitness journey.</p>
                    </div>

                    <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="feature-icon">
                            <i class="fas fa-video" aria-hidden="true"></i>
                        </div>
                        <h3>Virtual Classes</h3>
                        <p>Access live and on-demand workout classes from anywhere. Never miss a session with our flexible virtual training platform.</p>
                    </div>

                    <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                        <div class="feature-icon">
                            <i class="fas fa-utensils" aria-hidden="true"></i>
                        </div>
                        <h3>Nutrition Coaching</h3>
                        <p>Expert nutritionists provide personalized meal plans and dietary guidance to complement your fitness routine and maximize results.</p>
                    </div>

                    <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                        <div class="feature-icon">
                            <i class="fas fa-users" aria-hidden="true"></i>
                        </div>
                        <h3>Community Support</h3>
                        <p>Join a vibrant community of fitness enthusiasts who motivate and support each other through challenges and celebrations.</p>
                    </div>

                    <div class="feature-card" data-aos="fade-up" data-aos-delay="600">
                        <div class="feature-icon">
                            <i class="fas fa-dumbbell" aria-hidden="true"></i>
                        </div>
                        <h3>Premium Equipment</h3>
                        <p>Train with the latest, state-of-the-art fitness equipment in our modern facilities designed for optimal performance and safety.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Membership Calculator -->
        <section class="calculator-section" aria-labelledby="calculator-title">
            <div class="container">
                <div class="section-title" data-aos="fade-up">
                    <h2 id="calculator-title">Calculate Your Membership</h2>
                    <p>Find the perfect plan that fits your budget and goals</p>
                </div>
                
                <div class="calculator-container" data-aos="fade-up">
                    <form class="calculator-form" id="membershipCalculator">
                        <div class="form-group">
                            <label for="membershipType">Membership Type</label>
                            <select id="membershipType" name="membershipType" required>
                                <option value="">Select membership type</option>
                                <option value="basic" data-price="49">Basic - $49/month</option>
                                <option value="premium" data-price="79">Premium - $79/month</option>
                                <option value="elite" data-price="129">Elite - $129/month</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="duration">Duration</label>
                            <select id="duration" name="duration" required>
                                <option value="">Select duration</option>
                                <option value="1" data-discount="0">1 Month</option>
                                <option value="3" data-discount="5">3 Months (5% off)</option>
                                <option value="6" data-discount="10">6 Months (10% off)</option>
                                <option value="12" data-discount="20">12 Months (20% off)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="addOns">Add-ons</label>
                            <select id="addOns" name="addOns">
                                <option value="0">No add-ons</option>
                                <option value="25">Personal Training (+$25/month)</option>
                                <option value="15">Nutrition Coaching (+$15/month)</option>
                                <option value="35">Both (+$35/month)</option>
                            </select>
                        </div>
                        
                        <div class="calculator-result" id="calculatorResult" style="display: none;">
                            <div class="result-price" id="totalPrice">$0</div>
                            <p id="priceBreakdown">Select options to see pricing</p>
                            <a href="register.php" class="cta-button" style="margin-top: 15px;">
                                Get This Plan
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Contact Section with Enhanced Form Validation -->
        <section class="contact" id="contact" aria-labelledby="contact-title">
            <div class="container">
                <div class="section-title" data-aos="fade-up">
                    <h2 id="contact-title">Schedule Your Free Consultation</h2>
                    <p>Ready to start your fitness journey? Let's talk!</p>
                </div>
                
                <div class="contact-container">
                    <div class="contact-info" data-aos="fade-right">
                        <h3>Get In Touch</h3>
                        <p>Our fitness experts are ready to help you achieve your goals. Schedule a free consultation to discuss your fitness journey and find the perfect plan for you.</p>
                        
                        <div class="contact-details">
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                                <span>East Legon, Boundary Road, Accra, Ghana</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone-alt" aria-hidden="true"></i>
                                <span>+233 (0)549837285</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-envelope" aria-hidden="true"></i>
                                <span>junyappteam@gmail.com</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-clock" aria-hidden="true"></i>
                                <span>Mon-Fri: 5AM-11PM, Sat-Sun: 6AM-10PM</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-form" data-aos="fade-left">
                        <form id="consultationForm" novalidate>
                            <div class="form-group">
                                <label for="fullName">Full Name *</label>
                                <input type="text" id="fullName" name="name" required>
                                <div class="error-message" id="fullNameError">Please enter your full name</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" required>
                                <div class="error-message" id="emailError">Please enter a valid email address</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone">
                                <div class="error-message" id="phoneError">Please enter a valid phone number</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Subject</label>
                                <input type="text" id="subject" name="subject" placeholder="What's this about?">
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea id="message" name="message" placeholder="Tell us about your fitness goals..." required></textarea>
                                <div class="error-message" id="messageError">Please enter your message</div>
                            </div>
                            
                            <button type="submit" class="cta-button">
                                <i class="fas fa-calendar-check" aria-hidden="true"></i>
                                Schedule Free Consultation
                            </button>
                        </form>
                        
                        <div id="consultationSuccess" class="success-message">
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                            <h4>Message Sent Successfully!</h4>
                            <p>Dear <span id="userName"></span>, your message has been sent successfully. We'll contact you within 24 hours to confirm your free consultation appointment.</p>
                        </div>
                        
                        <div id="consultationError" class="success-message" style="background: #ffebee; color: #c62828; display: none;">
                            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                            <h4>Error Sending Message</h4>
                            <p>There was an error sending your message. Please try again later or contact us directly.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Sticky CTA Bar -->
    <div class="sticky-cta" id="stickyCta">
        <div class="sticky-cta-content">
            <h3>Ready to Transform Your Life?</h3>
            <p>Join 5000+ members who chose EliteFit</p>
        </div>
        <a href="register.php" class="cta-button">
            <i class="fas fa-rocket" aria-hidden="true"></i>
            Get Started
        </a>
    </div>

    <!-- WhatsApp Chat Button -->
    <a href="https://wa.me/233549837285?text=Hi! I'm interested in learning more about EliteFit membership." class="whatsapp-chat" aria-label="Chat with us on WhatsApp" target="_blank" rel="noopener">
        <i class="fab fa-whatsapp" aria-hidden="true"></i>
    </a>

    <!-- Exit Intent Popup -->
    <div class="exit-popup" id="exitPopup" role="dialog" aria-labelledby="popup-title" aria-modal="true">
        <div class="popup-content">
            <button class="popup-close" id="popupClose" aria-label="Close popup">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
            <h3 id="popup-title" class="popup-offer">Wait! Don't Miss Out!</h3>
            <p>Get <strong>20% OFF</strong> your first month when you join EliteFit today!</p>
            <p style="margin-bottom: 20px; color: #64748b;">Limited time offer for new members only.</p>
            <a href="register.php?promo=SAVE20" class="cta-button">
                <i class="fas fa-gift" aria-hidden="true"></i>
                Claim Your Discount
            </a>
            <p style="margin-top: 15px; font-size: 0.9rem; color: #64748b;">
                Offer expires in 24 hours. Terms and conditions apply.
            </p>
        </div>
    </div>

    <!-- Footer -->
    <footer role="contentinfo">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>EliteFit</h3>
                    <p>Premium fitness solutions designed to help you achieve your health and wellness goals through personalized training, expert guidance, and a supportive community.</p>
                    <div style="margin-top: 20px;">
                        <a href="#" aria-label="Follow us on Facebook" style="color: rgba(255,255,255,0.7); margin-right: 15px; font-size: 1.2rem;">
                            <i class="fab fa-facebook-f" aria-hidden="true"></i>
                        </a>
                        <a href="#" aria-label="Follow us on Instagram" style="color: rgba(255,255,255,0.7); margin-right: 15px; font-size: 1.2rem;">
                            <i class="fab fa-instagram" aria-hidden="true"></i>
                        </a>
                        <a href="#" aria-label="Follow us on Twitter" style="color: rgba(255,255,255,0.7); margin-right: 15px; font-size: 1.2rem;">
                            <i class="fab fa-twitter" aria-hidden="true"></i>
                        </a>
                        <a href="#" aria-label="Subscribe to our YouTube channel" style="color: rgba(255,255,255,0.7); font-size: 1.2rem;">
                            <i class="fab fa-youtube" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#testimonials">Testimonials</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <li><a href="register.php">Join Now</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Programs</h3>
                    <ul class="footer-links">
                        <li><a href="#">Personal Training</a></li>
                        <li><a href="#">Group Classes</a></li>
                        <li><a href="#">Virtual Training</a></li>
                        <li><a href="#">Nutrition Coaching</a></li>
                        <li><a href="#">Corporate Wellness</a></li>
                        <li><a href="#">Youth Programs</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Support</h3>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Membership Agreement</a></li>
                        <li><a href="#">Contact Support</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 EliteFit. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/emailjs-com@3.2.0/dist/email.min.js"></script>
    
    <script>
        // Fast Page Loader - Hide after DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const loader = document.getElementById('pageLoader');
                loader.style.opacity = '0';
                setTimeout(function() {
                    loader.style.display = 'none';
                }, 300);
            }, 500);
        });

        // Initialize EmailJS with your User ID
        (function() {
            emailjs.init("J7eH552YZ1UzGixM4"); // Replace with your EmailJS user ID
        })();

        // Initialize AOS with reduced settings for better performance
        try {
            AOS.init({
                duration: 600,
                easing: 'ease-in-out',
                once: true,
                offset: 50
            });
        } catch(e) {
            console.log('AOS not loaded');
        }

        // Mobile Menu Toggle
        document.getElementById('mobileMenu').addEventListener('click', function() {
            const navLinks = document.getElementById('navLinks');
            const isActive = navLinks.classList.contains('active');
            
            navLinks.classList.toggle('active');
            this.setAttribute('aria-expanded', !isActive);
            this.querySelector('i').classList.toggle('fa-bars');
            this.querySelector('i').classList.toggle('fa-times');
        });

        // Header Scroll Effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Smooth Scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // Close mobile menu if open
                    document.getElementById('navLinks').classList.remove('active');
                    document.getElementById('mobileMenu').setAttribute('aria-expanded', 'false');
                    document.querySelector('#mobileMenu i').classList.add('fa-bars');
                    document.querySelector('#mobileMenu i').classList.remove('fa-times');
                }
            });
        });

        // Lazy load hero video after page loads
        window.addEventListener('load', function() {
            setTimeout(function() {
                const heroVideo = document.getElementById('heroVideo');
                if (heroVideo) {
                    heroVideo.addEventListener('loadeddata', function() {
                        this.classList.add('loaded');
                        this.play().catch(e => console.log('Video autoplay failed'));
                    });
                    heroVideo.load();
                }
            }, 1000);
        });

        // Sticky CTA Bar - Show after passing contact form
        let stickyCTAShown = false;
        window.addEventListener('scroll', function() {
            const stickyCta = document.getElementById('stickyCta');
            const contactSection = document.getElementById('contact');
            
            if (contactSection) {
                const contactRect = contactSection.getBoundingClientRect();
                const hasPassedContact = contactRect.bottom < window.innerHeight;
                
                if (hasPassedContact && !stickyCTAShown) {
                    stickyCta.classList.add('show');
                    stickyCTAShown = true;
                } else if (!hasPassedContact && stickyCTAShown) {
                    stickyCta.classList.remove('show');
                    stickyCTAShown = false;
                }
            }
        });

        // Live Countdown Timer - Starts counting down from current time
        function updateCountdown() {
            const now = new Date().getTime();
            const nextClass = new Date();
            
            // Set next class to tomorrow at 8 AM (14 hours from now)
            nextClass.setHours(nextClass.getHours() + 14, 0, 0, 0);
            
            // If it's already past 8 AM tomorrow, set to 8 AM the day after
            if (nextClass < now) {
                nextClass.setDate(nextClass.getDate() + 1);
                nextClass.setHours(8, 0, 0, 0);
            }
            
            const distance = nextClass - now;
            
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
            document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Enhanced Quiz Functionality
        let currentQuestion = 1;
        const totalQuestions = 5;
        let quizAnswers = {};

        function updateQuizProgress() {
            const progress = (currentQuestion / totalQuestions) * 100;
            document.getElementById('quizProgress').style.width = progress + '%';
        }

        function showQuestion(questionNum) {
            document.querySelectorAll('.quiz-question').forEach(q => q.classList.remove('active'));
            const targetQuestion = document.querySelector(`[data-question="${questionNum}"]`);
            if (targetQuestion) {
                targetQuestion.classList.add('active');
            }
            updateQuizProgress();
        }

        function getRecommendation() {
            const goal = quizAnswers[1];
            const frequency = quizAnswers[2];
            const experience = quizAnswers[3];
            const workoutType = quizAnswers[4];
            const challenge = quizAnswers[5];
            
            let recommendation = '';
            let planName = '';
            let price = '';
            let features = [];
            
            // Determine plan based on answers
            if (goal === 'weight-loss') {
                if (frequency === '2-3-times' && experience === 'beginner') {
                    planName = 'Weight Loss Starter';
                    price = '$59/month';
                    features = ['3x/week guided workouts', 'Basic nutrition plan', 'Progress tracking'];
                } else {
                    planName = 'Intensive Weight Loss';
                    price = '$89/month';
                    features = ['5x/week varied workouts', 'Detailed meal plans', 'Weekly check-ins'];
                }
            } else if (goal === 'muscle-gain') {
                planName = 'Muscle Building Pro';
                price = '$99/month';
                features = ['Strength-focused routines', 'Protein optimization', 'Form coaching'];
            } else if (goal === 'endurance') {
                planName = 'Endurance Elite';
                price = '$79/month';
                features = ['Cardio conditioning', 'Stamina building', 'Performance tracking'];
            } else {
                planName = 'Complete Fitness';
                price = '$69/month';
                features = ['Balanced training', 'Flexibility work', 'Wellness coaching'];
            }

            // Add challenge-specific features
            if (challenge === 'time') {
                features.push('Quick 20-30 min workouts');
            } else if (challenge === 'motivation') {
                features.push('Accountability partner');
            } else if (challenge === 'knowledge') {
                features.push('Educational resources');
            } else if (challenge === 'consistency') {
                features.push('Habit tracking tools');
            }

            recommendation = `
                <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); margin: 20px 0;">
                    <h4 style="color: var(--primary); font-size: 1.8rem; margin-bottom: 15px;">${planName}</h4>
                    <div style="font-size: 2rem; font-weight: 800; color: var(--primary); margin-bottom: 20px;">${price}</div>
                    <ul style="list-style: none; padding: 0; margin-bottom: 25px;">
                        ${features.map(feature => `<li style="padding: 8px 0; border-bottom: 1px solid #eee;"><i class="fas fa-check" style="color: var(--accent); margin-right: 10px;"></i>${feature}</li>`).join('')}
                    </ul>
                    <p style="color: #666; font-style: italic;">Perfect match based on your goals and preferences!</p>
                </div>
            `;
            
            document.getElementById('recommendedPlan').innerHTML = recommendation;
        }

        // Quiz option selection
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('quiz-option')) {
                // Remove previous selection
                e.target.parentNode.querySelectorAll('.quiz-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Add selection to clicked option
                e.target.classList.add('selected');
                
                // Store answer
                const questionNum = parseInt(e.target.closest('.quiz-question').dataset.question);
                quizAnswers[questionNum] = e.target.dataset.value;
                
                // Show next question or result
                setTimeout(() => {
                    if (currentQuestion < totalQuestions) {
                        currentQuestion++;
                        showQuestion(currentQuestion);
                    } else {
                        getRecommendation();
                        document.getElementById('quizResult').classList.add('active');
                        document.getElementById('quizRestart').style.display = 'inline-block';
                    }
                }, 500);
            }
        });

        // Quiz restart
        document.getElementById('quizRestart').addEventListener('click', function() {
            currentQuestion = 1;
            quizAnswers = {};
            document.querySelectorAll('.quiz-question').forEach(q => q.classList.remove('active'));
            document.querySelectorAll('.quiz-option').forEach(opt => opt.classList.remove('selected'));
            showQuestion(1);
            this.style.display = 'none';
        });

        // Virtual Gym Tour with Images
        const tourImages = {
            cardio: {
                src: 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
                title: 'Cardio Zone',
                description: 'State-of-the-art treadmills, ellipticals, and bikes with entertainment systems'
            },
            weights: {
                src: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
                title: 'Weight Training Area',
                description: 'Free weights, machines, and functional training equipment'
            },
            classes: {
                src: 'https://images.unsplash.com/photo-1518611012118-696072aa579a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
                title: 'Group Classes Studio',
                description: 'Spacious studio for yoga, pilates, HIIT, and dance classes'
            },
            pool: {
                src: 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
                title: 'Swimming Pool',
                description: 'Olympic-size pool for laps, aqua fitness, and relaxation'
            }
        };

        document.querySelectorAll('.tour-control').forEach(control => {
            control.addEventListener('click', function() {
                const area = this.dataset.area;
                const tourImage = document.getElementById('tourImage');
                const tourOverlay = document.getElementById('tourOverlay');
                
                // Remove active class from all controls
                document.querySelectorAll('.tour-control').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                
                // Update image and overlay
                if (tourImages[area]) {
                    tourImage.src = tourImages[area].src;
                    tourImage.alt = tourImages[area].title;
                    tourOverlay.querySelector('h3').textContent = tourImages[area].title;
                    tourOverlay.querySelector('p').textContent = tourImages[area].description;
                }
            });
        });

        // Testimonials Carousel
        let currentTestimonial = 0;
        const testimonials = document.querySelectorAll('.testimonial-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        
        function showTestimonial(index) {
            testimonials.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            if (testimonials[index] && dots[index]) {
                testimonials[index].classList.add('active');
                dots[index].classList.add('active');
            }
        }
        
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentTestimonial = index;
                showTestimonial(currentTestimonial);
            });
        });
        
        // Auto-advance testimonials
        setInterval(() => {
            currentTestimonial = (currentTestimonial + 1) % testimonials.length;
            showTestimonial(currentTestimonial);
        }, 5000);

        // Animated Counters with Intersection Observer
        function animateCounters() {
            const counters = document.querySelectorAll('.counter-number');
            
            counters.forEach(counter => {
                const target = parseInt(counter.dataset.target);
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        counter.textContent = target.toLocaleString();
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current).toLocaleString();
                    }
                }, 16);
            });
        }
        
        // Trigger counter animation when section is visible
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    counterObserver.disconnect();
                }
            });
        });
        
        const counterSection = document.querySelector('.member-counter');
        if (counterSection) {
            counterObserver.observe(counterSection);
        }

        // Membership Calculator
        function calculateMembership() {
            const membershipType = document.getElementById('membershipType');
            const duration = document.getElementById('duration');
            const addOns = document.getElementById('addOns');
            const result = document.getElementById('calculatorResult');
            const totalPrice = document.getElementById('totalPrice');
            const priceBreakdown = document.getElementById('priceBreakdown');
            
            if (membershipType.value && duration.value) {
                const basePrice = parseInt(membershipType.selectedOptions[0].dataset.price);
                const months = parseInt(duration.value);
                const discount = parseInt(duration.selectedOptions[0].dataset.discount);
                const addOnPrice = parseInt(addOns.value);
                
                const monthlyTotal = basePrice + addOnPrice;
                const subtotal = monthlyTotal * months;
                const discountAmount = (subtotal * discount) / 100;
                const finalTotal = subtotal - discountAmount;
                
                totalPrice.textContent = `$${finalTotal.toLocaleString()}`;
                priceBreakdown.innerHTML = `
                    <strong>Monthly:</strong> $${monthlyTotal}/month<br>
                    <strong>Duration:</strong> ${months} month${months > 1 ? 's' : ''}<br>
                    ${discount > 0 ? `<strong>Discount:</strong> ${discount}% off (-$${discountAmount})<br>` : ''}
                    <strong>Total:</strong> $${finalTotal.toLocaleString()}
                `;
                
                result.style.display = 'block';
            } else {
                result.style.display = 'none';
            }
        }
        
        document.getElementById('membershipType').addEventListener('change', calculateMembership);
        document.getElementById('duration').addEventListener('change', calculateMembership);
        document.getElementById('addOns').addEventListener('change', calculateMembership);

        // Exit Intent Popup
        let exitIntentShown = false;
        
        document.addEventListener('mouseleave', function(e) {
            if (e.clientY <= 0 && !exitIntentShown) {
                document.getElementById('exitPopup').style.display = 'flex';
                exitIntentShown = true;
            }
        });
        
        document.getElementById('popupClose').addEventListener('click', function() {
            document.getElementById('exitPopup').style.display = 'none';
        });
        
        document.getElementById('exitPopup').addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });

        // Enhanced Form Validation
        function validateField(field, errorElement, validationFn, errorMessage) {
            const isValid = validationFn(field.value);
            const formGroup = field.closest('.form-group');
            
            if (!isValid) {
                formGroup.classList.add('error');
                errorElement.textContent = errorMessage;
                errorElement.classList.add('show');
                return false;
            } else {
                formGroup.classList.remove('error');
                errorElement.classList.remove('show');
                return true;
            }
        }

        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function validatePhone(phone) {
            if (!phone) return true; // Phone is optional
            const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
            return phoneRegex.test(phone.replace(/[\s\-$$$$]/g, ''));
        }

        // Real-time validation
        document.getElementById('fullName').addEventListener('blur', function() {
            validateField(
                this,
                document.getElementById('fullNameError'),
                (value) => value.trim().length >= 2,
                'Please enter your full name (at least 2 characters)'
            );
        });

        document.getElementById('email').addEventListener('blur', function() {
            validateField(
                this,
                document.getElementById('emailError'),
                validateEmail,
                'Please enter a valid email address'
            );
        });

        document.getElementById('phone').addEventListener('blur', function() {
            validateField(
                this,
                document.getElementById('phoneError'),
                validatePhone,
                'Please enter a valid phone number'
            );
        });

        document.getElementById('message').addEventListener('blur', function() {
            validateField(
                this,
                document.getElementById('messageError'),
                (value) => value.trim().length >= 10,
                'Please enter a message (at least 10 characters)'
            );
        });

        // Enhanced Form Submission with EmailJS
        document.getElementById('consultationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate all fields
            const fullName = document.getElementById('fullName');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');
            const message = document.getElementById('message');
            
            const isFullNameValid = validateField(
                fullName,
                document.getElementById('fullNameError'),
                (value) => value.trim().length >= 2,
                'Please enter your full name (at least 2 characters)'
            );
            
            const isEmailValid = validateField(
                email,
                document.getElementById('emailError'),
                validateEmail,
                'Please enter a valid email address'
            );
            
            const isPhoneValid = validateField(
                phone,
                document.getElementById('phoneError'),
                validatePhone,
                'Please enter a valid phone number'
            );
            
            const isMessageValid = validateField(
                message,
                document.getElementById('messageError'),
                (value) => value.trim().length >= 10,
                'Please enter a message (at least 10 characters)'
            );
            
            // If any validation fails, don't submit
            if (!isFullNameValid || !isEmailValid || !isPhoneValid || !isMessageValid) {
                // Focus on first invalid field
                const firstError = this.querySelector('.form-group.error input, .form-group.error textarea');
                if (firstError) {
                    firstError.focus();
                }
                return;
            }
            
            // Get form values
            const formData = {
                name: fullName.value.trim(),
                email: email.value.trim(),
                phone: phone.value.trim(),
                subject: document.getElementById('subject').value.trim(),
                message: message.value.trim()
            };
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;
            
            // Send email using EmailJS
            emailjs.send('service_zv3753o', 'template_jvz2qzq', formData)
                .then(function(response) {
                    console.log('SUCCESS!', response.status, response.text);
                    
                    // Display success message
                    document.getElementById('userName').textContent = formData.name.split(' ')[0]; // First name only
                    
                    // Hide form and show success message
                    document.getElementById('consultationForm').style.display = 'none';
                    document.getElementById('consultationError').style.display = 'none';
                    const successMessage = document.getElementById('consultationSuccess');
                    successMessage.style.display = 'block';
                    successMessage.classList.add('show');
                    
                    // Reset form after animation
                    setTimeout(() => {
                        document.getElementById('consultationForm').reset();
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        
                        // Clear any validation errors
                        document.querySelectorAll('.form-group').forEach(group => {
                            group.classList.remove('error');
                        });
                        document.querySelectorAll('.error-message').forEach(error => {
                            error.classList.remove('show');
                        });
                    }, 500);
                    
                    // Hide message and show form after 8 seconds
                    setTimeout(() => {
                        successMessage.classList.remove('show');
                        setTimeout(() => {
                            successMessage.style.display = 'none';
                            document.getElementById('consultationForm').style.display = 'block';
                        }, 500);
                    }, 8000);

                }, function(error) {
                    console.log('FAILED...', error);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    // Show error message
                    document.getElementById('consultationSuccess').style.display = 'none';
                    const errorMessage = document.getElementById('consultationError');
                    errorMessage.style.display = 'block';
                    errorMessage.classList.add('show');
                    
                    // Hide error message after 5 seconds
                    setTimeout(() => {
                        errorMessage.classList.remove('show');
                        setTimeout(() => {
                            errorMessage.style.display = 'none';
                        }, 500);
                    }, 5000);
                });
        });

        // Keyboard Navigation Support
        document.addEventListener('keydown', function(e) {
            // Close popup with Escape key
            if (e.key === 'Escape') {
                const popup = document.getElementById('exitPopup');
                if (popup.style.display === 'flex') {
                    popup.style.display = 'none';
                }
            }
        });

        console.log('EliteFit website loaded successfully! 🏋️‍♂️');
    </script>
</body>
</html>