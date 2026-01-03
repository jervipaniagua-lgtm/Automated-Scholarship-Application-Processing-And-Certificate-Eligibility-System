    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ScholarManage | Scholarship Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php
    session_start();
    $auth_error = $_SESSION['auth_error'] ?? '';
    unset($_SESSION['auth_error']);
    ?>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        :root{
        --primary:#2c3e50; --secondary:#3498db; --light:#ecf0f1; --dark:#2c3e50;
        }
        body{ background:#f9f9f9; color:#333; line-height:1.6; }

        /* Navbar */
        nav{
        background:var(--primary); color:#fff; padding:1rem 5%;
        display:flex; justify-content:space-between; align-items:center;
        position:sticky; top:0; z-index:1000; box-shadow:0 2px 10px rgba(0,0,0,.1);
        }
        .logo{ display:flex; align-items:center; gap:10px; }
        .logo i{ font-size:1.8rem; color:var(--secondary); }
        .logo h1{ font-size:1.5rem; font-weight:700; }
        .nav-links{ display:flex; gap:2rem; list-style:none; align-items:center; }
        .nav-links a{ color:white; text-decoration:none; font-weight:500; transition:color .3s; }
        .nav-links a:hover{ color:var(--secondary); }
        .mobile-menu-btn{ display:none; background:none; border:none; color:white; font-size:1.5rem; cursor:pointer; }

        /* Buttons */
        .btn{
        padding:.8rem 1.8rem; border-radius:4px; font-weight:600; cursor:pointer;
        transition:all .3s ease; text-decoration:none; display:inline-flex; align-items:center; gap:8px;
        }
        .btn-primary{ background:var(--secondary); color:white; border:none; }
        .btn-primary:hover{ background:#2980b9; transform:translateY(-3px); box-shadow:0 5px 15px rgba(0,0,0,.12); }
        .btn-secondary{ background:transparent; color:white; border:2px solid white; }
        .btn-secondary:hover{ background:white; color:var(--primary); transform:translateY(-3px); box-shadow:0 5px 15px rgba(0,0,0,.12); }

        /* HERO (Parallax) */
        .hero{
        position:relative;
        color:white;
        min-height:85vh;
        display:flex;
        align-items:center;
        justify-content:center;
        text-align:center;
        padding:5rem 5%;
        background-image:
            linear-gradient(135deg, rgba(44,62,80,.9) 0%, rgba(26,37,48,.95) 100%),
            url("");
        background-size:cover;
        background-position:center;
        background-attachment:fixed; /* parallax */
        overflow:hidden;
        }
        .hero::before{
        content:"";
        position:absolute; inset:-80px;
        background:
            radial-gradient(circle at 20% 20%, rgba(52,152,219,.22), transparent 55%),
            radial-gradient(circle at 80% 30%, rgba(255,255,255,.10), transparent 50%),
            radial-gradient(circle at 50% 85%, rgba(52,152,219,.15), transparent 55%);
        transform:translateY(var(--blobY, 0px));
        transition:transform .05s linear;
        pointer-events:none;
        }
        .hero-content{ position:relative; z-index:1; max-width:900px; }
        .hero h2{ font-size:3rem; margin-bottom:1rem; }
        .hero p{ font-size:1.2rem; max-width:750px; margin:0 auto 2rem; opacity:.92; }

        .cta-buttons{ display:flex; gap:1rem; flex-wrap:wrap; justify-content:center; margin-top:1.2rem; }

        /* Sections */
        .features{ padding:5rem 5%; background:white; }
        .section-title{ text-align:center; margin-bottom:3rem; color:var(--primary); }
        .section-title h2{ font-size:2.5rem; margin-bottom:.5rem; }
        .section-title p{ color:#666; max-width:600px; margin:0 auto; }

        .features-grid{
        display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));
        gap:2rem; margin-top:3rem;
        }
        .feature-card{
        background:var(--light); padding:2rem; border-radius:8px;
        box-shadow:0 5px 15px rgba(0,0,0,.05); transition:transform .3s ease;
        }
        .feature-card:hover{ transform:translateY(-10px); }
        .feature-icon{
        background:var(--secondary); color:white; width:70px; height:70px; border-radius:50%;
        display:flex; align-items:center; justify-content:center; margin-bottom:1.5rem; font-size:1.8rem;
        }
        .feature-card h3{ color:var(--primary); margin-bottom:1rem; }
        .feature-card p{ color:#555; margin-bottom:1rem; }

        /* How it works */
        .how-it-works{ padding:5rem 5%; background:#f5f7fa; }
        .steps{ display:flex; flex-wrap:wrap; justify-content:center; gap:2rem; margin-top:3rem; }
        .step{
        flex:1; min-width:250px; max-width:350px; background:white; padding:2rem; border-radius:8px;
        box-shadow:0 5px 15px rgba(0,0,0,.05); position:relative;
        }
        .step-number{
        position:absolute; top:-15px; left:-15px; background:var(--secondary); color:white;
        width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center;
        font-weight:bold; font-size:1.2rem;
        }
        .step h3{ color:var(--primary); margin-bottom:1rem; }
        .step p{ color:#555; }

        /* Login Modal */
        .login-modal{
        display:none; position:fixed; inset:0; background:rgba(0,0,0,.7);
        z-index:2000; justify-content:center; align-items:center;
        opacity:0;
        transition:opacity 0.3s ease-out;
        }
        .login-modal.show{
        opacity:1;
        }
        .modal-content{
        background:white; width:92%; max-width:520px; border-radius:10px; overflow:hidden;
        box-shadow:0 10px 30px rgba(0,0,0,.2);
        transform:scale(0.9);
        transition:transform 0.3s ease-out;
        }
        .login-modal.show .modal-content{
        transform:scale(1);
        }
        .modal-header{
        background:var(--primary); color:white; padding:1.3rem 1.5rem;
        display:flex; justify-content:space-between; align-items:center;
        }
        .close-modal{ background:none; border:none; color:white; font-size:1.6rem; cursor:pointer; }
        .modal-body{ padding:2rem; }
        .form-group{ margin-bottom:1.2rem; }
        .form-group label{ display:block; margin-bottom:.5rem; font-weight:600; color:var(--primary); }
        .form-group input{
        width:100%; padding:.85rem; border:1px solid #ddd; border-radius:6px; font-size:1rem;
        }
        .modal-btn{
        width:100%; padding:.95rem; background:var(--secondary); color:white; border:none; border-radius:6px;
        font-weight:700; cursor:pointer; transition:background-color .3s;
        display:flex; justify-content:center; align-items:center; gap:8px;
        }
        .modal-btn:hover{ background:#2980b9; }
        .register-link{ text-align:center; margin-top:1rem; color:#666; }
        .register-link a{ color:var(--secondary); text-decoration:none; font-weight:600; }

        /* Footer */
        footer{ background:var(--dark); color:white; padding:3rem 5% 1.5rem; }
        .footer-content{
        display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));
        gap:2rem; margin-bottom:2rem;
        }
        .footer-column h3{ margin-bottom:1.2rem; font-size:1.2rem; }
        .footer-links{ list-style:none; }
        .footer-links li{ margin-bottom:.8rem; color:#bbb; }
        .footer-links a{ color:#bbb; text-decoration:none; transition:color .3s; }
        .footer-links a:hover{ color:white; }
        .copyright{
        text-align:center; padding-top:1.5rem; border-top:1px solid #444; color:#bbb; font-size:.9rem;
        }

        /* Fade Transitions */
        .fade-in{
        opacity:0;
        transform:translateY(30px);
        transition:opacity 0.8s ease-out, transform 0.8s ease-out;
        }
        .fade-in.visible{
        opacity:1;
        transform:translateY(0);
        }
        .fade-in-left{
        opacity:0;
        transform:translateX(-40px);
        transition:opacity 0.8s ease-out, transform 0.8s ease-out;
        }
        .fade-in-left.visible{
        opacity:1;
        transform:translateX(0);
        }
        .fade-in-right{
        opacity:0;
        transform:translateX(40px);
        transition:opacity 0.8s ease-out, transform 0.8s ease-out;
        }
        .fade-in-right.visible{
        opacity:1;
        transform:translateX(0);
        }
        .stagger-1{ transition-delay:0.1s; }
        .stagger-2{ transition-delay:0.2s; }
        .stagger-3{ transition-delay:0.3s; }
        .stagger-4{ transition-delay:0.4s; }

        @media (max-width: 992px){
        .hero h2{ font-size:2.5rem; }
        .hero{ background-attachment:scroll; } /* mobile perf */
        }
        @media (max-width: 768px){
        .nav-links{
            display:none; position:absolute; top:100%; left:0; width:100%;
            background:var(--primary); flex-direction:column; padding:1rem 0; text-align:center;
        }
        .nav-links.active{ display:flex; }
        .nav-links li{ padding:.8rem 0; }
        .mobile-menu-btn{ display:block; }
        .hero h2{ font-size:2rem; }
        .hero p{ font-size:1rem; }
        .cta-buttons{ flex-direction:column; align-items:center; }
        .btn{ width:100%; justify-content:center; max-width:320px; }
        .hero{ background-attachment:scroll; }
        }
    </style>
    </head>
    <body>

    <!-- NAV -->
    <nav>
        <div class="logo">
        <i class="fas fa-graduation-cap"></i>
        <h1>ScholarManage</h1>
        </div>
        <ul class="nav-links">
        <li><a href="#home">Home</a></li>
        <li><a href="#features">Features</a></li>
        <li><a href="#how-it-works">How It Works</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="register.php" class="btn btn-secondary" style="padding:.6rem 1.2rem;">Register</a></li>
        <li><button class="btn btn-primary" id="openLogin" type="button">Login</button></li>
        </ul>
        <button class="mobile-menu-btn" id="mobileMenuBtn" type="button">
        <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- HERO -->
    <section class="hero" id="home">
        <div class="hero-content">
        <h2 class="fade-in">Streamlined Scholarship Management System</h2>
        <p class="fade-in stagger-1">Students can apply for scholarships and request certificates with automatic eligibility checks. Admin accounts are default and not shown on this page.</p>
        <div class="cta-buttons fade-in stagger-2">
            <button class="btn btn-primary" id="applyNow" type="button">
            <i class="fas fa-paper-plane"></i> Apply for Scholarship
            </button>
            <button class="btn btn-secondary" id="requestCert" type="button">
            <i class="fas fa-file-certificate"></i> Request Certificate
            </button>
        </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section class="features" id="features">
        <div class="section-title fade-in">
        <h2>System Features</h2>
        <p>Everything needed for scholarship applications and certificate generation.</p>
        </div>

        <div class="features-grid">
        <div class="feature-card fade-in-left stagger-1">
            <div class="feature-icon"><i class="fas fa-user-plus"></i></div>
            <h3>Student Registration</h3>
            <p>Students register once using their details and secure credentials.</p>
        </div>

        <div class="feature-card fade-in stagger-2">
            <div class="feature-icon"><i class="fas fa-file-signature"></i></div>
            <h3>Scholarship Application</h3>
            <p>Submit application and upload requirements (COR, Grades, ID, etc.).</p>
        </div>

        <div class="feature-card fade-in-right stagger-3">
            <div class="feature-icon"><i class="fas fa-certificate"></i></div>
            <h3>Certificate Requests</h3>
            <p>Only students with NO active scholarship can request certificates.</p>
        </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="how-it-works" id="how-it-works">
        <div class="section-title fade-in">
        <h2>How It Works</h2>
        <p>Simple workflow for students and admins.</p>
        </div>

        <div class="steps">
        <div class="step fade-in stagger-1">
            <div class="step-number">1</div>
            <h3>Register & Login</h3>
            <p>Students create an account and log in.</p>
        </div>
        <div class="step fade-in stagger-2">
            <div class="step-number">2</div>
            <h3>Apply for Scholarship</h3>
            <p>Upload documents and submit. Status becomes pending.</p>
        </div>
        <div class="step fade-in stagger-3">
            <div class="step-number">3</div>
            <h3>Admin Reviews</h3>
            <p>Admin checks if student already has scholarship, then approves or rejects.</p>
        </div>
        <div class="step fade-in stagger-4">
            <div class="step-number">4</div>
            <h3>Certificate (If Eligible)</h3>
            <p>Students without scholarship may request; admin generates PDF certificate.</p>
        </div>
        </div>
    </section>

    <!-- LOGIN MODAL (POST to auth.php) -->
    <div class="login-modal" id="loginModal">
        <div class="modal-content">
        <div class="modal-header">
            <h3>Student Login</h3>
            <button class="close-modal" id="closeModal" type="button">&times;</button>
        </div>
        <div class="modal-body">

            <!-- IMPORTANT: This POSTS to auth.php -->
            <form id="loginForm" method="POST" action="auth.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input name="email" type="email" id="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input name="password" type="password" id="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="modal-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>

            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
            </form>

            <p style="margin-top:12px;color:#888;font-size:0.9rem;text-align:center;">
            Admin account is default and logs in through a separate admin page (not shown here).
            </p>
        </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer id="about">
        <div class="footer-content">
        <div class="footer-column">
            <h3>ScholarManage</h3>
            <p>A scholarship management system designed to streamline application and certificate requests.</p>
        </div>
        <div class="footer-column">
            <h3>Quick Links</h3>
            <ul class="footer-links">
            <li><a href="#home">Home</a></li>
            <li><a href="#features">Features</a></li>
            <li><a href="#how-it-works">How It Works</a></li>
            <li><a href="#" id="footerLogin">Student Login</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h3>Contact Info</h3>
            <ul class="footer-links">
            <li><i class="fas fa-envelope"></i> support@scholarmanage.edu</li>
            <li><i class="fas fa-phone"></i> (123) 456-7890</li>
            <li><i class="fas fa-map-marker-alt"></i> University Campus, Education City</li>
            </ul>
        </div>
        </div>
        <div class="copyright">
        <p>&copy; 2023 ScholarManage. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Mobile menu
        document.getElementById('mobileMenuBtn').addEventListener('click', () => {
        document.querySelector('.nav-links').classList.toggle('active');
        });

        // Modal open/close
        const loginModal = document.getElementById('loginModal');
        const openLoginBtn = document.getElementById('openLogin');
        const footerLogin = document.getElementById('footerLogin');
        const closeModalBtn = document.getElementById('closeModal');

        function openLoginModal(){
        loginModal.style.display = 'flex';
        setTimeout(() => loginModal.classList.add('show'), 10);
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.getElementById('email').focus(), 300);
        }
        function closeLoginModal(){
        loginModal.classList.remove('show');
        setTimeout(() => {
            loginModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
        }

        openLoginBtn.addEventListener('click', openLoginModal);
        footerLogin.addEventListener('click', (e) => { e.preventDefault(); openLoginModal(); });
        closeModalBtn.addEventListener('click', closeLoginModal);
        window.addEventListener('click', (e) => { if(e.target === loginModal) closeLoginModal(); });

        // Force login first for CTA buttons
        document.getElementById('applyNow').addEventListener('click', () => openLoginModal());
        document.getElementById('requestCert').addEventListener('click', () => openLoginModal());

        // Parallax blob layer (light)
        const hero = document.querySelector('.hero');
        window.addEventListener('scroll', () => {
        const y = window.scrollY;
        hero.style.setProperty('--blobY', (y * 0.10) + 'px');
        });

        // Fade-in animations on scroll
        const observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
            }
        });
        }, observerOptions);

        // Observe all fade-in elements
        document.querySelectorAll('.fade-in, .fade-in-left, .fade-in-right').forEach(el => {
        observer.observe(el);
        });

        // Immediate fade-in for hero content
        setTimeout(() => {
        document.querySelectorAll('.hero .fade-in').forEach(el => {
            el.classList.add('visible');
        });
        }, 100);
        
        // Show authentication error if exists
        <?php if (!empty($auth_error)): ?>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: '<?php echo addslashes($auth_error); ?>',
            confirmButtonColor: '#3498db'
        });
        <?php endif; ?>
    </script>
    </body>
    </html>
