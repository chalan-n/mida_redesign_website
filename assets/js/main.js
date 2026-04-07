
document.addEventListener('DOMContentLoaded', () => {
    // --- Mobile Menu Toggle ---
    const mobileBtn = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    const navLinks = navMenu ? navMenu.querySelectorAll('a') : [];

    if (mobileBtn) {
        const setMenuState = (isOpen) => {
            navMenu.classList.toggle('active', isOpen);
            mobileBtn.classList.toggle('active', isOpen);
            mobileBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        };

        mobileBtn.addEventListener('click', () => {
            setMenuState(!navMenu.classList.contains('active'));
        });

        mobileBtn.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setMenuState(false);
                mobileBtn.focus();
            }
        });

        navLinks.forEach((link) => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    setMenuState(false);
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && navMenu.classList.contains('active')) {
                setMenuState(false);
                mobileBtn.focus();
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                setMenuState(false);
            }
        });
    }

    // --- Sticky Header ---
    const header = document.querySelector('.header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // --- Loan Calculator (Flat Rate) ---
    const amountInput = document.getElementById('loanAmount');
    const amountRange = document.getElementById('amountRange');
    const termSelect = document.getElementById('loanTerm');
    const typeSelect = document.getElementById('loanType');

    const resultMonthly = document.getElementById('monthlyPayment');
    const resultTotal = document.getElementById('totalPayment');

    // Interest Rates (Approximate Flat Rates per month)
    // Car: 0.55%, Pickup: 0.60%, Truck: 0.70%, Land: 0.90%
    const rates = {
        'car': 0.0055,
        'pickup': 0.0060,
        'truck': 0.0070,
        'land': 0.0090
    };

    function calculateLoan() {
        if (!amountInput || !termSelect || !typeSelect) return;

        let principal = parseFloat(amountInput.value);
        let months = parseInt(termSelect.value);
        let type = typeSelect.value;
        let ratePerMonth = rates[type] || 0.0060; // Default

        if (isNaN(principal) || principal <= 0) {
            resultMonthly.textContent = '0';
            // resultTotal.textContent = '0';
            return;
        }

        // Flat Rate Calculation
        // Total Interest = Principal * Rate/Month * Months
        let totalInterest = principal * ratePerMonth * months;
        let totalAmount = principal + totalInterest;
        let monthlyPayment = totalAmount / months;

        // Format numbers
        resultMonthly.innerText = '฿' + Math.ceil(monthlyPayment).toLocaleString();

        // Update Range Slider to match Input
        if (amountRange && document.activeElement !== amountRange) {
            amountRange.value = principal;
        }
    }

    // Event Listeners for Calculator
    if (amountInput) {
        amountInput.addEventListener('input', () => {
            // Sync range
            if (amountRange) amountRange.value = amountInput.value;
            calculateLoan();
        });

        if (amountRange) {
            amountRange.addEventListener('input', () => {
                amountInput.value = amountRange.value;
                calculateLoan();
            });
        }
    }

    if (termSelect) termSelect.addEventListener('change', calculateLoan);
    if (typeSelect) typeSelect.addEventListener('change', calculateLoan);

    // Initial Calc
    calculateLoan();



    // --- Cookie Consent Banner ---
    (function initCookieBanner() {
        const cookieKey = 'mida_cookie_consent';

        // Check if user has already accepted
        if (!localStorage.getItem(cookieKey)) {
            // Create banner HTML
            const banner = document.createElement('div');
            banner.className = 'cookie-consent-banner';
            banner.innerHTML = `
                <div class="cookie-content">
                    <p class="cookie-text">
                        เว็บไซต์นี้ใช้คุกกี้ (Cookies) เพื่อมอบประสบการณ์การใช้งานที่ดีที่สุดให้กับท่าน 
                        และเพื่อปรับปรุงประสิทธิภาพเว็บไซต์ ท่านสามารถศึกษารายละเอียดได้ที่ 
                        <a href="cookie_policy.php">นโยบายเกี่ยวกับคุกกี้</a>
                    </p>
                    <button class="btn btn-primary cookie-btn" id="acceptCookie">ยอมรับ</button>
                </div>
            `;

            document.body.appendChild(banner);

            // Allow small delay for transition effect
            setTimeout(() => {
                banner.classList.add('show');
            }, 100);

            // Add click listener
            document.getElementById('acceptCookie').addEventListener('click', () => {
                localStorage.setItem(cookieKey, 'true');
                banner.classList.remove('show');

                // Remove from DOM after transition
                setTimeout(() => {
                    banner.remove();
                }, 500);
            });
        }
    })();

    // --- Back to Top Button ---
    (function initBackToTop() {
        // Create button HTML
        const btn = document.createElement('button');
        btn.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
        btn.className = 'back-to-top';
        btn.setAttribute('aria-label', 'Back to top');
        document.body.appendChild(btn);

        // Show/Hide on scroll
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                btn.classList.add('show');
            } else {
                btn.classList.remove('show');
            }
        });

        // Scroll to top on click
        btn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    })();
});
