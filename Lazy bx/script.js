function initLazyLoad() {
    const lazyBgElements = document.querySelectorAll(".lazyload-bg[data-bg]");

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const bgUrl = element.getAttribute("data-bg");

                if (bgUrl) {
                    const img = new Image();
                    img.src = bgUrl;

                    img.onload = function () {
                        element.style.backgroundImage = `url(${bgUrl})`;
                        element.classList.add("lazyloaded");
                        const loader = element.querySelector(".loader");
                        if (loader) loader.remove();
                    };

                    element.removeAttribute("data-bg");
                }

                observer.unobserve(element);
            }
        });
    }, {rootMargin: "100px"});

    lazyBgElements.forEach(el => observer.observe(el));
}