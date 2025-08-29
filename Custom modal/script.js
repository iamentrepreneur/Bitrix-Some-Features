

// ——— ГЛАВНЫЕ НАСТРОЙКИ ———
const MODAL_SELECTOR_ATTR = "[data-modal-open]"; // кнопки открытия
const OVERLAY_ID = "overlay-bg"; // твой overlay из футера
const MODAL_OPEN_CLASS = "is-open";
const OVERLAY_OPEN_CLASS = "show-max";

(function () {
    // 1) Безопасно ждём DOM (Битрикс)
    const onReady = (fn) => {
        if (window.BX && BX.ready) BX.ready(fn);
        else if (document.readyState !== "loading") fn();
        else document.addEventListener("DOMContentLoaded", fn);
    };

    // 2) Ждём появления overlay в DOM, даже если футер грузится позже
    function waitForOverlay(cb, timeoutMs = 5000) {
        const start = Date.now();
        const tryGet = () => {
            const el = document.getElementById(OVERLAY_ID);
            if (el) return cb(el);
            if (Date.now() - start > timeoutMs) {
                console.warn("[modal] overlay not found by timeout");
                return;
            }
            requestAnimationFrame(tryGet);
        };
        tryGet();
    }

    onReady(function () {
        let currentModal = null;

        waitForOverlay(function (overlay) {
            // ——— ОТКРЫТИЕ ———
            document.addEventListener("click", function (e) {
                const opener = e.target.closest(MODAL_SELECTOR_ATTR);
                if (!opener) return;

                const selector = opener.getAttribute("data-modal-open");
                const modal = selector ? document.querySelector(selector) : null;
                if (!modal) {
                    console.warn("[modal] не найден селектор:", selector);
                    return;
                }

                currentModal?.classList.remove(MODAL_OPEN_CLASS);
                currentModal = modal;

                overlay.classList.add(OVERLAY_OPEN_CLASS);
                modal.classList.add(MODAL_OPEN_CLASS);
                e.preventDefault();
            });

            // ——— ЗАКРЫТИЕ: клик по фону ———
            overlay.addEventListener("click", function (e) {
                // Если хочешь закрывать при любом клике по overlay — просто закрывай
                closeCurrent();
            });

            // ——— ЗАКРЫТИЕ: Esc ———
            document.addEventListener("keydown", function (e) {
                if (e.key === "Escape" && overlay.classList.contains(OVERLAY_OPEN_CLASS)) {
                    closeCurrent();
                }
            });

            function closeCurrent() {
                if (currentModal) currentModal.classList.remove(MODAL_OPEN_CLASS);
                overlay.classList.remove(OVERLAY_OPEN_CLASS);
                currentModal = null;
            }
        });

        // 3) Защита от отсутствия Fancybox — где-то в шаблоне может остаться вызов
        if (typeof window.Fancybox === "undefined") {
            // Ничего не делаем — просто не вызываем Fancybox.bind/close и т.п.
            // Если где-то остался вызов, оберни его условием:
            // if (window.Fancybox) { Fancybox.bind("[data-fancybox]", {...}); }
        }
    });
})();