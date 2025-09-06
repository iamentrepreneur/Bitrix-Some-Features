<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>


<div class="some-picture lazyload-bg" data-bg="pic.png">
    <div class="loader"></div>
</div>



<div class="lazy-wrap">
<img
        alt=""
        class="lazy"
        width="<?=$item['PREVIEW_PICTURE']['WIDTH']?>"
        height="<?=$item['PREVIEW_PICTURE']['HEIGHT']?>"
        data-src="<?=$item['PREVIEW_PICTURE']['SRC']?>"
>
<span class="lazy-spinner" aria-hidden="true"></span>
<noscript>
    <img src="<?=$item['PREVIEW_PICTURE']['WIDTH']?>" alt="">
</noscript>
</div>

<script>

    // Lazyload bg
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

    document.addEventListener("DOMContentLoaded", function () {
        initLazyLoad();
    });

    // Pictures lazyload
    (function () {
        let io = null;

        function initLazy(root) {
            const scope = root || document;
            const imgs = scope.querySelectorAll('img.lazy[data-src]');
            if (!imgs.length) return;

            if (!io && 'IntersectionObserver' in window) {
                io = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (!entry.isIntersecting) return;
                        const img = entry.target;
                        const wrap = img.closest('.lazy-wrap');
                        img.src = img.dataset.src;

                        img.addEventListener('load', () => {
                            wrap.classList.add('is-loaded');
                        }, {once:true});

                        img.addEventListener('error', () => {
                            wrap.classList.add('is-error');
                        }, {once:true});

                        obs.unobserve(img);
                    });
                }, { rootMargin: '200px 0px' });
            }

            imgs.forEach(img => {
                if (io) {
                    io.observe(img);
                } else {
                    // фолбек без IO
                    img.src = img.dataset.src;
                    const wrap = img.closest('.lazy-wrap');
                    wrap.classList.add('is-loaded');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => initLazy(document));

        window.__initLazyIn = initLazy;

        // onsuccess: function (html) {
        //     grid.innerHTML = html;
        //     if (window.__initLazyIn) window.__initLazyIn(grid);
    })();
</script>

</body>
</html>