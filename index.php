<?php
session_start();
// Removed: Check to redirect if already logged in. Users will now see this general page regardless of login status.
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>DigiKeep - Welcome</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
/* --- PROFESSIONAL TEAL THEME STYLES --- */

/* 1. Global & Body */
body { 
    font-family: 'Inter', system-ui, -apple-system, sans-serif; 
    background: #f4f8f9; /* Very subtle cool gray/teal tint */
    color: #37474f; 
    display: flex; 
    flex-direction: column; 
    min-height: 100vh; 
}

/* 2. Top Navigation (Right Side) */
.top-nav {
    position: absolute;
    top: 25px;
    right: 40px;
    z-index: 1000;
}

.top-nav > ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    gap: 30px;
    align-items: center;
}

.top-nav > ul > li > a {
    color: #546e7a; 
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.top-nav > ul > li > a:hover {
    color: #00897b; /* Teal hover */
    transform: translateY(-1px);
}
.top-nav i { margin-right: 6px; }

/* --- Brand Logo (Left Side) --- */
.brand-logo {
    position: absolute;
    top: 20px;
    left: 40px;
    z-index: 1000;
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #00695c; /* Teal Brand Color */
    font-weight: 800; /* Bold font */
    font-size: 1.3rem;
    letter-spacing: -0.5px;
    transition: color 0.3s ease;
}

.brand-logo:hover {
    color: #004d40; /* Darker teal on hover */
}

.brand-logo img {
    height: 40px; /* Adjust size */
    width: auto;
    margin-right: 10px;
}

/* Dropdown styling */
.dropdown-menu { 
    border: none; 
    box-shadow: 0 10px 25px rgba(0,0,0,0.08); 
    border-radius: 10px;
    margin-top: 12px; 
    padding: 8px;
}
.dropdown-item { 
    font-size: 14px; 
    color: #546e7a; 
    padding: 10px 15px; 
    border-radius: 6px;
    font-weight: 500;
}
.dropdown-item:active, .dropdown-item:hover { 
    background-color: #e0f2f1; 
    color: #00695c; 
}

/* 3. Hero Section */
.hero { 
    /* Teal Gradient */
    background: linear-gradient(135deg, #004d40 0%, #00695c 40%, #26a69a 100%);
    color: white; 
    padding: 80px 30px; 
    border-radius: 16px; 
    margin-bottom: 60px; 
    text-align: center; 
    position: relative; 
    margin-top: 80px; /* Increased top margin to clear the logo/nav */
    box-shadow: 0 15px 35px rgba(0, 77, 64, 0.2); 
    border: 1px solid rgba(255,255,255,0.1);
}

.hero h1 { font-weight: 800; letter-spacing: -0.5px; }
.hero .lead { font-weight: 400; opacity: 0.9; }

/* 4. Content Section & Cards */
.content-section { padding: 20px 0 60px 0; }
.content-section h3 { color: #263238; font-weight: 700; }

.card {
    border: none;
    border-radius: 12px;
    background: white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-top: 4px solid #4db6ac; 
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0, 137, 123, 0.15); 
}

.card h5 { color: #00695c; font-weight: 700; margin-bottom: 15px; }
.card p { color: #607d8b; line-height: 1.6; font-size: 0.95rem; }

.content-section a {
    color: #00897b;
    text-decoration: none;
    font-weight: 600;
    border-bottom: 1px dotted #00897b;
}
.content-section a:hover { color: #004d40; }

/* --- UPDATED: Info Box Style (Left Aligned Logo) --- */
.info-box {
    background: white;
    border-radius: 16px;
    padding: 40px;
    margin-top: 40px;
    box-shadow: 0 10px 30px rgba(0, 77, 64, 0.05);
    /* Left border for side-by-side layout */
    border-left: 6px solid #00695c; 
    
    display: flex;
    flex-direction: row;    /* Stack Side-by-Side */
    align-items: center;    /* Center vertically */
    text-align: left;       /* Left align text */
    gap: 40px;              /* Space between logo and text */
}

/* On small screens, stack them */
@media (max-width: 768px) {
    .info-box {
        flex-direction: column;
        text-align: center;
        border-left: none;
        border-top: 6px solid #00695c;
    }
}

.info-box img {
    width: 220px; /* Bigger Logo */
    height: auto;
    opacity: 0.9;
    flex-shrink: 0; /* Prevent logo from shrinking */
}

.info-text p {
    color: #546e7a;
    line-height: 1.7;
    margin-bottom: 0;
    font-size: 1rem;
}

/* 5. FAQ Section */
.faq-container {
    max-width: 800px;
    margin: 0 auto 80px auto;
}

.faq-item {
    background: white;
    border: 1px solid #b2dfdb; 
    border-radius: 10px;
    margin-bottom: 15px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.faq-question {
    padding: 22px 25px;
    cursor: pointer;
    font-weight: 600;
    color: #00695c; 
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    transition: all 0.3s ease;
}

.faq-question:hover { 
    background: #e0f2f1; 
    color: #004d40; 
}

.active .faq-question { 
    background: linear-gradient(to right, #00695c, #00897b);
    color: #ffffff !important; 
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
    background: #fdfdfd; 
}

.faq-answer p { 
    padding: 25px; 
    margin: 0; 
    color: #546e7a; 
    line-height: 1.6;
    border-left: 4px solid #4db6ac;
    background: #f4f8f9;
}

.faq-icon { transition: transform 0.3s; color: #00897b; }
.active .faq-icon { transform: rotate(180deg); color: #ffffff; }

/* 6. Footer */
.footer {
    background-color: #fff !important;
    border-top: 1px solid #eceff1 !important;
}
.text-primary { color: #00897b !important; }

</style>
</head>
<body>

<a href="index.php" class="brand-logo">
    <img src="uploads/logodigikeep.png" alt="DigiKeep Logo">
    DigiKeep
</a>

<nav class="top-nav">
    <ul>
        <li><a href="index.php"><i class="bi bi-house-door"></i> Home</a></li>
        
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-telephone"></i> Contact No
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="mailto:support@digikeep.com"><i class="bi bi-envelope me-2"></i> support@digikeep.com</a></li>
                <li><a class="dropdown-item" href="tel:+603958432"><i class="bi bi-phone me-2"></i> +603958432</a></li>
            </ul>
        </li>

        <li><a href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
    </ul>
</nav>

<div class="container py-5">

    <div class="hero">
        <h1 class="fw-bold mb-2">DigiKeep (Digital Asset Management)</h1>
        <p class="lead mb-4">Empowering teams to upload, manage, and control company digital files efficiently.</p>
        <p class="mt-4">This system is confidential and accessible only by authorised personnel.</p>
    </div>

    <div class="content-section">
        <h3 class="text-center mb-4">What is Digital Asset Management (DAM)?</h3>
        <p class="lead text-center text-muted">A DAM system is a centralised platform for organising, storing, and retrieving rich media files, ensuring brand consistency and legal compliance.</p>
        
        <div class="row mt-5">
            <div class="col-md-4">
                <div class="card p-3 h-100">
                    <h5>Centralised Storage</h5>
                    <p>All approved digital assets are stored in one secure location, making it easy for authorised users to find and utilise them efficiently.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 h-100">
                    <h5>Security and Access Control</h5>
                    <p>Restrict access to sensitive documents and media, granting permissions only to authorised users based on their role (Admin or User).</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 h-100">
                    <h5>Efficient Workflow</h5>
                    <p>The system is designed to streamline the process of uploading, reviewing, and approving digital assets for internal use.</p>
                </div>
            </div>
        </div>

        <div class="info-box">
            <div>
                <img src="uploads/slogan.png" alt="DigiKeep Logo Large">
            </div>
            <div class="info-text">
                <h4 style="color: #00695c; font-weight: 700; margin-bottom: 15px;">About DigiKeep</h4>
                <p>DigiKeep is a Digital Asset Management (DAM) platform that helps organisations securely store, organise, and access important digital files in one central place. With features like metadata tagging, user-role control, and version management, DigiKeep improves teamwork, reduces file duplication, and keeps every asset accurate and easy to find. Our goal is to provide a simple, reliable system that strengthens productivity and supports organisations in managing their growing digital content with confidence.</p>
            </div>
        </div>

        <div class="text-center mt-5">
            <p class="text-muted">For access, please proceed to the <a href="login.php">internal login portal</a> (Authorised Access Only).</p>
        </div>
    </div>

    <div class="faq-container">
        <h3 class="text-center mb-4">Frequently Asked Questions</h3>
        
        <div class="faq-item">
            <div class="faq-question">How do I get an account? <i class="bi bi-chevron-down faq-icon"></i></div>
            <div class="faq-answer"><p>Access is restricted to employees. Please contact the IT department or your manager to request login credentials.</p></div>
        </div>

        <div class="faq-item">
            <div class="faq-question">What file types are supported? <i class="bi bi-chevron-down faq-icon"></i></div>
            <div class="faq-answer"><p>The system currently supports PDF, JPG, PNG, Excel, and Word documents. File size limits may apply.</p></div>
        </div>

        <div class="faq-item">
            <div class="faq-question">Who approves the assets? <i class="bi bi-chevron-down faq-icon"></i></div>
            <div class="faq-answer"><p>Assets are uploaded to a pending queue and reviewed by designated System Administrators before becoming visible to the company.</p></div>
        </div>
    </div>

</div>

<?php
// Determine display name/role safely
$displayRole = 'Guest';
if (isset($_SESSION['role'])) {
    $displayRole = ucfirst($_SESSION['role']); // e.g., "Admin" or "User"
} elseif (isset($_SESSION['username'])) {
    $displayRole = htmlspecialchars($_SESSION['username']);
}
?>

<footer class="footer mt-5 py-4 bg-light border-top">
    <div class="container-fluid">
        <div class="row align-items-center">
            
            <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                <span class="text-muted small">
                    &copy; <?= date('Y') ?> <strong>Asset Management System</strong>. All rights reserved.
                </span>
            </div>

            <div class="col-md-6 text-center text-md-end">
                <span class="text-muted small me-3">Version 1.0</span>
                <span class="text-muted small">
                    Logged in as: <span class="fw-bold text-primary"><?= $displayRole ?></span>
                </span>
            </div>
            
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>

<script>
const questions = document.querySelectorAll('.faq-question');
questions.forEach(question => {
    question.addEventListener('click', () => {
        const item = question.parentNode;
        const answer = item.querySelector('.faq-answer');
        
        // Toggle Active Class
        item.classList.toggle('active');

        // Toggle Max Height for Slide Effect
        if (answer.style.maxHeight) {
            answer.style.maxHeight = null;
        } else {
            answer.style.maxHeight = answer.scrollHeight + "px";
        }
    });
});
</script>

</body>
</html>