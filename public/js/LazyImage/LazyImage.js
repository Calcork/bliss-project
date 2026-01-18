/**
 * LazyImage - Progressive image loading with LQIP (Low Quality Image Placeholder)
 *
 * Shows a tiny blurred placeholder first, then swaps to full-res image
 * when it enters the viewport.
 */

class LazyImage {
    constructor() {
        this.observer = null;
        this.init();
    }

    init() {
        // Use IntersectionObserver for efficient lazy loading
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver(
                (entries) => this.handleIntersection(entries),
                {
                    rootMargin: '50px 0px', // Start loading 50px before entering viewport
                    threshold: 0.01
                }
            );

            this.observeImages();
        } else {
            // Fallback: load all images immediately
            this.loadAllImages();
        }

        // Observe for dynamically added images
        this.observeMutations();
    }

    observeImages() {
        const images = document.querySelectorAll('.lazy-image[data-src]');
        images.forEach((img) => {
            if (!img.dataset.observed) {
                this.observer.observe(img);
                img.dataset.observed = 'true';
            }
        });
    }

    handleIntersection(entries) {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                this.loadImage(entry.target);
                this.observer.unobserve(entry.target);
            }
        });
    }

    loadImage(img) {
        const src = img.dataset.src;
        if (!src) return;

        // Create a new image to preload
        const tempImage = new Image();

        tempImage.onload = () => {
            // Swap src and trigger transition
            img.src = src;
            img.classList.remove('lazy-image--loading');
            img.classList.add('lazy-image--loaded');
            delete img.dataset.src;
        };

        tempImage.onerror = () => {
            // On error, still remove loading state
            img.classList.remove('lazy-image--loading');
            img.classList.add('lazy-image--error');
        };

        tempImage.src = src;
    }

    loadAllImages() {
        const images = document.querySelectorAll('.lazy-image[data-src]');
        images.forEach((img) => this.loadImage(img));
    }

    observeMutations() {
        // Watch for dynamically added images
        const mutationObserver = new MutationObserver((mutations) => {
            let hasNewImages = false;
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        if (node.classList?.contains('lazy-image') && node.dataset?.src) {
                            hasNewImages = true;
                        }
                        // Also check children
                        if (node.querySelectorAll) {
                            const childImages = node.querySelectorAll('.lazy-image[data-src]');
                            if (childImages.length > 0) {
                                hasNewImages = true;
                            }
                        }
                    }
                });
            });

            if (hasNewImages) {
                this.observeImages();
            }
        });

        mutationObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
}

// Auto-initialize when DOM is ready
function initLazyImages() {
    return new LazyImage();
}

// Initialize on DOMContentLoaded or immediately if already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLazyImages);
} else {
    initLazyImages();
}

export { LazyImage, initLazyImages };
export default LazyImage;
