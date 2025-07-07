<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindMate - Home</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        html, body { scroll-behavior: smooth; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f7f4; color: #333; }
        .navbar { background-color: #2e7d32; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar .nav-link { color: #ffffff !important; transition: color 0.3s; }
        .navbar .nav-link:hover { color: #a5d6a7 !important; }
        .hero { background: url('https://images.unsplash.com/photo-1457369804613-52c61a468e7d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80') no-repeat center center; background-size: cover; color: #fff; padding: 150px 0; position: relative; }
        .hero-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); }
        .hero-content { position: relative; z-index: 1; text-align: center; }
        .section { padding: 60px 0; }
        .card-custom { border: none; background: #ffffff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .card-custom:hover { transform: translateY(-5px); }
        .tech-logo { height: 50px; margin: 10px; filter: grayscale(0%); transition: filter 0.3s; }
        .tech-logo:hover { filter: grayscale(0%) brightness(1.2); }
        .footer { background-color: #2e7d32; color: #fff; padding: 20px 0; text-align: center; }
        .team-img { width: 140px; height: 140px; object-fit: cover; }
        /* Add to existing <style> block */
.about-img {
    max-width: 100%;
    height: auto;
    max-height: 300px; /* Adjust this value based on your preference */
    object-fit: cover;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="#">MindMate</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#team">Team</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tech">Tech Stack</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
                <div>
                    <a class="btn btn-outline-light ms-3" href="login.php">Login</a>
                    <a class="btn btn-light ms-2" href="register.php">Sign Up</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="container">
                <h1 class="display-4 fw-bold">Empowering Young Minds with AI</h1>
                <p class="lead mb-4">Track your mood, get personalized support, and thrive with MindMate.</p>
              
            </div>
        </div>
    </section>

    <!-- About Section -->
   <section class="section" id="about">
    <div class="container">
        <h2 class="text-center mb-5">About MindMate</h2>
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="lead">MindMate was created to address the post-COVID mental health surge among students. Born from an AIML Hackathon, our mission is to blend cutting-edge AI with empathetic design, helping young people understand and improve their emotional well-being.</p>
            </div>
            <div class="col-md-6">
                <img src="mindmate.jpg" alt="MindMate Concept" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto; max-height: 300px; object-fit: cover;">
            </div>
        </div>
    </div>
</section>

    <!-- Features Section -->
    <section class="section bg-light" id="features">
        <div class="container">
            <h2 class="text-center mb-5">Key Features</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card card-custom h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-pen fa-2x text-success mb-3"></i>
                            <h5 class="card-title">Daily Mood Journaling</h5>
                            <p class="card-text">Log your feelings in seconds for self-awareness and growth.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-2x text-success mb-3"></i>
                            <h5 class="card-title">Real-Time Sentiment</h5>
                            <p class="card-text">AI analyzes your mood and shows trends instantly.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-envelope fa-2x text-success mb-3"></i>
                            <h5 class="card-title">Weekly AI Report</h5>
                            <p class="card-text">Get personalized tips via email every week.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="section" id="team">
        <div class="container">
            <h2 class="text-center mb-5">Meet the Team</h2>
            <div class="row g-4 justify-content-center">
                <div class="col-sm-6 col-lg-3 text-center">
                    <img src="pavanlogo.jpg" alt="Pavan" class="rounded-circle team-img shadow-sm mb-3">
                    <h6 class="fw-bold">Pavan</h6>
                    <p class="text-muted"> Backend Developer</p>
                </div>
                <div class="col-sm-6 col-lg-3 text-center">
                    <img src="sakshi.jpg" alt="Sakshi" class="rounded-circle team-img shadow-sm mb-3">
                    <h6 class="fw-bold">Sakshi</h6>
                    <p class="text-muted"> UI/UX Designer</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tech Stack Section -->
    <section class="section bg-light" id="tech">
        <div class="container text-center">
            <h2 class="mb-5">Tech Stack</h2>
            <div>
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" class="tech-logo" alt="PHP">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" class="tech-logo" alt="MySQL">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/python/python-original.svg" class="tech-logo" alt="Python">
                <img src="https://avatars.githubusercontent.com/u/44050962?s=200&v=4" class="tech-logo" alt="Flask">
                <img src="https://upload.wikimedia.org/wikipedia/commons/a/a8/Microsoft_Azure_Logo.svg" class="tech-logo" alt="Azure">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" class="tech-logo" alt="JavaScript">
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section" id="contact">
        <div class="container">
            <h2 class="text-center mb-5">Contact Us</h2>
            <div class="row justify-content-center">
                <div class="col-md-8">
                  
                <div class="text-center mt-4">
                    <p><strong>Contact Details:</strong></p>
                    <p><i class="fas fa-phone"></i> Phone: <a href="tel:+911234567890" target="_blank">+91 123 456 7890</a></p>
                    <p><i class="fas fa-envelope"></i> Email: <a href="mailto:mindmate.team@example.com" target="_blank">mindmate.team@example.com</a></p>
                    <p><i class="fab fa-instagram"></i> Instagram: <a href="https://instagram.com/mindmate_official" target="_blank">@mindmate_official</a></p>
                    <p><i class="fab fa-linkedin"></i> LinkedIn: <a href="https://linkedin.com/company/mindmate" target="_blank">linkedin.com/company/mindmate</a></p>
                </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p class="mb-0">© 2025 MindMate. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>