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

    // Create Dots
    slides.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.classList.add('dot');
        if (index === 0) dot.classList.add('active');
        dot.addEventListener('click', () => goToSlide(index));
        dotsContainer.appendChild(dot);
    });

    const dots = document.querySelectorAll('.dot');

    function updateDots() {
        dots.forEach((dot, index) => {
            if (index === currentIndex) dot.classList.add('active');
            else dot.classList.remove('active');
        });
    }

    function goToSlide(index) {
        if (index < 0) index = slideCount - 1;
        if (index >= slideCount) index = 0;

        currentIndex = index;
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

    // Initial Start
    startTimer();
});
