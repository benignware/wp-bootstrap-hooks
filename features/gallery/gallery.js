document.addEventListener('DOMContentLoaded', () => {
  if (!window.bootstrap || !window.bootstrap.Carousel) {
    console.error("Bootstrap Carousel is not available.");
    return;
  }

  document.querySelectorAll('.carousel').forEach(carouselEl => {
    CarouselController.getOrCreateInstance(carouselEl);
  });
});

class CarouselController {
  static PLAYING_CLASS = 'is-playing';
  static PAUSED_CLASS = 'is-paused';
  static BUTTON_PLAYING_CLASS = 'is-playing';
  static BUTTON_PAUSED_CLASS = 'is-paused';
  static TOGGLE_PLAY_SELECTOR = '[data-bs-toggle="play"]';
  static THUMBNAIL_WIDTH = 72;

  static instances = new Map();

  static getInstance(carouselEl) {
    return CarouselController.instances.get(carouselEl);
  }

  static getOrCreateInstance(carouselEl) {
    if (!CarouselController.instances.has(carouselEl)) {
      CarouselController.instances.set(carouselEl, new CarouselController(carouselEl));
    }
    return CarouselController.instances.get(carouselEl);
  }

  constructor(carouselEl) {
    this.handleClick = this.handleClick.bind(this);
    this.handleResize = this.handleResize.bind(this);
    this.handleMouseMove = this.handleMouseMove.bind(this);
    this.handleSlideEvent = this.handleSlideEvent.bind(this);

    this.carouselEl = carouselEl;
    this.isPlaying = false;
    this.isHovering = false;
    this.isVisible = false;
    this.autoplayInterval = null;

    this.initCarousel();

    document.addEventListener('click', this.handleClick);
    document.addEventListener('mousemove', this.handleMouseMove);
    window.addEventListener('load', this.handleResize);
    window.addEventListener('resize', this.handleResize);
    this.observeVisibility();
    this.setupModalEventListeners();
    
    this.handleResize();
  }

  get relatedTargets() {
    return [...new Set(
      [...document.querySelectorAll('[data-bs-slide-to]')]
        .reduce((targets, control) => {
          const isDescendant = this.carouselEl.contains(control);
          const isTarget = this.carouselEl.matches(control.dataset.bsTarget); 
          const target = !isDescendant && isTarget
            ? control.closest('.carousel')
            : isDescendant && !isTarget
              ? document.querySelector(control.dataset.bsTarget)
              : null;

          return target ? [...targets, target] : targets;
        }, [])
    )];
  }

  get relatedModal() {
    const modalEl = this.carouselEl.closest('.modal');
    if (modalEl) {
      return modalEl;
    }
    const controls = [...document.querySelectorAll('[data-bs-toggle="modal"]')];
    const selectors = controls.map(control => control.dataset.bsTarget);
    const controlTargets = [...new Set(selectors)].map(target => document.querySelector(target));
    return controlTargets[0] || null;
  }

  get carouselIndicators() {
    return [...new Set(
      [...document.querySelectorAll('[data-bs-slide-to]')]
        .filter(control => this.carouselEl.matches(control.dataset.bsTarget))
      )];
  }

  get carouselInstance() {
    return window.bootstrap.Carousel.getOrCreateInstance(this.carouselEl);
  }

  get controlButtons() {
    return Array.from(document.querySelectorAll(CarouselController.TOGGLE_PLAY_SELECTOR))
      .filter(button => this.carouselEl.matches(button.dataset.bsTarget));
  }

  get isControlled() {
    return this.controlButtons.length > 0;
  }

  get externalIndicators() {
    return this.carouselIndicators.filter(indicator => !indicator.closest('.carousel'));
  }

  initCarousel() {
    this.isPlaying = this.carouselEl.dataset.bsRide === 'carousel';
    if (this.isControlled) {
      this.reinitializeControlledCarousel();
    }
    this.updateControlButtons();

    this.carouselEl.addEventListener('slide.bs.carousel', this.handleSlideEvent);
  }

  dispose() {
    this.stopAutoplay();
    this.carouselEl.removeEventListener('slide.bs.carousel', this.handleSlideEvent);
    window.removeEventListener('resize', this.handleResize);
    document.removeEventListener('click', this.handleClick);
  }

  reinitializeControlledCarousel() {
    this.carouselInstance.dispose();
    new window.bootstrap.Carousel(this.carouselEl, {
      ride: false,
      pause: false 
    });
    this.carouselEl.setAttribute('data-bs-ride', 'false');
    if (this.isPlaying) {
      this.startAutoplay();
    }
  }

