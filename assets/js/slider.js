document.addEventListener('DOMContentLoaded', () => {
    const sliderWrapper = document.querySelector('.slider-wrapper');
    const slides = document.querySelectorAll('.slide');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const dotsContainer = document.querySelector('.slider-dots');

    if (!sliderWrapper || slides.length === 0) return;

    let currentIndex = 0;
    const slideCount = slides.length;
    let autoPlayInterval;

    function loadSlideImage(index) {
        const slide = slides[index];
        if (!slide) return;

        const images = slide.querySelectorAll('img[data-src]');
        images.forEach((img) => {
            const src = img.getAttribute('data-src');
            if (!src) return;

            img.src = src;
            img.removeAttribute('data-src');
            img.classList.add('loaded');
        });
    }

    function preloadNearbySlides(index) {
        loadSlideImage(index);
        loadSlideImage((index + 1) % slideCount);
    }

    // Create Dots
    slides.forEach((_, index) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.classList.add('dot');
        dot.setAttribute('aria-label', `Go to banner slide ${index + 1}`);
        dot.setAttribute('role', 'tab');
        if (index === 0) dot.classList.add('active');
        dot.addEventListener('click', () => goToSlide(index));
        dotsContainer.appendChild(dot);
    });

    const dots = document.querySelectorAll('.dot');

    function updateDots() {
        dots.forEach((dot, index) => {
            const isActive = index === currentIndex;
            dot.classList.toggle('active', isActive);
            dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
            dot.setAttribute('tabindex', isActive ? '0' : '-1');
        });
    }

    function goToSlide(index) {
        if (index < 0) index = slideCount - 1;
        if (index >= slideCount) index = 0;

        currentIndex = index;
        preloadNearbySlides(currentIndex);
        sliderWrapper.style.transform = `translateX(-${currentIndex * 100}%)`;
        updateDots();
        resetTimer();
    }

    function nextSlide() {
        goToSlide(currentIndex + 1);
    }

    function prevSlide() {
        goToSlide(currentIndex - 1);
    }

    function startTimer() {
        autoPlayInterval = setInterval(nextSlide, 5000); // 5 seconds
    }

    function resetTimer() {
        clearInterval(autoPlayInterval);
        startTimer();
    }

    // Event Listeners
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);
    if (nextBtn) nextBtn.addEventListener('click', nextSlide);

    sliderWrapper.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') prevSlide();
        if (event.key === 'ArrowRight') nextSlide();
    });

    [prevBtn, nextBtn, dotsContainer].forEach((element) => {
        if (!element) return;
        element.addEventListener('focusin', () => clearInterval(autoPlayInterval));
        element.addEventListener('focusout', resetTimer);
    });

    sliderWrapper.addEventListener('mouseenter', () => clearInterval(autoPlayInterval));
    sliderWrapper.addEventListener('mouseleave', resetTimer);
    sliderWrapper.setAttribute('tabindex', '0');
    sliderWrapper.setAttribute('aria-label', 'Homepage banner slider');
    sliderWrapper.setAttribute('role', 'region');

    // Initial Start
    preloadNearbySlides(currentIndex);
    updateDots();
    startTimer();
});
