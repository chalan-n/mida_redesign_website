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

    if (resultMonthly) {
        resultMonthly.setAttribute('aria-live', 'polite');
    }

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
        let ratePerMonth = rates[type] || 0.0060;

        if (isNaN(principal) || principal <= 0) {
            resultMonthly.textContent = '0';
            return;
        }

        let totalInterest = principal * ratePerMonth * months;
        let totalAmount = principal + totalInterest;
        let monthlyPayment = totalAmount / months;

        resultMonthly.innerText = '฿' + Math.ceil(monthlyPayment).toLocaleString();

        if (amountRange && document.activeElement !== amountRange) {
            amountRange.value = principal;
        }
    }

    if (amountInput) {
        amountInput.addEventListener('input', () => {
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
    calculateLoan();

    // --- Cookie Consent Banner ---
    (function initCookieBanner() {
        const cookieKey = 'mida_cookie_consent';
        let banner = document.getElementById('cookieConsentBanner');
        let acceptButton = document.getElementById('acceptCookie');

        const hideBanner = () => {
            if (!banner) return;
            banner.classList.remove('show');
            banner.setAttribute('aria-hidden', 'true');
        };

        const acceptCookies = () => {
            localStorage.setItem(cookieKey, 'true');
            hideBanner();

            setTimeout(() => {
                if (banner && !banner.dataset.persistent) {
                    banner.remove();
                }
            }, 500);
        };

        if (!banner) {
            banner = document.createElement('div');
            banner.className = 'cookie-consent-banner';
            banner.id = 'cookieConsentBanner';
            banner.innerHTML = `
                <div class="cookie-content">
                    <p class="cookie-text">
                        เว็บไซต์นี้ใช้คุกกี้ (Cookies) เพื่อมอบประสบการณ์การใช้งานที่ดีที่สุดให้กับท่าน 
                        และเพื่อปรับปรุงประสิทธิภาพเว็บไซต์ ท่านสามารถศึกษารายละเอียดได้ที่ 
                        <a href="cookie_policy.php">นโยบายเกี่ยวกับคุกกี้</a>
                    </p>
                    <button class="btn btn-primary cookie-btn" id="acceptCookie" type="button">ยอมรับ</button>
                </div>
            `;

            document.body.appendChild(banner);
            acceptButton = document.getElementById('acceptCookie');
        } else {
            banner.dataset.persistent = 'true';
            if (!acceptButton) {
                acceptButton = banner.querySelector('.cookie-btn');
            }
        }

        banner.setAttribute('role', 'region');
        banner.setAttribute('aria-label', 'Cookie consent');
        banner.setAttribute('aria-hidden', 'true');

        if (acceptButton) {
            acceptButton.setAttribute('type', 'button');
            acceptButton.addEventListener('click', acceptCookies);
        }

        if (localStorage.getItem(cookieKey)) {
            hideBanner();
            return;
        }

        setTimeout(() => {
            banner.setAttribute('aria-hidden', 'false');
            banner.classList.add('show');
        }, 100);
    })();

    // --- Back to Top Button ---
    (function initBackToTop() {
        const btn = document.createElement('button');
        btn.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
        btn.className = 'back-to-top';
        btn.type = 'button';
        btn.setAttribute('aria-label', 'Back to top');
        btn.setAttribute('aria-hidden', 'true');
        btn.tabIndex = -1;
        document.body.appendChild(btn);

        const setBackToTopVisibility = (isVisible) => {
            btn.classList.toggle('show', isVisible);
            btn.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
            btn.tabIndex = isVisible ? 0 : -1;
        };

        window.addEventListener('scroll', () => {
            setBackToTopVisibility(window.scrollY > 300);
        });

        btn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        setBackToTopVisibility(window.scrollY > 300);
    })();
});
