import Swiper from 'swiper';
import { Navigation, Keyboard, Mousewheel, FreeMode } from 'swiper/modules';

class Gallery {
	constructor(element) {
		this.element = element;
		console.log('Creating Gallery instance for element:', this.element);
		if (this.element.dataset.galleryInitialized === 'true') {
			console.log('Gallery already initialized, skipping:', this.element);
			return;
		}

		const swiperEl = this.element.querySelector('.gallery__swiper');
		if (!swiperEl) return;

		this.swiper = new Swiper(swiperEl, {
			modules: [Navigation, Keyboard, Mousewheel, FreeMode],
			slidesPerView: 'auto',
			spaceBetween: 24,
			speed: 500,
			grabCursor: true,
			freeMode: {
				enabled: true,
				momentum: true,
			},
			keyboard: {
				enabled: true,
				onlyInViewport: true,
			},
			mousewheel: {
				enabled: true,
				forceToAxis: true,
			},
			navigation: {
				nextEl: this.element.querySelector('.gallery__next'),
				prevEl: this.element.querySelector('.gallery__prev'),
				addIcons: true,
			},
			// breakpoints: {
			// 	768: {
			// 		spaceBetween: 32,
			// 	},
			// 	1024: {
			// 		spaceBetween: 48,
			// 	},
			// 	1440: {
			// 		spaceBetween: 48,
			// 	},
			// },
		});

		this.element.dataset.galleryInitialized = 'true';
		console.log('Initialized gallery:', this.element);
	}
}

function initGalleries(root = document) {
	root.querySelectorAll('sq-gallery').forEach((element) => {
		new Gallery(element);
	});
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', () => initGalleries(), { once: true });
} else {
	initGalleries();
}