  handleSlideEvent(event) {
    const targetIndex = event.to;

    const externalIndicators = this.externalIndicators;
    
    externalIndicators.forEach(indicator => {
      const index = parseInt(indicator.dataset.bsSlideTo, 10);
      indicator.classList.toggle('active', index === targetIndex);
    });

    const indicatorContainers = [...new Set(externalIndicators.map(indicator => indicator.parentElement))];

    if (indicatorContainers.length) {
      this.handleResize();
    }

    indicatorContainers.forEach(container => {
      const item = container.querySelector(`[data-bs-slide-to="${targetIndex}"]`);

      if (!item) return;

      const bi = item.getBoundingClientRect();
      const bp = container.getBoundingClientRect();
      const x = bi.left - bp.left;

      container.scrollTo({
        left: x,
        behavior: 'smooth'
      });
    });

    if (!this.isVisible) return;
  
    this.relatedTargets.forEach(relatedEl => {
      const relatedInstance = CarouselController.getInstance(relatedEl);
  
      if (relatedInstance && !relatedInstance.isVisible) {
        relatedEl.classList.add('no-transition');
        relatedInstance.carouselInstance.to(targetIndex);
        relatedEl.offsetHeight;
        relatedEl.classList.remove('no-transition');
      }
    });
  }

  handleResize() {
    if (!this.isVisible) return;

    const externalIndicators = this.externalIndicators;
    const indicatorContainers = [...new Set(externalIndicators.map(indicator => indicator.parentElement))];

    indicatorContainers.forEach(container => {
      const style = window.getComputedStyle(container);
      const g = parseFloat(style.getPropertyValue('column-gap'));
      const w = container.parentNode.clientWidth + g;
      const a = Math.floor(w / (CarouselController.THUMBNAIL_WIDTH + g));
      const c = container.childElementCount;
      const x = w / a - g;

      container.style.gridTemplateColumns = `repeat(${Math.max(c, a)}, minmax(${x}px, 1fr))`;
    });
  }

  handleClick(event) {
    const button = event.target.closest(CarouselController.TOGGLE_PLAY_SELECTOR);
    if (button && this.carouselEl.matches(button.dataset.bsTarget)) {
      this.togglePlayPause();
    }

    this.relatedTargets.forEach(carousel => {
      const carouselControllerInstance = CarouselController.getInstance(carousel);
      
      if (carouselControllerInstance) {
        if (this.isPlaying) {
          carouselControllerInstance.play();
        } else {
          carouselControllerInstance.pause();
        }
      }
    });
  }

  handleMouseMove(event) {
    const isMouseAboveCarousel = this.carouselEl.contains(event.target);
    const indicatorContainers = [...new Set(this.carouselIndicators.map(indicator => indicator.parentElement))];
    const isMouseAboveIndicators = indicatorContainers.some(parent => parent.contains(event.target));
    this.isHovering = isMouseAboveCarousel || isMouseAboveIndicators;

    if (this.isPlaying && this.isHovering && !this.isHovering) {
      this.stopAutoplay();
    } else if (this.isPlaying && !this.isHovering && this.isVisible) {
      this.startAutoplay();
    }
  }

  togglePlayPause() {
    if (this.isPlaying) {
      this.stopAutoplay();
      this.carouselEl.classList.remove(CarouselController.PLAYING_CLASS);
    } else {
      this.startAutoplay();
      this.carouselEl.classList.add(CarouselController.PLAYING_CLASS);
    }
    this.isPlaying = !this.isPlaying;
    this.updateControlButtons();
  }

  startAutoplay() {
    this.stopAutoplay();
    this.autoplayInterval = setInterval(() => {
      if (!this.isPlaying || this.isHovering || !this.isVisible) {
        clearInterval(this.autoplayInterval);
        return;
      }
      this.carouselInstance.next();
    }, this.carouselEl.dataset.bsInterval || 5000);
  }

  stopAutoplay() {
    clearInterval(this.autoplayInterval);
    this.autoplayInterval = null;
  }

  updateControlButtons() {
    this.controlButtons.forEach(button => {
      if (this.isPlaying) {
        button.classList.add(CarouselController.BUTTON_PLAYING_CLASS);
        button.classList.remove(CarouselController.BUTTON_PAUSED_CLASS);
      } else {
        button.classList.add(CarouselController.BUTTON_PAUSED_CLASS);
        button.classList.remove(CarouselController.BUTTON_PLAYING_CLASS);
      }
    });
  }

  observeVisibility() {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        this.isVisible = entry.isIntersecting;
        this.handleVisibilityChange();
      });
    }, { threshold: 0.5 });
    observer.observe(this.carouselEl);
  }

  setupModalEventListeners() {
    const modalEl = this.relatedModal;

    if (modalEl) {
      const isModal = modalEl.contains(this.carouselEl);

      modalEl.addEventListener('show.bs.modal', () => {
        this.isVisible = isModal;
        this.handleVisibilityChange();
      });
      modalEl.addEventListener('hide.bs.modal', () => {
        this.isVisible = !isModal;
        this.handleVisibilityChange();
      });
    }
  }

  handleVisibilityChange() {
    if (this.isPlaying && !this.isHovering) {
      this.isVisible ? this.startAutoplay() : this.stopAutoplay();
    }
  }

  play() {
    if (!this.isPlaying) {
      this.togglePlayPause();
    }
  }

  pause() {
    if (this.isPlaying) {
      this.togglePlayPause();
    }
  }
}
