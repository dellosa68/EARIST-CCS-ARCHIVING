<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer Example</title>
    <style>
        /* General footer styling */
.footer {
    background: linear-gradient(135deg, #6e8efb, #a777e3);
    color: #fff;
    padding: 30px 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}

.footer-logo h1 {
    font-size: 28px;
    margin: 0;
    font-family: 'Arial', sans-serif;
}

.footer-links,
.footer-social {
    display: flex;
    gap: 20px;
}

.footer-links a,
.footer-social a {
    color: #fff;
    text-decoration: none;
    font-size: 16px;
    transition: color 0.3s ease, transform 0.3s ease;
}

.footer-links a:hover,
.footer-social a:hover {
    color: #f0f0f0;
    transform: scale(1.1);
}

.footer-social {
    gap: 15px;
}

.social-icon {
    display: inline-block;
    width: 40px;
    height: 40px;
    line-height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    font-size: 18px;
    text-align: center;
    transition: background 0.3s ease, transform 0.3s ease;
}

.social-icon:hover {
    background: rgba(255, 255, 255, 0.4);
    transform: scale(1.2);
}

.footer-bottom {
    margin-top: 20px;
}

.footer-bottom p {
    margin: 0;
    font-size: 14px;
}

    </style>
</head>
<body>
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">
                <h1>MyWebsite</h1>
            </div>
            <div class="footer-links">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
                <a href="privacy.php">Privacy Policy</a>
            </div>
            <div class="footer-social">
                <a href="https://facebook.com" target="_blank" class="social-icon">F</a>
                <a href="https://twitter.com" target="_blank" class="social-icon">T</a>
                <a href="https://instagram.com" target="_blank" class="social-icon">I</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> MyWebsite. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>