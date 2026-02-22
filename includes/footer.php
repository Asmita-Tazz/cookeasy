<style>
    .main-footer {
        background: var(--primary);
        color: #d1d8d8;
        padding: 5rem 8% 2rem;
        margin-top: 6rem;
    }
    .footer-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 4rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding-bottom: 3rem;
    }
    .footer-logo { 
         font-family: 'Playfair Display';
         color: white; 
         font-size: 1.5rem;
         margin-bottom: 1.5rem;
         display: block;
         text-decoration: none;
     }
    .footer-col h4 { 
         color: white;
         margin-bottom: 1.2rem; 
         font-size: 1rem; 
    }
    .footer-col a { 
         display: block;
         color: #a2adad;
         text-decoration: none;
         margin-bottom: 0.8rem; 
         font-size: 0.9rem; 
         transition: 0.3s; 
    }
    .footer-col a:hover {
         color: var(--accent);
         }
    .copyright {
          text-align: center;
          padding-top: 2rem; 
          font-size: 0.85rem;
          color: #7f8c8d;
          }
</style>

<footer class="main-footer">
    <div class="footer-grid">
        <div class="footer-col">
            <a href="#" class="footer-logo">Cook<span>Easy.</span></a>
            <p>Empowering home cooks to share their passion and discover new flavors every single day.</p>
        </div>
        <div class="footer-col">
            <h4>Quick Links</h4>
            <a href="index.php">Home</a>
            <a href="index.php">All Recipes</a>
            <a href="categories.php">Collections</a>
        </div>
        <div class="footer-col">
            <h4>Community</h4>
            <a href="#">Top Chefs</a>
            <a href="#">Food Stories</a>
            <a href="#">Write a Review</a>
        </div>
        <div class="footer-col">
            <h4>Newsletter</h4>
            <p style="font-size: 0.8rem; margin-bottom: 1rem;">Get the best recipes in your inbox.</p>
            <input type="email" placeholder="Your email" style="width:100%; padding: 0.6rem; border-radius: 4px; border:none;">
        </div>
    </div>
    <p class="copyright">&copy; 2026 CookEasy Digital Media. All rights reserved.</p>
</footer>
</body>
</html>